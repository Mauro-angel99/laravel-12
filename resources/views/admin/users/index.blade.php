<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Gestione Utenti') }}
        </h2>
    </x-slot>

    <div class="py-1">
        <div class="max-w-7xl mx-auto sm:px-0 lg:px-0 xl:px-8">
            <div class="p-3 sm:p-3 bg-white shadow sm:rounded-lg">
                <!-- Search Bar -->
                <div class="mb-6">
                    <form method="GET" action="{{ route('users.index') }}" class="flex gap-4 items-end">
                        <div class="flex-1">
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="search" 
                                    id="search" 
                                    value="{{ request('search') }}" 
                                    placeholder="Cerca utenti..." 
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-copam-blue focus:border-copam-blue sm:text-sm"
                                />
                                
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-copam-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-copam-blue-hover focus:bg-copam-blue-hover active:bg-copam-blue-active focus:outline-none focus:ring-2 focus:ring-copam-blue focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Cerca
                            </button>
                            @if(request('search'))
                                <a 
                                    href="{{ route('users.index') }}" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue"
                                >
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancella
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
                <!-- Actions -->
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <select name="per_page" id="per_page" onchange="this.form.submit()" class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-copam-blue focus:ring-copam-blue sm:text-sm">
                            @foreach([10,25,50,100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-copam-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-copam-blue-hover focus:bg-copam-blue-hover active:bg-copam-blue-active focus:outline-none focus:ring-2 focus:ring-copam-blue focus:ring-offset-2 transition ease-in-out duration-150">
                            + Crea Utente
                        </a>
                    </div>
                </div>

                
                <!-- Results info -->
                @if(request('search'))
                    <div class="mb-4 text-sm text-gray-600">
                        @if($users->count() > 0)
                            Trovati {{ $users->count() }} utente{{ $users->count() === 1 ? '' : 'i' }} per "{{ request('search') }}"
                        @else
                            Nessun utente trovato per "{{ request('search') }}"
                        @endif
                    </div>
                @endif

                <div class="overflow-x-auto">
                    @if($users->count() > 0)
                        <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr class="border-b border-gray-200">
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Nome</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">Ruolo</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50 border-b border-gray-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-gray-200">{{ $user->roles->pluck('name')->join(', ') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('users.edit', $user) }}" class="text-white hover:text-white bg-copam-blue hover:bg-copam-blue-hover px-3 py-1 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                                            Modifica
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun utente</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(request('search'))
                                    Prova a modificare i termini di ricerca.
                                @else
                                    Inizia aggiungendo un nuovo utente.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
