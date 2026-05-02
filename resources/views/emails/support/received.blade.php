<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo ticket di supporto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            max-width: 560px;
            margin: 0 auto;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .header {
            background: #1e3a5f;
            padding: 24px 32px;
        }

        .header h1 {
            color: #fff;
            font-size: 18px;
            margin: 0;
        }

        .header p {
            color: rgba(255, 255, 255, .7);
            font-size: 13px;
            margin: 4px 0 0;
        }

        .body {
            padding: 24px 32px;
        }

        .label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .value {
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .message-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            color: #374151;
            white-space: pre-wrap;
        }

        .footer {
            padding: 16px 32px;
            border-top: 1px solid #f3f4f6;
            font-size: 12px;
            color: #9ca3af;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            background: #1e3a5f;
            color: #fff;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            <h1>Nuovo Ticket #{{ $ticket->id }}</h1>
            <p>Da {{ $ticket->user->name }} ({{ $ticket->user->email }})</p>
        </div>
        <div class="body">
            <div class="label">Categoria</div>
            <div class="value">{{ \App\Models\SupportTicket::$categories[$ticket->category] }}</div>

            <div class="label">Oggetto</div>
            <div class="value">{{ $ticket->subject }}</div>

            <div class="label">Messaggio</div>
            <div class="message-box">{{ $ticket->message }}</div>

            <a href="{{ route('admin.support.show', $ticket) }}" class="btn">Rispondi nel pannello</a>
        </div>
        <div class="footer">
            {{ config('app.name') }} &middot; {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>
