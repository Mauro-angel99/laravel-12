<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Impostazioni Generali') }}
        </h2>
    </x-slot>

    @section('content')
        <div class="py-1">
            <div class="w-full">
                <work-parameters-list
                    :is-admin="{{ Auth::user()->hasRole('admin') ? 'true' : 'false' }}"></work-parameters-list>
            </div>
        </div>
    @endsection
</x-app-layout>
