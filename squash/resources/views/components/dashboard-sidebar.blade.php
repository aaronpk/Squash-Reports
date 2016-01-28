<div class="ui secondary vertical menu">
  <!--
  <div class="item">
    <form action="/dashboard" method="get" class="ui form">
      <div class="ui icon input">
        <input type="text" placeholder="Search entries..." name="search" value="{{ $search }}">
        <i class="search icon"></i>
      </div>
    </form>
  </div>
  -->
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
