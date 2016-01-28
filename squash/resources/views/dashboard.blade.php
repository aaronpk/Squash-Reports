@extends('layouts.dashboard')

@section('sidebar')
  @parent
  @include('components/dashboard-sidebar')
@endsection

@section('main')

@if(count($entries))
<ul class="entry-list">
  @foreach($entries as $entry)
    @include('components/entry')
  @endforeach
</ul>
@else

<div class="ui positive message">
  <div class="header">
    Thanks for signing up for Squash Reports!
  </div>
  <p>Go back to your Slack channel and post your first entry! (Copy the one below if you need a suggestion.)</p>
  <p><blockquote>/done set up Squash Reports</blockquote></p>
  <p>You'll get an email later tonight with a summary of everything your team did today. You can always get back here by typing <b>/squash login</b> from your Slack channel.</p>
</div>

@endif

@endsection
