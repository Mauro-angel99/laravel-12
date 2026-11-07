@php
    $user = Auth::user();
    $isAdmin = $user->hasRole('admin');

    // Icone lucide (viewBox 24, stroke): solo il contenuto interno dell'<svg>.
    $icons = [
        'gestione' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        'lavorazioni' => '<path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/>',
        'distinta' => '<path d="M21 12h-8"/><path d="M21 6H8"/><path d="M21 18h-8"/><path d="M3 6v4c0 1.1.9 2 2 2h3"/><path d="M3 10v6c0 1.1.9 2 2 2h3"/>',
        'ordini' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
        'inizio' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'posizioni' => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
        'lamiere' => '<path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>',
        'articoli' => '<rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>',
        'inbox' => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'megafono' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'utenti' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'impostazioni' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
    ];

    // Navigazione a sezioni; le voci con `visible` a false non vengono mostrate.
    $sections = [
        [
            'label' => 'Produzione',
            'items' => [
                ['route' => 'workphases.index', 'label' => 'Gestione', 'icon' => 'gestione'],
                ['route' => 'assigned-work-phases.index', 'label' => 'Lavorazioni', 'icon' => 'lavorazioni'],
                ['route' => 'distinta-base.index', 'label' => 'Distinta Base', 'icon' => 'distinta'],
                ['route' => 'production-orders.index', 'label' => 'Stato Ordini', 'icon' => 'ordini'],
                ['route' => 'acknowledgements.index', 'label' => 'Inizio Lavoro', 'icon' => 'inizio', 'visible' => $isAdmin],
            ],
        ],
        [
            'label' => 'Magazzino',
            'items' => [
                ['route' => 'warehouse.index', 'label' => 'Posizioni', 'icon' => 'posizioni'],
                ['route' => 'sheets.index', 'label' => 'Lamiere', 'icon' => 'lamiere'],
                ['route' => 'articles.index', 'label' => 'Articoli', 'icon' => 'articoli'],
            ],
        ],
        [
            'label' => 'Comunicazioni',
            'items' => [
                ['route' => 'communications.inbox', 'label' => 'Comunicazioni', 'icon' => 'inbox', 'badge' => true],
                ['route' => 'communications.manage', 'label' => 'Gestione Comunicazioni', 'icon' => 'megafono', 'visible' => (bool) $user->is_moderator],
            ],
        ],
    ];

    // Fissata in fondo alla sidebar, fuori dallo scorrimento delle altre sezioni.
    $settingsSection = [
        'label' => 'Impostazioni',
        'items' => [
            ['route' => 'users.index', 'active' => 'users.*', 'label' => 'Utenti', 'icon' => 'utenti', 'visible' => $isAdmin],
            ['route' => 'general.edit', 'active' => 'general.*', 'label' => 'Generali', 'icon' => 'impostazioni', 'visible' => $isAdmin],
        ],
    ];
@endphp

<div class="flex h-full flex-col bg-gray-50">
    {{-- Stessa altezza della barra in alto, cosi i bordi inferiori restano allineati;
         px-6 = px-3 del nav + px-3 delle voci, per partire dove partono le icone. --}}
    <a href="{{ route('dashboard') }}" class="flex h-14 shrink-0 items-center border-b border-gray-200 px-6 py-1">
        <x-application-logo class="h-full w-auto" />
    </a>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        @foreach ($sections as $section)
            @include('layouts.sidebar-section', ['section' => $section, 'class' => 'mb-4'])
        @endforeach
    </nav>

    @if ($isAdmin)
        <div class="shrink-0 border-t border-gray-200 px-3 py-3">
            @include('layouts.sidebar-section', ['section' => $settingsSection])
        </div>
    @endif
</div>
