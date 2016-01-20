@extends('layouts.dashboard')

@section('sidebar')
  @parent
  <div class="ui secondary vertical menu">
    <div class="item">
      <div class="ui icon input">
        <input type="text" placeholder="Search entries...">
        <i class="search icon"></i>
      </div>
    </div>
    <div class="item">
      <div class="header">My Groups</div>
      <div class="menu">
        @foreach($my_groups as $g)
          <li><a href="/{{ $org->shortname }}/group/{{ $g->shortname }}">#{{ $g->shortname }}</a></li>
        @endforeach
      </div>
    </div>
    <div class="item">
      <div class="header">More Groups</div>
      <div class="menu">
        @foreach($other_groups as $g)
          <li><a href="/{{ $org->shortname }}/group/{{ $g->shortname }}">#{{ $g->shortname }}</a></li>
        @endforeach
      </div>
    </div>
  </div>
@endsection

@section('main')
  <div class="card">
    <h2>Hello World</h2>
    <p>Welcome to ZOMBOCOM</p>
  </div>
@endsection
