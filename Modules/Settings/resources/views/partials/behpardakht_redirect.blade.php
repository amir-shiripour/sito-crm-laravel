<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>در حال انتقال به درگاه به‌پرداخت ملت...</title>
    <style>
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            padding: 32px;
            max-width: 440px;
            width: 100%;
            text-align: center;
        }
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #f1f5f9;
            border-top: 4px solid #dc2626;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            font-size: 18px;
            color: #1e293b;
            margin: 0 0 8px;
            font-weight: 700;
        }
        p {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 24px;
            line-height: 1.6;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h2>در حال انتقال به درگاه به‌پرداخت ملت</h2>
        <p>لطفاً چند لحظه صبر کنید. شما در حال انتقال به درگاه امن شاپرک هستید...</p>

        <form id="mellat-redirect-form" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="POST">
            <input type="hidden" name="RefId" value="{{ $refId }}">
            @if(!empty($mobile))
                <input type="hidden" name="MobileNo" value="{{ $mobile }}">
            @endif
            <button type="submit" class="btn">انتقال مستقیم به بانک</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('mellat-redirect-form').submit();
        });
    </script>
</body>
</html>
