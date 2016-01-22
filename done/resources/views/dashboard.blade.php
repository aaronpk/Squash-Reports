@extends('layouts.dashboard')

@section('sidebar')
  @parent
  @include('components/dashboard-sidebar')
@endsection

@section('main')

<ul class="entry-list">
  @foreach($entries as $entry)
    @include('components/entry')
  @endforeach
</ul>

@endsection
