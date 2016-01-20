@extends('layouts.dashboard')

@section('sidebar')
  @parent
  @include('components/group_sidebar')
@endsection

@section('main')
  <div class="card">
    <h2>Hello World</h2>
    <p>Welcome to ZOMBOCOM</p>
  </div>
@endsection
