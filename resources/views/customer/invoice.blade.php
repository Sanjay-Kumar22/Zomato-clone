<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice for order #{{ $order->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 32px; color: #24232a; background: #f4f5f7; font: 14px/1.5 Arial, sans-serif; }
        .invoice { max-width: 760px; margin: auto; padding: 44px; background: #fff; border: 1px solid #e8e9ed; border-radius: 18px; }
        .top { display: flex; justify-content: space-between; gap: 24px; padding-bottom: 24px; border-bottom: 1px solid #ececf0; }
        h1 { margin: 0 0 6px; color: #e8342a; font-size: 24px; }
        h2 { margin: 0 0 20px; font-size: 28px; }
        .muted { color: #777b86; }
        .meta { text-align: right; }
        .addresses { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding: 24px 0; }
        .label { margin-bottom: 5px; font-size: 11px; font-weight: 700; color: #888b94; letter-spacing: .08em; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 8px; border-bottom: 1px solid #eff0f2; text-align: left; }
        th { color: #777b86; font-size: 11px; text-transform: uppercase; }
        .right { text-align: right; }
        .totals { width: min(100%, 340px); margin: 20px 0 0 auto; }
        .totals td { border: 0; padding: 5px 0; }
        .grand { border-top: 1px solid #e8e9ed !important; color: #e8342a; font-size: 18px; font-weight: 700; }
        .actions { max-width: 760px; margin: 16px auto; display: flex; justify-content: flex-end; gap: 10px; }
        button, a { display: inline-block; padding: 10px 16px; border: 0; border-radius: 9px; color: #fff; background: #e8342a; font: inherit; text-decoration: none; cursor: pointer; }
        a { color: #333; background: #e8e9ed; }
        @media(max-width:600px) { body { padding: 10px; } .invoice { padding: 22px; } .top { flex-direction: column; } .meta { text-align: left; } }
        @media print { body { padding: 0; background: #fff; } .invoice { max-width: none; border: 0; border-radius: 0; padding: 0; } .actions { display: none; } }
    </style>
</head>
<body>
<div class="invoice">
    <div class="top">
        <div>
            <h1>ZomatoClone</h1>
            <div class="muted">Order invoice</div>
        </div>
        <div class="meta">
            <h2>Invoice #{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</h2>
            <div>Issued {{ $order->created_at->format('d M Y') }}</div>
            <div class="muted">Order #{{ $order->id }} · {{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
        </div>
    </div>
    <div class="addresses">
        <div>
            <div class="label">Restaurant</div>
            <strong>{{ $order->restaurant->name }}</strong><br>
            {{ $order->restaurant->address }}<br>{{ $order->restaurant->pincode }}
        </div>
        <div>
            <div class="label">Billed to / delivered to</div>
            <strong>{{ $order->user->name }}</strong><br>
            {{ $order->address->full_address ?? 'Address unavailable' }}<br>
            {{ $order->address->pincode ?? '' }}
        </div>
    </div>
    <table>
        <thead><tr><th>Item</th><th>Qty</th><th class="right">Amount</th></tr></thead>
        <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>
                <strong>{{ $item->name }}</strong>
                @if(!empty($item->selected_options))
                <div class="muted">{{ collect($item->selected_options)->map(fn ($option) => $option['label'].': '.$option['value'])->join(', ') }}</div>
                @endif
            </td>
            <td>{{ $item->quantity }}</td>
            <td class="right">₹{{ number_format($item->price_snapshot * $item->quantity, 2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <table class="totals">
        <tr><td>Item subtotal</td><td class="right">₹{{ number_format($order->subtotal, 2) }}</td></tr>
        @if($order->discount > 0)<tr><td>Discount</td><td class="right">− ₹{{ number_format($order->discount, 2) }}</td></tr>@endif
        @if($order->points_discount > 0)<tr><td>Rewards applied</td><td class="right">− ₹{{ number_format($order->points_discount, 2) }}</td></tr>@endif
        <tr><td>Delivery</td><td class="right">₹{{ number_format($order->delivery_fee, 2) }}</td></tr>
        @if($order->packaging_charge > 0)<tr><td>Packaging</td><td class="right">₹{{ number_format($order->packaging_charge, 2) }}</td></tr>@endif
        @if($order->tax_amount > 0)<tr><td>GST & taxes</td><td class="right">₹{{ number_format($order->tax_amount, 2) }}</td></tr>@endif
        @if($order->platform_fee > 0)<tr><td>Platform fee</td><td class="right">₹{{ number_format($order->platform_fee, 2) }}</td></tr>@endif
        @if($order->tip_amount > 0)<tr><td>Delivery partner tip</td><td class="right">₹{{ number_format($order->tip_amount, 2) }}</td></tr>@endif
        @if($order->wallet_used > 0)<tr><td>Wallet payment</td><td class="right">− ₹{{ number_format($order->wallet_used, 2) }}</td></tr>@endif
        <tr class="grand"><td>Paid / due via {{ strtoupper($order->payment_method) }}</td><td class="right">₹{{ number_format($order->total, 2) }}</td></tr>
        <tr><td>Payment status</td><td class="right">{{ ucfirst($order->payment_status) }}</td></tr>
    </table>
</div>
<div class="actions">
    <a href="{{ route('customer.orders.show', $order) }}">Back to order</a>
    <button onclick="window.print()">Print / Save as PDF</button>
</div>
</body>
</html>
