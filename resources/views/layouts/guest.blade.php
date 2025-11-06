<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Bsys') }}</title>

    <!-- Tabler CSS -->
    <link href="{{ asset('tabler/css/tabler.min.css') }}" rel="stylesheet">
    <link href="{{ asset('tabler/css/tabler-vendors.min.css') }}" rel="stylesheet">
    <link href="{{ asset('tabler/css/demo.min.css') }}" rel="stylesheet">
</head>
<body class="d-flex flex-column bg-body-tertiary">

    <div class="page page-center">
        <div class="container container-tight py-4">
            {{ $slot }}
        </div>
    </div>

    <!-- Tabler JS -->
    <script src="{{ asset('tabler/js/tabler.min.js') }}"></script>
</body>
</html>
