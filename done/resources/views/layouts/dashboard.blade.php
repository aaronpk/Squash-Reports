@extends('layouts.master')

@section('content')
<div id="page">
  <div id="page_contents">
    <nav>
      @section('sidebar')
      @show
    </nav>
    <div id="content">
      @yield('main')
    </div>
  </div>
</div>
@endsection
