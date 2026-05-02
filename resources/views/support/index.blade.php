<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Supporto
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-copam-blue px-6 py-4">
                    <h3 class="text-base font-semibold text-white">Invia un messaggio</h3>
                    <p class="text-xs text-white/70 mt-0.5">Compila il modulo, ti risponderemo via email.</p>
                </div>

                <form method="POST" action="{{ route('support.store') }}" class="px-6 py-6 space-y-5">
                    @csrf

                    {{-- Categoria --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            Categoria *
                        </label>
                        <select name="category" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue bg-white @error('category') border-red-400 @enderror">
                            <option value="">— Seleziona —</option>
                            @foreach (\App\Models\SupportTicket::$categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Oggetto --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            Oggetto *
                        </label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="255"
                            placeholder="Descrivi brevemente il problema"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue @error('subject') border-red-400 @enderror" />
                        @error('subject')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Messaggio --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            Messaggio *
                        </label>
                        <textarea name="message" rows="6" required maxlength="5000"
                            placeholder="Descrivi il problema o la tua richiesta nel dettaglio..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-copam-blue focus:border-copam-blue resize-none @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-2 border-t border-gray-100">
                        <button type="submit"
                            class="px-5 py-2 text-sm font-medium text-white bg-copam-blue rounded-lg hover:bg-copam-blue/90 transition-colors focus:outline-none focus:ring-2 focus:ring-copam-blue">
                            Invia messaggio
                        </button>
                    </div>
                </form>
            </div>

            {{-- Storico ticket dell'utente --}}
            @php
                $myTickets = \App\Models\SupportTicket::where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
            @endphp

            @if ($myTickets->isNotEmpty())
                <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-3">
                        <h4 class="text-sm font-semibold text-gray-700">I tuoi ticket recenti</h4>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($myTickets as $ticket)
                            <li class="px-6 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $ticket->subject }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ \App\Models\SupportTicket::$categories[$ticket->category] }} &middot;
                                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                                        </p>
                                        @if ($ticket->admin_reply)
                                            <div
                                                class="mt-2 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 text-xs text-gray-700">
                                                <p class="font-semibold text-copam-blue mb-1">Risposta:</p>
                                                {{ $ticket->admin_reply }}
                                            </div>
                                        @endif
                                    </div>
                                    <span @class([
                                        'flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
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
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
