@extends('layouts.admin')

@section('header')

    @include('admin.header')

@endsection


@section('content')

    {{--    @include('admin.modaldialog_content')--}}

    @include('admin.modaldialog')

    @include('admin.sidemenu_bot_content')

    @include('admin.user_content')

@endsection
