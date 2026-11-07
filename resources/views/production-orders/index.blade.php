<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Stato Ordini di Produzione') }}
        </h2>
    </x-slot>

    @section('content')
        <div class="py-4">
            <div class="w-full">
                <production-orders-list></production-orders-list>
            </div>
        </div>
    @endsection
</x-app-layout>
