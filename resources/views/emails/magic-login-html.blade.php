<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #292524; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem;">Sign in to <strong>{{ config('app.name') }}</strong>:</p>
    <p style="margin: 0 0 1.5rem;">
        <a href="{{ $actionUrl }}" style="display: inline-block; background: #1e3a32; color: #fff; text-decoration: none; padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600;">Sign in</a>
    </p>
    <p style="margin: 0; font-size: 0.875rem; color: #78716c;">This link expires in one hour. If you didn’t ask for it, you can ignore this email.</p>
</body>
</html>
