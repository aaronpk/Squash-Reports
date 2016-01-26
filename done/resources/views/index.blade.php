
<!DOCTYPE html>
<html>
<head>
  <!-- Standard Meta -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

  <!-- Site Properities -->
  <title>Telegraph</title>
  <link rel="stylesheet" type="text/css" href="/semantic-ui/semantic.min.css">

  <style type="text/css">

    .hidden.menu {
      display: none;
    }

    .masthead.segment {
      min-height: 700px;
      padding: 1em 0em;
    }
    .masthead .logo.item img {
      margin-right: 1em;
    }
    .masthead .ui.menu .ui.button {
      margin-left: 0.5em;
    }
    .masthead h1.ui.header {
      margin-top: 3em;
      margin-bottom: 0em;
      font-size: 4em;
      font-weight: normal;
    }
    .masthead h2 {
      font-size: 1.7em;
      font-weight: normal;
      margin-bottom: 1em;
    }

    .ui.vertical.stripe {
      padding: 8em 0em;
    }
    .ui.vertical.stripe h3 {
      font-size: 2em;
    }
    .ui.vertical.stripe .button + h3,
    .ui.vertical.stripe p + h3 {
      margin-top: 3em;
    }
    .ui.vertical.stripe .floated.image {
      clear: both;
    }
    .ui.vertical.stripe p {
      font-size: 1.33em;
    }
    .ui.vertical.stripe .horizontal.divider {
      margin: 3em 0em;
    }

    .quote.stripe.segment {
      padding: 0em;
    }
    .quote.stripe.segment .grid .column {
      padding-top: 5em;
      padding-bottom: 5em;
    }

    .footer.segment {
      padding: 5em 0em;
    }

    .secondary.pointing.menu .toc.item {
      display: none;
    }

    @media only screen and (max-width: 700px) {
      .ui.fixed.menu {
        display: none !important;
      }
      .secondary.pointing.menu .item,
      .secondary.pointing.menu .menu {
        display: none;
      }
      .secondary.pointing.menu .toc.item {
        display: block;
      }
      .masthead.segment {
        min-height: 350px;
      }
      .masthead h1.ui.header {
        font-size: 2em;
        margin-top: 1.5em;
      }
      .masthead h2 {
        margin-top: 0.5em;
        font-size: 1.5em;
      }
    }

    .ui.inverted.segment.masthead {
      background-image: url(/assets/basket-of-squash.jpg);
      background-position: center;
      background-size: cover;
    }

    .ui.secondary.inverted.pointing.menu, .ui.secondary.pointing.menu {
      border: 0;
    }

    ul.commands {
      list-style-type: none;
      margin: 0;
      margin-left: 5em;
      padding: 0;
    }
    ul.commands li {
      margin-bottom: 0.5em;
    }

    .faq h4.ui.header {
      font-size: 1.5rem;
      color: #7d9338;
    }

  </style>

  <script src="/assets/jquery-2.2.0.min.js"></script>
  <script src="/semantic-ui/semantic.min.js"></script>
  <script>
  $(document)
    .ready(function() {

      // fix menu when passed
      $('.masthead')
        .visibility({
          once: false,
          onBottomPassed: function() {
            $('.fixed.menu').transition('fade in');
          },
          onBottomPassedReverse: function() {
            $('.fixed.menu').transition('fade out');
          }
        })
      ;

      // create sidebar and attach to menu open
      $('.ui.sidebar')
        .sidebar('attach events', '.toc.item')
      ;

    })
  ;
  </script>
</head>
<body>


<!-- Following Menu -->
<!--
<div class="ui large top fixed hidden menu">
  <div class="ui container">
    <a class="item" href="/">Home</a>
    <a class="item" href="/api">API</a>
    <div class="right menu">
      <div class="item">
        <a class="ui button" href="/auth/slack-login">Log in</a>
      </div>
    </div>
  </div>
</div>
-->
<!-- Sidebar Menu -->
<!--
<div class="ui vertical inverted sidebar menu">
    <a class="item" href="/">Home</a>
    <a class="item" href="/api">API</a>
    <a class="item" href="/auth/slack-login">Log in</a>
