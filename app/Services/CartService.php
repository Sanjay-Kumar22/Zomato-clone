<?php

namespace App\Services;

use App\Models\FoodItem;

class CartService
{
    protected string $key = 'cart';
    protected string $cartsKey = 'carts';
    protected string $activeKey = 'active_cart_restaurant_id';

    /**
     * Return the cart for the currently selected restaurant.
     * The restaurant can be selected by ?restaurant_id=... or a POST field.
     */
    public function get(): array
    {
        $this->migrateLegacyCart();
        $carts = session($this->cartsKey, []);
        $restaurantId = $this->activeRestaurantId();

        if (!$restaurantId) {
            return [];
        }

        return $carts[(string) $restaurantId] ?? [];
    }

    /** Return all restaurant carts keyed by restaurant id. */
    public function all(): array
    {
        $this->migrateLegacyCart();
        return session($this->cartsKey, []);
    }

    /** Select which restaurant cart is active for checkout/update/coupon actions. */
    public function select(?int $restaurantId): void
    {
        if (!$restaurantId) {
            session()->forget($this->activeKey);
            return;
        }

        $this->migrateLegacyCart();
        $carts = session($this->cartsKey, []);
        if (isset($carts[(string) $restaurantId])) {
            session([$this->activeKey => (int) $restaurantId]);
        }
    }

    /** Current active restaurant id, honoring request input/query first. */
    public function activeRestaurantId(): ?int
    {
        $requested = request()->input('restaurant_id', request()->query('restaurant_id'));
        if ($requested !== null && is_numeric($requested)) {
            $requested = (int) $requested;
            $carts = session($this->cartsKey, []);
            if (isset($carts[(string) $requested])) {
                session([$this->activeKey => $requested]);
                return $requested;
            }
        }

        $active = session($this->activeKey);
        if ($active !== null) {
            $carts = session($this->cartsKey, []);
            if (isset($carts[(string) $active])) {
                return (int) $active;
            }
        }

        $carts = session($this->cartsKey, []);
        if (!empty($carts)) {
            $first = (int) array_key_first($carts);
            session([$this->activeKey => $first]);
            return $first;
        }

        return null;
    }

    public function add(int $foodItemId, int $quantity = 1, array $optionIds = []): void
    {
        $item = FoodItem::with(['restaurant', 'options'])->findOrFail($foodItemId);
        $this->migrateLegacyCart();

        $carts = session($this->cartsKey, []);
        $restaurantKey = (string) $item->restaurant_id;
        $cart = $carts[$restaurantKey] ?? [];

        $optionIds = array_values(array_unique(array_map('intval', $optionIds)));
        sort($optionIds);

        $selectedOptions = $item->options->whereIn('id', $optionIds)->values();
        $extra = (float) $selectedOptions->sum(fn ($o) => (float) $o->extra_price);
        $unitPrice = (float) $item->price + $extra;

        $cartKey = $optionIds
            ? $foodItemId . '_' . md5(implode(',', $optionIds))
            : (string) $foodItemId;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'food_item_id'  => $item->id,
                'name'          => $item->name,
                'price'         => $unitPrice,
                'base_price'    => (float) $item->price,
                'options'       => $selectedOptions->map(fn ($o) => [
                    'label'       => $o->label,
                    'value'       => $o->value,
                    'extra_price' => (float) $o->extra_price,
                ])->toArray(),
                'restaurant_id' => $item->restaurant_id,
                'restaurant'    => $item->restaurant->name,
                'image'         => $item->image_url,
                'quantity'      => $quantity,
            ];
        }

        $carts[$restaurantKey] = $cart;
        session([$this->cartsKey => $carts, $this->activeKey => (int) $item->restaurant_id]);
    }

    public function update(string $cartKey, int $quantity): void
    {
        $restaurantId = $this->activeRestaurantId();
        if (!$restaurantId) return;

        $carts = session($this->cartsKey, []);
        $key = (string) $restaurantId;
        $cart = $carts[$key] ?? [];

        if ($quantity <= 0) {
            unset($cart[$cartKey]);
        } elseif (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $quantity;
        }

        if (empty($cart)) {
            unset($carts[$key]);
            session()->forget($this->activeKey);
        } else {
            $carts[$key] = $cart;
        }
        session([$this->cartsKey => $carts]);
    }

    public function remove(string $cartKey): void
    {
        $this->update($cartKey, 0);
    }

    /** Clear only the currently selected restaurant cart. */
    public function clear(): void
    {
        $restaurantId = $this->activeRestaurantId();
        if (!$restaurantId) return;

        $carts = session($this->cartsKey, []);
        unset($carts[(string) $restaurantId]);
        session([$this->cartsKey => $carts]);

        if (!empty($carts)) {
            session([$this->activeKey => (int) array_key_first($carts)]);
        } else {
            session()->forget($this->activeKey);
        }
    }

    /** Clear every restaurant cart. */
    public function clearAll(): void
    {
        session()->forget([$this->cartsKey, $this->key, $this->activeKey]);
    }

    public function total(): float
    {
        return collect($this->get())->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    /** Navbar badge = number of distinct cart lines, not total quantities. */
    public function count(): int
    {
        return collect($this->all())->sum(fn ($cart) => count($cart));
    }

    public function restaurantId(): ?int
    {
        return $this->activeRestaurantId();
    }

    protected function migrateLegacyCart(): void
    {
        if (session()->has($this->cartsKey)) {
            return;
        }

        $legacy = session($this->key, []);
        if (empty($legacy)) {
            session([$this->cartsKey => []]);
            return;
        }

        $restaurantId = collect($legacy)->first()['restaurant_id'] ?? null;
        if (!$restaurantId) {
            session([$this->cartsKey => []]);
            return;
        }

        session([
            $this->cartsKey => [(string) $restaurantId => $legacy],
            $this->activeKey => (int) $restaurantId,
        ]);
        session()->forget($this->key);
    }
}
