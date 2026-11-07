<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Articoli') }}
        </h2>
    </x-slot>

    @section('content')
        <div class="py-1">
            <div class="w-full">
                <article-search-list></article-search-list>
            </div>
        </div>
    @endsection
</x-app-layout>