</div>
-->

<!-- Page Contents -->
<div class="pusher">
  <div class="ui inverted vertical masthead center aligned segment">

    <!--
    <div class="ui container">
      <div class="ui large secondary inverted pointing menu">
        <a class="toc item">
          <i class="sidebar icon"></i>
        </a>
        <a class="item" href="/">Home</a>
        <a class="item" href="/api">API</a>
        <div class="right item">
          <a class="ui inverted button" href="/auth/slack-login">Log in</a>
        </div>
      </div>
    </div>
    -->

    <div class="ui text container">
      <h1 class="ui inverted header">
        Squash Reports
      </h1>
      <h2>Squash Reports keep your team in touch every day</h2>
      <a href="/auth/slack-login"><img alt="Add to Slack" height="40" width="139" src="https://platform.slack-edge.com/img/add_to_slack.png" srcset="https://platform.slack-edge.com/img/add_to_slack.png 1x, https://platform.slack-edge.com/img/add_to_slack@2x.png 2x"></a>
    </div>
  </div>


  <div class="ui vertical stripe segment">
    <div class="ui middle aligned stackable grid container">
      <div class="row">
        <div class="seven wide column">
          <img src="/assets/screenshots/slack-screenshot.png" class="ui large bordered rounded image">
        </div>
        <div class="eight wide column">
          <h3 class="ui header">Works with Slack!</h3>
          <p>Just say "/done" in Slack, when you have something to report!</p>
          <p>Share things as they happen, instead of waiting to report things until the end of the day.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="ui vertical stripe segment">
    <div class="ui middle aligned stackable grid container">
      <div class="row">
        <div class="eight wide column">
          <h3 class="ui header">Simple entry via Slack commands</h3>
          <p>Choose the right Slack command for your message.</p>
          <p>Easily share what you've <span class="ui horizontal large green label">/done</span>
            or are <span class="ui horizontal large olive label">/doing</span>,
            vent your frustration with <span class="ui horizontal large orange label">/blocking</span>,
            <span class="ui horizontal large teal label">/share</span> a helpful link,
            capture a <span class="ui horizontal large blue label">/quote</span> from a co-worker,
            or <span class="ui horizontal large purple label">/hero</span> someone when they do something nice.</p>
        </div>
        <div class="seven wide column">
          <ul class="commands">
            <li><div class="ui horizontal large green label">/done</div> pushed to production</li>
            <li><div class="ui horizontal large olive label">/doing</div> sending out welcome emails</li>
            <li><div class="ui horizontal large orange label">/blocking</div> the wifi is terrible today</li>
            <li><div class="ui horizontal large teal label">/share</div> https://squashreports.com/</li>
            <li><div class="ui horizontal large blue label">/quote</div> "this is the best!" -aaronpk</li>
            <li><div class="ui horizontal large purple label">/hero</div> SquashBot for always listening</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="ui vertical stripe segment">
    <div class="ui middle aligned stackable grid container">
      <div class="row">
        <div class="seven wide column">
          <img src="/assets/screenshots/group-page.png" class="ui large bordered rounded image">
        </div>
        <div class="eight wide column">
          <h3 class="ui header">See what the whole team is up to</h3>
          <p>Browse your group's history by day to see what everyone has been doing.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="ui vertical stripe segment">
    <div class="ui middle aligned stackable grid container">
      <div class="row">
        <div class="eight wide column">
          <h3 class="ui header">Browse your previous entries</h3>
          <p>Everyone in your organization will be able to see your previous entries across all groups on your profile page.</p>
          <p>You can personalize your page by choosing your own header image!</p>
        </div>
        <div class="seven wide column">
          <img src="/assets/screenshots/user-profile.png" class="ui large bordered rounded image">
        </div>
      </div>
    </div>
  </div>

  <div class="ui vertical stripe segment">
    <div class="ui middle aligned stackable grid container">
      <div class="row">
        <div class="seven wide column">
          <img src="/assets/screenshots/squash-email.png" class="ui large bordered rounded image">
        </div>
        <div class="eight wide column">
          <h3 class="ui header">Get daily reports via email</h3>
          <p>Ignore the website completely and read everything via email! Emails
            are delivered nightly in your local timezone, and include everything
            the team has done throughout the day.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="ui vertical stripe segment">
    <div class="ui middle aligned stackable grid container">
      <div class="row">
        <div class="eight wide column">
          <h3 class="ui header">Follow on the web</h3>
          <p>Follow all your groups from the website, find other groups in your organization, and search for older posts from the dashboard.</p>
        </div>
        <div class="seven wide column">
          <img src="/assets/screenshots/dashboard.png" class="ui large bordered rounded image">
        </div>
      </div>
    </div>
  </div>

  <div class="ui vertical stripe quote segment">
    <div class="ui equal width stackable internally celled grid">
      <div class="center aligned row">
        <div class="column">
          <h3>"Nobody can go to every Scrum (or would want to, for that matter) so the reports are really a winner"</h3>
          <p>Anonymous</p>
        </div>
        <div class="column">
          <h3>"I love it as a way to convey project/customer related information to my team without resorting to a single email or pinging them directly"</h3>
          <p>
            <img src="/assets/ajturner.jpg" class="ui avatar image"> <b>Andrew Turner</b>, CTO Esri R&amp;D DC
          </p>
        </div>
      </div>
    </div>
  </div>


  <div class="ui vertical stripe segment">
    <div class="ui text container"><div style="text-align:center;">
      <h2>Get started now!</h2>
      <a href="/auth/slack-login"><img alt="Add to Slack" height="40" width="139" src="https://platform.slack-edge.com/img/add_to_slack.png" srcset="https://platform.slack-edge.com/img/add_to_slack.png 1x, https://platform.slack-edge.com/img/add_to_slack@2x.png 2x"></a>
    </div></div>
  </div>

  <div class="ui vertical stripe segment">
    <div class="ui text container faq">
      <h3 class="ui header">Frequently Asked Questions</h3>

      <h4 class="ui header">How much does it cost?</h4>
      <p>Squash Reports is free while in beta. In the future, there will be a monthly charge per group (i.e. per Slack channel), tiered by number of features and users per group.</p>

      <h4 class="ui header">Who can read my entries?</h4>
      <p>Only people in your organization can read your entries. Squash Reports staff may access your data if needed for troubleshooting.</p>

      <h4 class="ui header">What information is included in the daily emails?</h4>
      <p>The daily emails include a list of all the entries from the past 24 hours, and will be sent to all subscribers of the group. The list of subscribers will also be included in the email.</p>

      <h4 class="ui header">More questions?</h4>
      <p>Email us at <a href="mailto:&#104;&#101;&#108;&#108;&#111;&#064;&#115;&#113;&#117;&#097;&#115;&#104;&#114;&#101;&#112;&#111;&#114;&#116;&#115;&#046;&#099;&#111;&#109;">&#104;&#101;&#108;&#108;&#111;&#064;&#115;&#113;&#117;&#097;&#115;&#104;&#114;&#101;&#112;&#111;&#114;&#116;&#115;&#046;&#099;&#111;&#109;</a></p>

    </div>
  </div>


  <div class="ui inverted vertical footer segment">
  <div class="ui container">
    <div class="ui stackable inverted divided equal height stackable grid">
      <div class="three wide column">
        <h4 class="ui inverted header">Squash Reports</h4>
        <div class="ui inverted link list">
          <a href="https://github.com/esripdx/Squash-Reports" class="item">Open Source</a>
          <a href="https://github.com/esripdx/Squash-Reports/issues" class="item">Issues</a>
        </div>
      </div>
      <div class="seven wide column">
        <h4 class="ui inverted header">Credits</h4>
        <p>
          <a href="https://thenounproject.com/search/?q=squash&i=56891">Acorn Squash Icon</a> by Irit Barzily from the Noun Project.
          Background photos CC0 from <a href="https://unsplash.com/license">Unsplash</a> photographers.
          Built with the <a href="http://semantic-ui.com/">Semantic UI</a> CSS framework, MIT license.
        </p>
      </div>
    </div>
  </div>
</div>

</div>

</body>

</html>
