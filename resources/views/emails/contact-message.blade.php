<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact message</title>
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; color: #292524; max-width: 40rem; margin: 0 auto; padding: 1.5rem;">
    <p style="margin: 0 0 1rem; font-size: 0.875rem; color: #78716c;">
        New message via {{ config('app.name') }} contact form
    </p>
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9375rem;">
        <tr>
            <td style="padding: 0.35rem 0; vertical-align: top; color: #78716c; width: 6rem;">Name</td>
            <td style="padding: 0.35rem 0;">{{ $senderName }}</td>
        </tr>
        <tr>
            <td style="padding: 0.35rem 0; vertical-align: top; color: #78716c;">Email</td>
            <td style="padding: 0.35rem 0;"><a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a></td>
        </tr>
        @if ($senderPhone)
            <tr>
                <td style="padding: 0.35rem 0; vertical-align: top; color: #78716c;">Phone</td>
                <td style="padding: 0.35rem 0;">{{ $senderPhone }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding: 0.35rem 0; vertical-align: top; color: #78716c;">Subject</td>
            <td style="padding: 0.35rem 0;">{{ $subjectLine }}</td>
        </tr>
    </table>
    <div style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #e7e5e4;">
        <p style="margin: 0 0 0.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #78716c;">Message</p>
        <p style="margin: 0; white-space: pre-wrap;">{{ $bodyText }}</p>
    </div>
</body>
</html>
