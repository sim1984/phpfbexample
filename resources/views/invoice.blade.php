@extends('example')

@section('title','Счет фактуры')

@section('body')

    <h1>Счет фактуры</h1>
    <p>
        {!! $filter !!}
        {!! $grid !!}
    </p>
@stop

