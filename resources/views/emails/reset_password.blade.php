<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Cabinet Grotesk', Arial, sans-serif;
            background-color: #f6f6f6;
            color: #3d3d3d;
            padding: 40px 0;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        h1 {
            color: #b78f3b;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        p {
            line-height: 1.6;
            margin-bottom: 16px;
            font-size: 15px;
        }
        .btn {
            display: inline-block;
            background-color: #b78f3b;
            color: white;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out;
        }
        .btn:hover {
            background-color: #9d7231;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #6D6D6D;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        <p>
            You are receiving this email because we received a password reset request for your account.
        </p>
        <p style="text-align: center;">
            <a class="btn" href="{{ $url }}">Reset Password</a>
        </p>
        <p>
            This password reset link will expire in <strong>60 minutes</strong>.
        </p>
        <p>
            If you did not request a password reset, no further action is required.
        </p>
        <div class="footer">
            &copy; {{ date('Y') }} <a href="https://seco.id/" target="_blank">seco</a>. All rights reserved.
        </div>
    </div>
</body>
</html>
