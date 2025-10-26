<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Fasi di Lavoro') }}
        </h2>
    </x-slot>

    @section('content')
    <div class="py-5">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <work-phase-list></work-phase-list>
        </div>
    </div>
    @endsection
</x-app-layout>