<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay — Zomato Clone</title>
    <style>
        * { box-sizing: border-box; }
        body {
            background: #f8f9fa; font-family: 'Segoe UI', sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .pay-card {
            background: white; border-radius: 20px; padding: 40px 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08); text-align: center; max-width: 380px; width: 90%;
        }
        .pay-icon { font-size: 48px; margin-bottom: 12px; }
        h5 { font-weight: 700; margin-bottom: 6px; }
        p { color: #888; font-size: 14px; margin-bottom: 24px; }
        .amount { font-size: 28px; font-weight: 700; color: #e8342a; margin-bottom: 24px; }
        .btn-pay {
            background: #e8342a; color: white; border: none; border-radius: 50px;
            padding: 12px 32px; font-weight: 600; font-size: 15px; cursor: pointer; width: 100%;
        }
        .btn-cancel {
            background: transparent; color: #888; border: none; margin-top: 14px;
            font-size: 13px; text-decoration: underline; cursor: pointer;
        }
        .spinner {
            width: 22px; height: 22px; border: 3px solid rgba(255,255,255,0.4);
            border-top-color: white; border-radius: 50%; display: none;
            animation: spin 0.7s linear infinite; margin: 0 auto;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="pay-card">
        <div class="pay-icon">💳</div>
        <h5>Complete your payment</h5>
        <p>{{ $order->restaurant->name ?? 'Zomato Clone' }} · Secure payment via Razorpay</p>
        <div class="amount">₹{{ number_format($order->total, 2) }}</div>

        <button id="payBtn" class="btn-pay">
            <span id="payBtnText">Pay Now</span>
            <div id="payBtnSpinner" class="spinner"></div>
        </button>

        <form id="verifyForm" action="{{ route('customer.payment.razorpay.verify', $order) }}" method="POST" style="display:none">
            @csrf
            <input type="hidden" name="razorpay_payment_id" id="rp_payment_id">
            <input type="hidden" name="razorpay_order_id" id="rp_order_id">
            <input type="hidden" name="razorpay_signature" id="rp_signature">
        </form>

        <a href="{{ route('customer.payment.razorpay.cancel', $order) }}"><button type="button" class="btn-cancel">Cancel and go back</button></a>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    const options = {
        key: @json($keyId),
        amount: @json($razorpayOrder['amount']),
        currency: @json($razorpayOrder['currency']),
        name: "ZomatoClone",
        description: "Order payment",
        order_id: @json($razorpayOrder['id']),
        prefill: {
            name: @json(auth()->user()->name),
            email: @json(auth()->user()->email),
        },
        theme: { color: "#e8342a" },
        handler: function (response) {
            document.getElementById('rp_payment_id').value = response.razorpay_payment_id;
            document.getElementById('rp_order_id').value = response.razorpay_order_id;
            document.getElementById('rp_signature').value = response.razorpay_signature;
            document.getElementById('verifyForm').submit();
        },
        modal: {
            ondismiss: function () {
                window.location.href = @json(route('customer.payment.razorpay.cancel', $order));
            }
        }
    };

    const rzp = new Razorpay(options);

    function openCheckout() {
        document.getElementById('payBtnText').style.display = 'none';
        document.getElementById('payBtnSpinner').style.display = 'block';
        rzp.open();
    }

    document.getElementById('payBtn').addEventListener('click', openCheckout);
    // Auto-open on page load so the customer isn't stuck on a blank page waiting.
    window.addEventListener('load', openCheckout);
    </script>
</body>
</html>
