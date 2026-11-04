<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\FoodItem;
use App\Models\GroupOrder;
use App\Models\GroupOrderItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Notifications\NewOrderPlaced;
use App\Mail\OrderConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class GroupOrderController extends Controller
{
    // Starts a new group order for a restaurant and takes the host straight
    // to the shareable group-order page.
    public function start(Restaurant $restaurant)
    {
        if (!$restaurant->isCurrentlyOpen()) {
            return back()->with('error', 'This restaurant is currently closed.');
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (GroupOrder::where('code', $code)->exists());

        $groupOrder = GroupOrder::create([
            'code'          => $code,
            'restaurant_id' => $restaurant->id,
            'host_id'       => auth()->id(),
            'status'        => 'open',
        ]);

        return redirect()->route('customer.group-orders.show', $groupOrder->code);
    }

    // The shared page — restaurant menu + everyone's items so far.
    // Anyone with the link (logged in as a customer) can open this and join.
    public function show(string $code)
    {
        $groupOrder = GroupOrder::where('code', $code)->firstOrFail();
        $groupOrder->load('restaurant', 'host', 'items.user', 'items.foodItem');

        $menuByCategory = $groupOrder->restaurant->foodItems()
            ->where('is_available', true)
            ->with('category', 'options')
            ->get()
            ->groupBy(fn ($item) => $item->category->name ?? 'Other');

        $myItems = $groupOrder->items->where('user_id', auth()->id());
        $itemsByUser = $groupOrder->items->groupBy(fn ($i) => $i->user->name ?? 'Someone');

        $addresses = auth()->user()->addresses;

        return view('customer.group-order', compact('groupOrder', 'menuByCategory', 'myItems', 'itemsByUser', 'addresses'));
    }

    public function addItem(Request $request, string $code)
    {
        $groupOrder = GroupOrder::where('code', $code)->firstOrFail();

        if ($groupOrder->status !== 'open') {
            return back()->with('error', 'This group order has already been placed or cancelled.');
        }

        $request->validate([
            'food_item_id' => 'required|exists:food_items,id',
            'quantity'     => 'required|integer|min:1|max:20',
            'option_ids'   => 'nullable|array',
        ]);

        $foodItem = FoodItem::with('options')->findOrFail($request->food_item_id);

        if ($foodItem->restaurant_id !== $groupOrder->restaurant_id) {
            abort(422, 'This item does not belong to this group order\'s restaurant.');
        }

        $optionIds = array_values(array_unique(array_map('intval', $request->option_ids ?? [])));
        $selectedOptions = $foodItem->options->whereIn('id', $optionIds)->values();
        $extra = (float) $selectedOptions->sum(fn ($o) => (float) $o->extra_price);

        GroupOrderItem::create([
            'group_order_id'    => $groupOrder->id,
            'user_id'           => auth()->id(),
            'food_item_id'      => $foodItem->id,
            'name'              => $foodItem->name,
            'price'             => (float) $foodItem->price + $extra,
            'quantity'          => $request->quantity,
            'selected_options'  => $selectedOptions->map(fn ($o) => [
                'label' => $o->label, 'value' => $o->value, 'extra_price' => (float) $o->extra_price,
            ])->toArray(),
        ]);

        return back()->with('success', 'Added to the group order!');
    }

    public function removeItem(string $code, GroupOrderItem $item)
    {
        abort_if($item->user_id !== auth()->id(), 403, 'You can only remove your own items.');

        $groupOrder = $item->groupOrder;
        if ($groupOrder->status !== 'open') {
            return back()->with('error', 'This group order has already been placed or cancelled.');
        }

        $item->delete();

        return back()->with('success', 'Item removed.');
    }

    // Only the host can finalize and place the combined order.
    // Kept simple for v1 — Cash on Delivery only, no coupons/points/wallet yet.
    public function place(Request $request, string $code)
    {
        $groupOrder = GroupOrder::where('code', $code)->firstOrFail();

        abort_if($groupOrder->host_id !== auth()->id(), 403, 'Only the host can place this group order.');

        if ($groupOrder->status !== 'open') {
            return back()->with('error', 'This group order has already been placed or cancelled.');
        }

        $groupOrder->load('items');
        if ($groupOrder->items->isEmpty()) {
            return back()->with('error', 'No items have been added yet.');
        }

        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        $address = auth()->user()->addresses()->findOrFail($request->address_id);
        $restaurant = $groupOrder->restaurant;

        if (!$restaurant->deliversToLocation($address->pincode, $address->lat, $address->lng)) {
            return back()->with('error', "Sorry, this restaurant doesn't deliver to that address.");
        }

        $deliveryFee = (float) (DeliveryZone::where('restaurant_id', $restaurant->id)
            ->where('pincode', $address->pincode)
            ->value('delivery_fee') ?? 0);

        $subtotal = $groupOrder->total();
        $total = $subtotal + $deliveryFee;

        $order = DB::transaction(function () use ($groupOrder, $request, $subtotal, $deliveryFee, $total) {
            $order = Order::create([
                'user_id'        => auth()->id(),
                'restaurant_id'  => $groupOrder->restaurant_id,
                'address_id'     => $request->address_id,
                'subtotal'       => $subtotal,
                'delivery_fee'   => $deliveryFee,
                'delivery_slot'  => 'ASAP',
                'total'          => $total,
                'status'         => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cod',
            ]);

            foreach ($groupOrder->items as $item) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'food_item_id'     => $item->food_item_id,
                    // Tag who ordered what right in the item name, since a
                    // group order splits one order across several people.
                    'name'             => $item->name . ' (for ' . ($item->user->name ?? 'someone') . ')',
                    'price_snapshot'   => $item->price,
                    'quantity'         => $item->quantity,
                    'selected_options' => $item->selected_options ?? [],
                ]);
            }

            $groupOrder->update([
                'status'     => 'placed',
                'order_id'   => $order->id,
                'address_id' => $request->address_id,
            ]);

            return $order;
        });

        $order->load('restaurant.owner', 'items', 'user');
        $order->restaurant?->owner?->notify(new NewOrderPlaced($order));
        Notification::send(\App\Models\User::admins()->get(), new NewOrderPlaced($order));

        if ($order->user?->email) {
            Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Group order placed successfully! 🎉');
    }

    public function cancel(string $code)
    {
        $groupOrder = GroupOrder::where('code', $code)->firstOrFail();
        abort_if($groupOrder->host_id !== auth()->id(), 403);

        if ($groupOrder->status === 'open') {
            $groupOrder->update(['status' => 'cancelled']);
        }

        return redirect()->route('customer.dashboard')->with('success', 'Group order cancelled.');
    }
}
