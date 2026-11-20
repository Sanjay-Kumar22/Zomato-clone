<?php

namespace Tests\Feature\Customer;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Notifications\NewOrderPlaced;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RazorpayPaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_payment_verification_marks_order_paid_and_notifies_once(): void
    {
        config(['services.razorpay.secret' => 'test-razorpay-secret']);
        Notification::fake();
        Mail::fake();

        $customer = User::factory()->create(['role' => 'customer']);
        $owner = User::factory()->create(['role' => 'restaurant_owner']);
        $now = now();

        $restaurantId = DB::table('restaurants')->insertGetId([
            'user_id' => $owner->id,
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'address' => 'Test address',
            'pincode' => '160055',
            'is_approved' => true,
            'is_open' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $addressId = DB::table('addresses')->insertGetId([
            'user_id' => $customer->id,
            'label' => 'Home',
            'full_address' => 'Test address',
            'pincode' => '160055',
            'is_default' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'restaurant_id' => $restaurantId,
            'address_id' => $addressId,
            'subtotal' => 250,
            'delivery_fee' => 0,
            'total' => 250,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'razorpay',
            'payment_id' => 'order_test_123',
        ]);

        $paymentId = 'pay_test_123';
        $signature = hash_hmac(
            'sha256',
            'order_test_123|' . $paymentId,
            'test-razorpay-secret'
        );

        PaymentAttempt::create([
            'order_id' => $order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_test_123',
            'amount' => 250,
            'currency' => 'INR',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($customer)->post(
            route('customer.payment.razorpay.verify', $order),
            [
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => 'order_test_123',
                'razorpay_signature' => $signature,
            ]
        );

        $response->assertRedirect(route('customer.orders.show', $order));
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame($paymentId, $order->fresh()->payment_id);
        $this->assertDatabaseHas('payment_attempts', [
            'order_id' => $order->id,
            'gateway_payment_id' => $paymentId,
            'status' => 'captured',
        ]);
        Notification::assertSentTo($owner, NewOrderPlaced::class);
        Mail::assertSent(OrderConfirmationMail::class);
    }
}
