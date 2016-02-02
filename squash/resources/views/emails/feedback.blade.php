<html>
<body style="margin: 0; padding: 0;">

  <div style="background-color: #6d812f; padding: 30px 10px; text-align: center; font-size: 22pt; color: #d5e79f;">
    Squash Reports Feedback
  </div>

  <div style="padding: 20px 10px; background-color: #ffffff;">
    <div style="margin-bottom: 1em;">{{ $username }} at {{ $org }} says:</div>
    <div style="font-size: 14pt;">{{ $text }}</div>
  </div>

  <div style="font-size: 9pt; text-align: center; padding-top: 10px; border-top: 3px #6d812f solid;">
    This email was sent to {{ $to }} on behalf of {{ $from }}
  </div>
</div>

</body>
</html>
