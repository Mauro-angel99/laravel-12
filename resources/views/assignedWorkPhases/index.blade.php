<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Fasi di lavoro prese in carico') }}
        </h2>
    </x-slot>

    @section('content')
        <div class="py-1">
            <div class="max-w-8xl mx-auto sm:px-0 md:px-0 lg:px-0 xl:px-8">
                <assigned-work-phase-list
                    :is-admin="{{ Auth::user()->roles()->where('name', 'admin')->exists() ? 1 : 0 }}"></assigned-work-phase-list>
            </div>
        </div>
    @endsection
</x-app-layout>
