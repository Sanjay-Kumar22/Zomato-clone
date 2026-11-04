<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, Helvetica, sans-serif; background: #f7f7f9; margin: 0; padding: 0; color: #333; }
    .wrap { max-width: 560px; margin: 0 auto; padding: 24px 16px; }
    .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    .header { background: #e8342a; color: #ffffff; padding: 24px; text-align: center; }
    .header h1 { margin: 0; font-size: 22px; }
    .body { padding: 24px; }
    .item-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
    .item-row:last-child { border-bottom: none; }
    .total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #666; }
    .grand-total { display: flex; justify-content: space-between; padding-top: 10px; margin-top: 6px; border-top: 2px solid #eee; font-size: 17px; font-weight: bold; color: #e8342a; }
    .footer { text-align: center; padding: 16px; font-size: 12px; color: #999; }
    .btn { display: inline-block; background: #e8342a; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 30px; font-weight: bold; font-size: 14px; }
</style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="header">
            <h1>🎉 Order Confirmed!</h1>
            <p style="margin:6px 0 0;font-size:14px;opacity:0.9">Order #{{ $order->id }} from {{ $order->restaurant->name ?? 'the restaurant' }}</p>
        </div>
        <div class="body">
            <p>Hi {{ $order->user->name ?? 'there' }},</p>
            <p>Thanks for your order! Here's a quick summary:</p>

            @foreach($order->items as $item)
            <div class="item-row">
                <span>
                    {{ $item->name }} × {{ $item->quantity }}
                    @if(!empty($item->selected_options))
                    <br><span style="font-size:12px;color:#999">
                        {{ collect($item->selected_options)->map(fn($o) => $o['label'].': '.$o['value'])->join(', ') }}
                    </span>
                    @endif
                </span>
                <span>₹{{ number_format($item->price_snapshot * $item->quantity, 2) }}</span>
            </div>
            @endforeach

            <div class="total-row"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
            @if($order->discount > 0)
            <div class="total-row" style="color:#2e7d32"><span>Coupon Discount</span><span>- ₹{{ number_format($order->discount, 2) }}</span></div>
            @endif
            <div class="total-row"><span>Delivery Fee</span><span>₹{{ number_format($order->delivery_fee, 2) }}</span></div>
            <div class="grand-total"><span>Total</span><span>₹{{ number_format($order->total, 2) }}</span></div>

            <p style="margin-top:20px;font-size:13px;color:#888">
                Payment method: {{ in_array($order->payment_method, ['stripe', 'razorpay']) ? 'Paid Online' : 'Cash on Delivery' }}
            </p>

            <div style="text-align:center;margin-top:24px">
                <a href="{{ route('customer.orders.show', $order) }}" class="btn">Track Your Order</a>
            </div>
        </div>
    </div>
    <div class="footer">
        {{ config('app.name') }} — you're receiving this because you placed an order.
    </div>
</div>
</body>
</html>
