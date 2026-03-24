<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827;">
    <p>Hello {{ $name }},</p>

    <p>Use this 6-digit verification code to continue:</p>

    <p style="font-size: 28px; letter-spacing: 6px; font-weight: bold; margin: 20px 0;">
        {{ $code }}
    </p>

    <p>This code will expire in 10 minutes.</p>

    <p>If you did not request this code, you can ignore this email.</p>
</body>
</html>
