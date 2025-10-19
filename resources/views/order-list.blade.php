<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('db_error'))
                        <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded mb-4">
                            ERRORE CONNESSIONE DB: {{ session('db_error') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-bold mt-4 mb-2">Dati di Prova: dbo.A01_ORD_FAS (MSSQL)</h3>
                    
                    @if(is_array($dati_tab_cab) && count($dati_tab_cab) > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RECORD_ID</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FLASS</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FLDES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($dati_tab_cab as $riga)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $riga->RECORD_ID }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $riga->FLASS }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $riga->FLDES }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif (is_array($dati_tab_cab))
                         <p class="text-yellow-600">Connessione OK, ma la tabella è vuota.</p>
                    @else
                        <p class="text-red-600">Errore: Dati non recuperati. Controlla il messaggio di errore sopra.</p>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
