<?php

namespace App\Http\Controllers;

use App\Mail\SupportTicketReceived;
use App\Mail\SupportTicketReplied;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SupportTicketController extends Controller
{
    public function index()
    {
        return view('support.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:billing,technical_error,strategic_suggestion',
            'subject'  => 'required|string|max:255',
            'message'  => 'required|string|max:5000',
        ]);

        $ticket = SupportTicket::create([
            ...$validated,
            'user_id' => Auth::id(),
            'status'  => 'open',
        ]);

        // Notifica all'admin
        $adminEmail = config('mail.support_admin_email', env('SUPPORT_ADMIN_EMAIL'));
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new SupportTicketReceived($ticket));
        }

        return redirect()->route('support.index')
            ->with('success', 'Il tuo messaggio è stato inviato. Ti risponderemo al più presto.');
    }

    // ── ADMIN ──────────────────────────────────────────────────────────────

    public function adminIndex()
    {
        $tickets = SupportTicket::with('user')
            ->orderByRaw("FIELD(status, 'open', 'replied', 'closed')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.support.index', compact('tickets'));
    }

    public function adminShow(SupportTicket $ticket)
    {
        $ticket->load('user', 'repliedBy');
        return view('admin.support.show', compact('ticket'));
    }

    public function adminReply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string|max:10000',
        ]);

        $ticket->update([
            'admin_reply' => $validated['admin_reply'],
            'status'      => 'replied',
            'replied_at'  => now(),
            'replied_by'  => Auth::id(),
        ]);

        // Manda email all'utente con la risposta
        Mail::to($ticket->user->email)->send(new SupportTicketReplied($ticket));

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Risposta inviata con successo.');
    }

    public function adminClose(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'closed']);
        return redirect()->route('admin.support.index')
            ->with('success', 'Ticket chiuso.');
    }
}
