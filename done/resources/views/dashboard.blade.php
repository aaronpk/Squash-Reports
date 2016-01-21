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
      <ul class="group_list">
        @foreach($my_groups as $g)
          <li><a href="/{{ $org->shortname }}/group/{{ $g->shortname }}">#{{ $g->shortname }}</a></li>
        @endforeach
      </ul>
    </div>
    <div class="item">
      <div class="header">More Groups</div>
      <ul class="group_list">
        @foreach($other_groups as $g)
          <li><a href="/{{ $org->shortname }}/group/{{ $g->shortname }}">#{{ $g->shortname }}</a></li>
        @endforeach
      </ul>
    </div>
  </div>
@endsection

@section('main')

<ul class="entry-list">
  @foreach($entries as $entry)
    @include('components/entry')
  @endforeach
</ul>

@endsection
