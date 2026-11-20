<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #{{ $order->id }} — Print Slip</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', monospace;
            width: 320px;
            margin: 20px auto;
            color: #000;
            font-size: 13px;
        }
        .center { text-align: center; }
        .bold { font-weight: 700; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .sub { font-size: 11px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        td { padding: 3px 0; vertical-align: top; }
        .qty-col { width: 30px; }
        .price-col { width: 60px; text-align: right; }
        .item-name { font-weight: 600; }
        .item-opt { font-size: 11px; color: #333; padding-left: 8px; }
        .total-row td { padding-top: 6px; font-weight: 700; font-size: 14px; border-top: 1px solid #000; }
        .meta-row { display: flex; justify-content: space-between; font-size: 12px; margin: 2px 0; }
        .print-btn {
            display: block; width: 100%; margin-top: 20px; padding: 10px;
            background: #e8342a; color: white; border: none; border-radius: 8px;
            font-family: 'Segoe UI', sans-serif; font-weight: 600; cursor: pointer;
        }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="center">
        <h1>{{ $order->restaurant->name }}</h1>
        <div class="sub">{{ $order->restaurant->address ?? '' }}</div>
        <div class="bold">KITCHEN ORDER SLIP</div>
    </div>

    <div class="divider"></div>

    <div class="meta-row"><span>Order #</span><span class="bold">#{{ $order->id }}</span></div>
    <div class="meta-row"><span>Date</span><span>{{ $order->created_at->format('d M Y, h:i A') }}</span></div>
    <div class="meta-row"><span>Delivery</span><span>{{ $order->delivery_slot ?? 'ASAP' }}</span></div>
    <div class="meta-row"><span>Payment</span><span>{{ strtoupper($order->payment_method) }}</span></div>
    <div class="meta-row"><span>Customer</span><span>{{ $order->user->name ?? 'Customer' }}</span></div>
    @if($order->user?->phone)
    <div class="meta-row"><span>Phone</span><span>{{ $order->user->phone }}</span></div>
    @endif

    @if($order->special_instructions)
    <div class="divider"></div>
    <div class="bold">CUSTOMER INSTRUCTIONS</div>
    <div>{{ $order->special_instructions }}</div>
    @endif
    @if($order->cooking_notes)
    <div class="divider"></div>
    <div class="bold">KITCHEN NOTES</div>
    <div>{{ $order->cooking_notes }}</div>
    @endif

    <div class="divider"></div>

    <table>
        <tr class="bold">
            <td class="qty-col">Qty</td>
            <td>Item</td>
            <td class="price-col">Price</td>
        </tr>
        @foreach($order->items as $item)
        <tr>
            <td class="qty-col">{{ $item->quantity }}x</td>
            <td>
                <div class="item-name">{{ $item->name }}</div>
                @if(!empty($item->selected_options))
                    @foreach($item->selected_options as $opt)
                    <div class="item-opt">+ {{ $opt['label'] ?? '' }}: {{ $opt['value'] ?? '' }}</div>
                    @endforeach
                @endif
            </td>
            <td class="price-col">₹{{ number_format($item->price_snapshot * $item->quantity, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr><td>Item Total</td><td class="price-col">₹{{ number_format($order->subtotal, 2) }}</td></tr>
        @if($order->discount > 0)
        <tr><td>Discount</td><td class="price-col">- ₹{{ number_format($order->discount, 2) }}</td></tr>
        @endif
        @if($order->points_discount > 0)
        <tr><td>Points Used</td><td class="price-col">- ₹{{ number_format($order->points_discount, 2) }}</td></tr>
        @endif
        @if($order->wallet_used > 0)
        <tr><td>Wallet</td><td class="price-col">- ₹{{ number_format($order->wallet_used, 2) }}</td></tr>
        @endif
        <tr><td>Delivery Fee</td><td class="price-col">₹{{ number_format($order->delivery_fee, 2) }}</td></tr>
        @if($order->tip_amount > 0)
        <tr><td>Tip</td><td class="price-col">₹{{ number_format($order->tip_amount, 2) }}</td></tr>
        @endif
        <tr class="total-row"><td>TOTAL</td><td class="price-col">₹{{ number_format($order->total, 2) }}</td></tr>
    </table>

    <div class="divider"></div>

    <div class="center sub">Thank you! · Printed {{ now()->format('d M Y, h:i A') }}</div>

    <button class="print-btn" onclick="window.print()">🖨️ Print Slip</button>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
