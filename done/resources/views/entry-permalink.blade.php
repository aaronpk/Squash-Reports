@extends('layouts.master')

@section('content')
<div id="entry_permalink">

  <div id="profile_text">
    <div id="profile_feed">

      <ul class="entry-list">
        @include('components/entry')
      </ul>

    </div>
  </div>

</div>
@endsection
