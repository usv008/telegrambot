@extends('layouts.admin')

@section('header')

    @include('admin.header')

@endsection


@section('content')

{{--    @include('admin.modaldialog_content')--}}
    @include('admin.modaldialog')

    @include('admin.archi_content')

@endsection
