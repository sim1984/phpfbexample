@extends('example')

@section('title','Заказчики')

@section('body')

    <h1>Заказчики</h1>
    <p>
        {!! $filter !!}
        {!! $grid !!}
    </p>
@stop

