<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in to {{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background:#f5f5f4; color:#292524; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f4; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
                    <tr>
                        <td style="padding:0 8px 12px; text-align:left; font-size:12px; letter-spacing:0.08em; text-transform:uppercase; color:#78716c; font-weight:600;">
                            {{ config('app.name') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff; border:1px solid #e7e5e4; border-radius:16px; padding:28px 24px;">
                            <h1 style="margin:0 0 10px; font-size:28px; line-height:1.2; color:#1e3a32; font-weight:700;">Your sign-in link is ready</h1>
                            <p style="margin:0 0 18px; font-size:15px; line-height:1.6; color:#44403c;">
                                Click below to securely sign in to <strong>{{ config('app.name') }}</strong>.
                            </p>

                            <p style="margin:0 0 18px;">
                                <a
                                    href="{{ $actionUrl }}"
                                    style="display:inline-block; background:#1e3a32; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:10px; font-size:15px; font-weight:700;"
                                >Sign in now</a>
                            </p>

                            <p style="margin:0 0 12px; font-size:13px; line-height:1.6; color:#57534e;">
                                This link expires in <strong>1 hour</strong> and can only be used once.
                            </p>
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#78716c;">
                                If the button does not work, copy and paste this URL into your browser:
                                <br>
                                <a href="{{ $actionUrl }}" style="color:#1e3a32; word-break:break-all;">{{ $actionUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 8px 0; font-size:12px; line-height:1.6; color:#78716c;">
                            If you did not request this email, you can safely ignore it.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
