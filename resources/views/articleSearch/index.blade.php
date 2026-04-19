<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Ricerca Articolo') }}
        </h2>
    </x-slot>

    @section('content')
        <div class="py-1">
            <div class="max-w-8xl mx-auto sm:px-0 md:px-0 lg:px-0 xl:px-8">
                <article-search-list></article-search-list>
            </div>
        </div>
    @endsection
</x-app-layout>
