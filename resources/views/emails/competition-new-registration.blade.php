<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New entry</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #292524;">
    @php
        $regs = $registrations->values();
        $first = $regs->first();
    @endphp
    <p style="margin: 0 0 1rem;">Someone has registered for <strong>{{ $competition->title }}</strong>@if ($regs->count() > 1) ({{ $regs->count() }} people)@endif.</p>
    @if ($first?->squad)
        <p style="margin: 0 0 0.75rem; font-size: 14px; color: #57534e;">
            <strong>Squad:</strong> {{ $first->squad->label() }} ({{ $first->squad->starts_at->timezone('Europe/London')->format('D j M Y, g:ia') }})
        </p>
    @else
        <p style="margin: 0 0 0.75rem; font-size: 14px; color: #57534e;"><strong>Format:</strong> Open entry</p>
    @endif
    <table style="border-collapse: collapse; font-size: 14px; width: 100%;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 6px 12px 6px 0; color: #78716c; font-weight: 600;">Name</th>
                <th style="text-align: left; padding: 6px 12px 6px 0; color: #78716c; font-weight: 600;">CPSA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($regs as $r)
                <tr>
                    <td style="padding: 4px 12px 4px 0; vertical-align: top;">{{ $r->entrant_name }}</td>
                    <td style="padding: 4px 12px 4px 0; vertical-align: top;">{{ $r->cpsa_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($first)
        <table style="border-collapse: collapse; font-size: 14px; margin-top: 1rem;">
            <tr><td style="padding: 4px 12px 4px 0; color: #78716c;">Contact email</td><td><a href="mailto:{{ $first->email }}">{{ $first->email }}</a></td></tr>
            <tr><td style="padding: 4px 12px 4px 0; color: #78716c;">Contact telephone</td><td>{{ $first->telephone }}</td></tr>
        </table>
    @endif
    <p style="margin: 1.25rem 0 0;">
        <a href="{{ route('account.competitions.registrations.index', $competition, absolute: true) }}">View all registrations</a>
    </p>
</body>
</html>
