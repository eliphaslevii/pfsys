@extends('layouts.app')

@section('content')
<div class="page-body">
  <div class="container-xl">
    <h2 class="mb-4">Acompanhamento de Processos de Devolução/Recusa</h2>
    @include('returnProcess.partials.table')
  </div>
</div>
@endsection
