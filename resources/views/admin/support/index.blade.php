<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ticket di Supporto</h2>
            <span class="text-sm text-gray-500">Admin</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-copam-blue px-6 py-4">
                    <h3 class="text-base font-semibold text-white">Tutti i Ticket</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Utente</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Categoria</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Oggetto</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Data</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Stato</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($tickets as $ticket)
                                <tr
                                    class="{{ $loop->even ? 'bg-gray-50/50' : 'bg-white' }} hover:bg-blue-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $ticket->id }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">{{ $ticket->user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $ticket->user->email }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600">
                                        {{ \App\Models\SupportTicket::$categories[$ticket->category] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ $ticket->subject }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                                        {{ $ticket->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span @class([
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
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
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.support.show', $ticket) }}"
                                            class="text-xs font-medium text-copam-blue hover:underline">
                                            Visualizza
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400">
                                        Nessun ticket presente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($tickets->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
