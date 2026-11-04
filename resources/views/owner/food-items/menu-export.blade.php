<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $restaurant->name }} — Menu</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            max-width: 800px;
            margin: 30px auto;
            color: #2b2b2b;
            padding: 0 20px;
        }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #e8342a; padding-bottom: 16px; }
        .header h1 { font-size: 30px; color: #e8342a; margin-bottom: 4px; }
        .header .sub { font-size: 13px; color: #777; }
        .category-title {
            font-size: 17px; font-weight: 700; color: #e8342a;
            margin: 26px 0 10px; padding-bottom: 6px; border-bottom: 1px solid #eee;
        }
        .item-row {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding: 8px 0; border-bottom: 1px dashed #eee;
        }
        .item-left { flex: 1; padding-right: 16px; }
        .item-name { font-weight: 600; font-size: 14.5px; }
        .veg-dot {
            display: inline-block; width: 10px; height: 10px; border: 1.5px solid;
            margin-right: 6px; vertical-align: middle;
        }
        .veg-dot.veg { border-color: #1e8e4e; }
        .veg-dot.veg::after { content: ''; display: block; width: 5px; height: 5px; background: #1e8e4e; margin: 1px; border-radius: 50%; }
        .veg-dot.nonveg { border-color: #c62828; }
        .veg-dot.nonveg::after { content: ''; display: block; width: 5px; height: 5px; background: #c62828; margin: 1px; border-radius: 50%; }
        .item-desc { font-size: 12px; color: #888; margin-top: 2px; }
        .item-price { font-weight: 700; color: #e8342a; font-size: 14px; white-space: nowrap; }
        .item-unavailable { opacity: 0.45; }
        .item-unavailable .item-price::after { content: ' (unavailable)'; font-size: 10px; color: #999; font-weight: 400; }
        .footer { text-align: center; margin-top: 30px; font-size: 11px; color: #aaa; }
        .print-btn {
            display: block; width: 200px; margin: 24px auto 0; padding: 10px;
            background: #e8342a; color: white; border: none; border-radius: 8px;
            font-weight: 600; cursor: pointer;
        }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $restaurant->name }}</h1>
        <div class="sub">{{ $restaurant->address ?? '' }}</div>
        <div class="sub">Full Menu · Exported {{ now()->format('d M Y') }}</div>
    </div>

    @forelse($menuByCategory as $categoryName => $items)
        <div class="category-title">{{ $categoryName }}</div>
        @foreach($items as $item)
        <div class="item-row {{ !$item->is_available ? 'item-unavailable' : '' }}">
            <div class="item-left">
                <div class="item-name">
                    <span class="veg-dot {{ $item->is_veg ? 'veg' : 'nonveg' }}"></span>
                    {{ $item->name }}
                </div>
                @if($item->description)
                    <div class="item-desc">{{ $item->description }}</div>
                @endif
            </div>
            <div class="item-price">₹{{ number_format($item->price, 2) }}</div>
        </div>
        @endforeach
    @empty
        <p style="text-align:center;color:#999;margin-top:40px">No menu items yet.</p>
    @endforelse

    <div class="footer">Generated from ZomatoClone Owner Panel</div>

    <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
