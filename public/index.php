<?php
require('../config.php');

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title>Anonymes Feedback | Bakespace</title>
  <link rel="stylesheet" href="master.css" type="text/css" media="all" charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<div id="wrapper">
  <h1>Anonymes Feedback</h1>
<?php if (!$config['enabled']): ?>
  <p class="error">Sorry, dieser Dienst steht gerade nicht zur Verfügung.</p>
<?php else: ?>
  <p class="explanation">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
  <form action="form.html" method="post">
    <label for="recipient">Empfänger</label>
    <select name="recipient" id="recipient" size="1">
<?php foreach($primary_recipients as $key => $recp): ?>
      <option value="p<?php echo $key ?>"><?php echo $recp[0] ?></option>
<?php endforeach; ?>
      <option disabled>_________</option>
<?php foreach($secondary_recipients as $key => $recp): ?>
      <option value="s<?php echo $key ?>"><?php echo $recp[0] ?></option>
<?php endforeach; ?>
    </select>
    <textarea name="message" rows="8" cols="40">
Liebe Backenden,

ich mag euch.

Aber eigentlich auch nicht.

Herzlichst,
Ihr Wagner
    </textarea>
    
    <!--ReCaptcha Start-->
    <script type="text/javascript"
       src="http://www.google.com/recaptcha/api/challenge?k=6LdWNuwSAAAAALKxfOr6qT1K9rFzEu3PW69aVhL">
    </script>
    <noscript>
       <iframe src="http://www.google.com/recaptcha/api/noscript?k=6LdWNuwSAAAAALKxfOr6qT1K9rFzEu3PW69aVhL"
           height="300" width="500" frameborder="0"></iframe><br>
       <textarea name="recaptcha_challenge_field" rows="3" cols="40">
       </textarea>
       <input type="hidden" name="recaptcha_response_field"
           value="manual_challenge">
    </noscript>
    <!--ReCaptcha End-->
    
    <div class="note">
      Um uns vor Spam zu schützen, speichern wir die IP-Adresse deines Computers.
    </div>
  <input type="submit" name="submit" value="Absenden!" id="submit">
  </form>
<?php endif; ?>
</div>
</body>
</html>