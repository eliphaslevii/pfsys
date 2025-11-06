<x-guest-layout>
    <style>
        body {
            background: url('{{ asset('images/bg.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45); /* overlay escuro */
            backdrop-filter: blur(3px); /* efeito vidro, sutil */
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card {
            width: 100%;
            max-width: 380px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
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