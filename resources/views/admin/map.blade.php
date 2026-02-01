@extends('layouts.admin')

@section('header')

    @include('admin.header')

@endsection


@section('content')

{{--    @include('admin.modaldialog_content')--}}

    @include('admin.map_content')

@endsection
