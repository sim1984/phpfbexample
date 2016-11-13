@extends('example')

@section('title','Товары')

@section('body')

    <h1>Товары</h1>
    <p>
        {!! $filter !!}
        {!! $grid !!}
    </p>
@stop

