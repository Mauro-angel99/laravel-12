<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Fasi di lavoro prese in carico') }}
        </h2>
    </x-slot>

    @section('content')
        <div class="py-1">
            <div class="w-full">
                <assigned-work-phase-list
                    :is-admin="{{ Auth::user()->roles()->where('name', 'admin')->exists() ? 1 : 0 }}"></assigned-work-phase-list>
            </div>
        </div>
    @endsection
</x-app-layout>
