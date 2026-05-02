<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risposta al tuo ticket</title>
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

        .original {
            background: #f9fafb;
            border-left: 3px solid #d1d5db;
            padding: 10px 14px;
            font-size: 13px;
            color: #6b7280;
            white-space: pre-wrap;
            margin-bottom: 20px;
            border-radius: 0 6px 6px 0;
        }

        .reply-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px;
            font-size: 14px;
            color: #1e3a5f;
            white-space: pre-wrap;
            margin-bottom: 20px;
        }

        .footer {
            padding: 16px 32px;
            border-top: 1px solid #f3f4f6;
            font-size: 12px;
            color: #9ca3af;
        }

        .btn {
            display: inline-block;
            background: #1e3a5f;
            color: #fff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .02em;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            <h1>Ciao, {{ $ticket->user->name }}!</h1>
            <p>Abbiamo risposto al tuo ticket #{{ $ticket->id }}</p>
        </div>
        <div class="body">
            <div class="label">Il tuo messaggio originale</div>
            <div class="original">{{ $ticket->subject }}
                {{ $ticket->message }}</div>

            <div class="label">La nostra risposta</div>
            <div class="reply-box">{{ $ticket->admin_reply }}</div>

            <a href="{{ route('support.index') }}" class="btn">Continua la conversazione nel pannello</a>
        </div>
        <div class="footer">
            {{ config('app.name') }} &middot; Questo messaggio è stato inviato automaticamente, non rispondere
            direttamente a questa email.
        </div>
    </div>
</body>

</html>
