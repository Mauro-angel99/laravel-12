<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Magazzino') }}
        </h2>
    </x-slot>

    @section('content')
    <div class="py-1">
        <div class="max-w-7xl mx-auto sm:px-0 md:px-0 lg:px-0 xl:px-8">
            <warehouse-list></warehouse-list>
        </div>
    </div>
    @endsection
</x-app-layout>
