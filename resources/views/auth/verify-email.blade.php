<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Grazie per esserti registrato! Prima di iniziare, potresti verificare il tuo indirizzo email cliccando sul link che ti abbiamo appena inviato? Se non hai ricevuto l'email, saremo lieti di inviartene un'altra.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Un nuovo link di verifica è stato inviato all'indirizzo email che hai fornito durante la registrazione.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Reinvia Email di Verifica
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-copam-blue">
                Esci
            </button>
        </form>
    </div>
</x-guest-layout>
