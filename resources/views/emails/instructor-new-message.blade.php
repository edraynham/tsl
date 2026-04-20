<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New instructor enquiry</title>
</head>
<body style="margin:0; padding:24px 12px; background:#f5f5f4; color:#292524; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
                    <tr>
                        <td style="background:#ffffff; border:1px solid #e7e5e4; border-radius:16px; padding:24px;">
                            <p style="margin:0 0 8px; font-size:12px; letter-spacing:0.08em; text-transform:uppercase; color:#78716c; font-weight:600;">{{ config('app.name') }}</p>
                            <h1 style="margin:0 0 10px; font-size:26px; line-height:1.2; color:#1e3a32;">New instructor message</h1>
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#44403c;">
                                You have received a new enquiry for <strong>{{ $instructor->name }}</strong>.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px; font-size:14px; color:#44403c;">
                                <tr><td style="padding:2px 0; width:110px; color:#78716c;">From</td><td style="padding:2px 0;">{{ $messageRecord->sender_name }}</td></tr>
                                <tr><td style="padding:2px 0; color:#78716c;">Email</td><td style="padding:2px 0;">{{ $messageRecord->sender_email }}</td></tr>
                                @if ($messageRecord->sender_phone)
                                    <tr><td style="padding:2px 0; color:#78716c;">Phone</td><td style="padding:2px 0;">{{ $messageRecord->sender_phone }}</td></tr>
                                @endif
                                @if ($messageRecord->skill_level)
                                    <tr><td style="padding:2px 0; color:#78716c;">Skill level</td><td style="padding:2px 0;">{{ ucfirst($messageRecord->skill_level) }}</td></tr>
                                @endif
                            </table>

                            <p style="margin:0 0 16px;">
                                <a href="{{ $accountUrl }}" style="display:inline-block; background:#1e3a32; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:10px; font-size:15px; font-weight:700;">
                                    View in account area
                                </a>
                            </p>

                            <p style="margin:0; font-size:12px; color:#78716c;">
                                Sign in and open your Instructor account section to read and manage messages.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
