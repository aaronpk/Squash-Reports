@extends('layouts.master')

@section('sidebar')
  @parent
  <p>This is appended to the master sidebar.</p>
@endsection

@section('content')
  {{ var_dump(Auth::user()) }}
@endsection
