<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New entry</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #292524;">
    <p style="margin: 0 0 1rem;">Someone has registered for <strong>{{ $competition->title }}</strong>.</p>
    <table style="border-collapse: collapse; font-size: 14px;">
        <tr><td style="padding: 4px 12px 4px 0; color: #78716c;">Name</td><td>{{ $registration->entrant_name }}</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #78716c;">CPSA no.</td><td>{{ $registration->cpsa_number }}</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #78716c;">Email</td><td><a href="mailto:{{ $registration->email }}">{{ $registration->email }}</a></td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #78716c;">Telephone</td><td>{{ $registration->telephone }}</td></tr>
        @if ($registration->squad)
            <tr><td style="padding: 4px 12px 4px 0; color: #78716c;">Squad</td><td>{{ $registration->squad->label() }} ({{ $registration->squad->starts_at->timezone('Europe/London')->format('D j M Y, g:ia') }})</td></tr>
        @else
            <tr><td style="padding: 4px 12px 4px 0; color: #78716c;">Format</td><td>Open entry</td></tr>
        @endif
    </table>
    <p style="margin: 1.25rem 0 0;">
        <a href="{{ route('account.competitions.registrations.index', $competition, absolute: true) }}">View all registrations</a>
    </p>
</body>
</html>
