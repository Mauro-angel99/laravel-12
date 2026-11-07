@props(['header' => null])

@php
    // Comunicazioni assegnate all'utente e non ancora lette (valore iniziale del badge nel menu, poi aggiornato via polling)
    $unreadCommunications = Auth::user()->receivedCommunications()->wherePivotNull('read_at')->count();
@endphp

{{--
    Guscio autenticato con navigazione laterale.

    Da `lg` in su la sidebar e sempre visibile; sotto diventa un pannello che
    scorre sopra il contenuto, perche su tablet e telefono lo spazio
    orizzontale serve alle tabelle.
--}}
<div x-data="{ menuOpen: false }" x-init="$store.communications.start({{ $unreadCommunications }})"
    @keydown.escape.window="menuOpen = false" class="min-h-screen bg-white">

    {{-- Sidebar fissa: fuori dal flusso, il contenuto le lascia spazio col padding. --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 border-r border-gray-200 lg:block print:!hidden">
        @include('layouts.sidebar', ['unreadCommunications' => $unreadCommunications])
    </aside>

    <div @click="menuOpen = false" aria-hidden="true"
        :class="menuOpen ? 'opacity-100' : 'pointer-events-none opacity-0'"
        class="pointer-events-none fixed inset-0 z-40 bg-gray-900/20 opacity-0 transition-opacity duration-200 motion-reduce:transition-none lg:hidden print:!hidden">
    </div>

    <aside x-effect="$el.inert = !menuOpen" inert
        :class="menuOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-[45] w-64 -translate-x-full border-r border-gray-200 bg-white shadow-xl transition-transform duration-200 ease-out motion-reduce:transition-none lg:hidden print:!hidden">
        @include('layouts.sidebar', ['unreadCommunications' => $unreadCommunications])
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-30 border-b border-gray-200 bg-white print:!hidden">
            <div class="flex h-14 items-center gap-3 px-4">
                <button type="button" @click="menuOpen = !menuOpen"
                    :aria-label="menuOpen ? 'Chiudi il menu' : 'Apri il menu'" aria-label="Apri il menu"
                    class="inline-flex h-8 items-center justify-center rounded-md px-2 text-gray-700 transition-colors hover:bg-gray-100 lg:hidden">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <g x-show="!menuOpen"><line x1="4" x2="20" y1="12" y2="12" /><line x1="4" x2="20" y1="6" y2="6" /><line x1="4" x2="20" y1="18" y2="18" /></g>
                        <g x-show="menuOpen" style="display: none"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></g>
                    </svg>
                </button>

                @if ($header)
                    <div class="min-w-0 truncate [&_h2]:text-left">
                        {{ $header }}
                    </div>
                @endif

                <div class="ml-auto flex items-center gap-3 text-sm">
                    <a href="{{ route('profile.edit') }}" title="{{ __('Profilo') }}"
                        class="hidden text-gray-500 underline-offset-4 hover:text-gray-900 hover:underline sm:inline">
                        {{ Auth::user()->name }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex h-8 items-center gap-2 rounded-md px-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100">
                            <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><polyline points="16 17 21 12 16 7" /><line x1="21" x2="9" y1="12" y2="12" />
                            </svg>
                            {{ __('Esci') }}
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main {{ $attributes->merge(['class' => 'px-4 py-4 sm:px-6']) }}>
            {{ $slot }}
        </main>
    </div>
</div>
