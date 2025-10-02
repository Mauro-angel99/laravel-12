<x-layouts.app>
    <div class="container">
        <h1>Modifica Ruolo - {{ $user->name }}</h1>

        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="role">Ruolo</label>
                <select name="role" id="role" class="form-control">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-success">Aggiorna</button>
        </form>
    </div>
</x-layouts.app>
