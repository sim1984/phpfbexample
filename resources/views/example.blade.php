@extends('master')

@section('title','Пример работы с Firebird')


@section('body')


<h1>Пример</h1>

@if(Session::has('message'))
    <div class="alert alert-success">
        {!! Session::get('message') !!}
    </div>
@endif

<p>Пример работы с Firebird.<br/>

</p>


@stop


@section('content')

    @include('menu')

    @yield('body')


@stop

