@extends('layouts.master')

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
        <a class="item">#geotriggers</a>
        <a class="item">#developers</a>
      </div>
    </div>
    <div class="item">
      <div class="header">More Groups</div>
      <div class="menu">
        <a class="item">#opendata</a>
        <a class="item">#blah</a>
      </div>
    </div>
  </div>
@endsection

@section('content')
  <div class="card">
    <h2>Hello World</h2>
    <p>Welcome to ZOMBOCOM</p>
  </div>
@endsection
