<html>
  <head>
    <title>Done Reports</title>
    <link rel="stylesheet" type="text/css" href="/semantic-ui/semantic.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/styles.css">
    <script src="/assets/jquery-2.2.0.min.js"></script>
    <script src="/assets/script.js"></script>
  </head>
  <body>

    <header class="ui fixed inverted main menu">
      <div class="ui container">
        <a href="javascript:open_nav();" class="icon item collapse-icon"><i class="content icon"></i></a>
        <a href="/dashboard" class="item">
          <img class="logo" src="/assets/compass.svg">
          <span style="padding-left: 0.7em;">Done!</span>
        </a>
        <a href="/{{ $org->shortname }}/{{ Auth::user()->username }}" class="item">Your Profile</a>
        <div class="ui simple dropdown item right">
          Settings <i class="dropdown icon"></i>
          <div class="menu">
            <a class="item" href="/profile">Edit Profile</a>
            <a class="item" href="/auth/logout">Sign Out</a>
          </div>
        </div>
      </div>
    </header>

    @yield('content')

    <script src="/semantic-ui/semantic.min.js"></script>
  </body>
</html>
