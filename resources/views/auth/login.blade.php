<x-guest-layout>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        const notyf = new Notyf({
            duration: 2500,
            position: { x: "right", y: "top" }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            @if(session('notify_success'))
                notyf.success("{{ session('notify_success') }}");
            @endif

            @if(session('notify_error'))
                notyf.error("{{ session('notify_error') }}");
            @endif

            @if($errors->any())
                notyf.error("{{ $errors->first() }}");
            @endif

});
    </script>
    <form class="card card-md" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="card-body">

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required
                    autofocus>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-2">
                <label class="form-label">Senha</label>
                <input id="password" name="password" type="password" class="form-control" required>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" name="remember">
                        <span class="form-check-label">Lembrar-me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" tabindex="-1" class="text-sm">Esqueci minha senha</a>
                    @endif
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </div>
        </div>

    </form>
</x-guest-layout>