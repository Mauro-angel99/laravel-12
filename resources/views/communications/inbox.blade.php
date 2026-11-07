<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center py-0">
            {{ __('Comunicazioni') }}
        </h2>
    </x-slot>

    @section('content')
        <div class="py-1">
            <div class="w-full">
                <communication-inbox-list></communication-inbox-list>
            </div>
        </div>
    @endsection
</x-app-layout>
