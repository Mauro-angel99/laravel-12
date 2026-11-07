<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Fasi di Lavoro') }}
        </h2>
    </x-slot>

    @section('content')
        <div class="py-1">
            <div class="w-full">
                <work-phase-list></work-phase-list>
            </div>
        </div>
    @endsection
</x-app-layout>
