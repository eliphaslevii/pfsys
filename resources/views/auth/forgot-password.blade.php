<x-guest-layout>

    {{-- Notyf --}}
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

            @if(session('status'))
                notyf.success("{{ session('status') }}");
            @endif

            @if($errors->any())
                notyf.error("{{ $errors->first() }}");
            @endif

        });
    </script>

    <form class="card card-md" method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="card-body">

            <h2 class="h2 text-center mb-4">Recuperar Senha</h2>

            <p class="text-muted text-center mb-4">
                Informe seu e-mail e enviaremos um link para redefinir sua senha.
            </p>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input id="email" name="email" type="email"
                       class="form-control" value="{{ old('email') }}" required autofocus>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">
                    Enviar Link de Recuperação
                </button>
            </div>

        </div>

    </form>

</x-guest-layout>
