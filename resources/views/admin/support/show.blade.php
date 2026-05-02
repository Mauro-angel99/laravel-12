<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.support.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ticket #{{ $ticket->id }} — {{ $ticket->subject }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Dettagli ticket --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-copam-blue px-6 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-white">{{ $ticket->subject }}</h3>
                        <p class="text-xs text-white/70 mt-0.5">
                            {{ \App\Models\SupportTicket::$categories[$ticket->category] }}
                            &middot; {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <span @class([
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                        'bg-yellow-100 text-yellow-700' => $ticket->status === 'open',
                        'bg-green-100 text-green-700' => $ticket->status === 'replied',
                        'bg-gray-100 text-gray-500' => $ticket->status === 'closed',
                    ])>
                        {{ match ($ticket->status) {
                            'open' => 'Aperto',
                            'replied' => 'Risposto',
                            'closed' => 'Chiuso',
                        } }}
                    </span>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Da</p>
                        <p class="text-sm text-gray-800">{{ $ticket->user->name }}
                            <span class="text-gray-400">&lt;{{ $ticket->user->email }}&gt;</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Messaggio</p>
                        <div class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-700 whitespace-pre-wrap">
                            {{ $ticket->message }}</div>
                    </div>
                </div>
            </div>

            {{-- Risposta admin esistente --}}
            @if ($ticket->admin_reply)
                <div class="bg-blue-50 border border-blue-100 rounded-xl px-6 py-4">
                    <p class="text-xs font-semibold text-copam-blue uppercase tracking-wide mb-2">
                        Risposta inviata il {{ $ticket->replied_at?->format('d/m/Y H:i') }}
                        @if ($ticket->repliedBy)
                            da {{ $ticket->repliedBy->name }}
                        @endif
                    </p>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $ticket->admin_reply }}</p>
                </div>
            @endif

            {{-- Form risposta --}}
            @if ($ticket->status !== 'closed')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h4 class="text-sm font-semibold text-gray-700">
                            {{ $ticket->admin_reply ? 'Aggiorna risposta' : 'Rispondi' }}
                        </h4>
                    </div>
                    <form method="POST" action="{{ route('admin.support.reply', $ticket) }}"
                        class="px-6 py-5 space-y-4">
                        @csrf
                        <textarea name="admin_reply" rows="6" required placeholder="Scrivi la risposta da inviare via email all'utente..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue resize-none @error('admin_reply') border-red-400 @enderror">{{ old('admin_reply', $ticket->admin_reply) }}</textarea>
                        @error('admin_reply')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <form method="POST" action="{{ route('admin.support.close', $ticket) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="text-xs text-gray-400 hover:text-red-600 transition-colors"
                                    onclick="return confirm('Chiudere il ticket?')">
                                    Chiudi ticket
                                </button>
                            </form>
                            <button type="submit"
                                class="px-5 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors focus:outline-none focus:ring-2 focus:ring-copam-blue">
                                Invia risposta via email
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="text-center py-4">
                    <form method="POST" action="{{ route('admin.support.close', $ticket) }}">
                        @csrf
                        @method('PATCH')
                    </form>
                    <p class="text-sm text-gray-400">Ticket chiuso.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
