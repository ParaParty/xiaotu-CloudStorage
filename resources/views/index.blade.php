@extends('layout.layout')

@section('title','导航')

@section('head')

    <script src="{{ asset('assets/js/api.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

@endsection

@section('main')
    <script type='module'>
        var workingDirectory='/';
        api('{{ route('api.storage.get-path') }}','/',(data)=>{
            console.log(data);

        });

    </script>
@endsection
