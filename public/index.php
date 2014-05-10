<?php
require_once('../config.php');
require_once('../recaptchalib.php');
require_once('../functions.php');

// Stores error messages to display to the user
$errors = array();
$success = false;

/*
 * Check cache
 */
// PHP requires setting a timezone when using date()
date_default_timezone_set('Europe/Berlin');
$cache_file = date('YmdH') . '.cache';

/* delete old cache files */
if (!$config['keep_caches']) {
  if (!af_remove_old_caches($cache_file)) {
    $errors[] = 'Ein Fehler ist aufgetreten (Alte Cache-Datei konnte nicht gelöscht werden).';
    $config['enabled'] = false;
    break;
  }
}

/* check whether the rate limit has been exceeded */
$cache_path = '../cache/' . $cache_file;
if (!touch($cache_path)) {
  $errors[] = 'Ein Fehler ist aufgetreten (Cache-Datei ist nicht schreibbar).';
  $config['enabled'] = false;
}
$cache_file_object = new SplFileInfo($cache_path);
if ($cache_file_object->getSize() > $config['ratelimit']) {
  $errors[] = 'Im Moment kannst du kein Feedback verschicken. Bitte probiere es in ca. einer Stunde nochmal.';
  $config['enabled'] = false;
}

// user has submitted text
if ($config['enabled'] && $_SERVER['REQUEST_METHOD'] == 'POST') {
  /*
   * preparing user submitted content
  */
  $message = af_sanitize_message($_POST['message']);

  /*
   * Validations
   */
  $failed = false;

  /* captcha validation */
  $resp = recaptcha_check_answer ($recaptcha_private_key,
                                  $_SERVER["REMOTE_ADDR"],
                                  $_POST["recaptcha_challenge_field"],
                                  $_POST["recaptcha_response_field"]);
  if (!$resp->is_valid) {
    $failed = true;
    $errors[] = "Du hast das Captcha (die zwei Wörter) nicht korrekt eingegeben. Bitte versuche es nochmal.";
  }
  unset($resp);

  /* validations on message */
  /* minimum length */
  if (!$failed && strlen($message) < 2) {
    $failed = true;
    $errors[] = "Bitte gib eine Nachricht ein.";
  }
  /* maximum length */
  if (!$failed && strlen($message) > 5000) {
    $failed = true;
    $errors[] = "Bitte gib nicht mehr als 5.000 Zeichen als Nachricht ein.";
  }
  /* recipient */
  if (!$failed) {
    $recipient = af_parse_recipient($_POST['recipient'],
                                    $primary_recipients,
                                    $secondary_recipients);

    if (!$recipient) {
      $failed = true;
      $errors[] = 'Bitte wähle aus, an wen deine Nachricht gesendet werden soll.';
    }
  }

  /*
  * Send message
  * $recipient[1] contains the recipient mail address
  */
  if (!$failed) {
    $headers = "Content-type: text/plain; charset=utf-8\r\n";
    $headers .= 'From: ' . $config['mail_from'] . "\r\n";

    $message = $config['mail_prefix'] . "\n" . $message;

    $message = af_format_message($message);

    // increment rate counter
    if (!file_put_contents($cache_path, 'm', FILE_APPEND)) {
      $failed = true;
      $errors[] = 'Ein Fehler ist aufgetreten (Cache-Datei konnte nicht geschrieben werden 2).';
      $config['enabled'] = false;
    }

    // check rate limit again
    if (!$failed && $cache_file_object->getSize() > ($config['ratelimit']+1)) {
      $errors[] = 'Im Moment kannst du kein Feedback verschicken. Bitte probiere es in ca. einer Stunde nochmal. (2)';
      $config['enabled'] = false;
    }
    if (!$failed) {
      $success = true;
      // actually send the mail
      if (!is_array($recipient[1])) {
        $recipient[1] = array($recipient[1]);
      }
      foreach ($recipient[1] as $recp) {
        if ($config['mail_pretend']) {
          echo "mail($recp, ".$config['mail_subject'].", $message, $headers);";
        } else {
          mail($recp, $config['mail_subject'], $message, $headers);
        }
      }
    }
  }
}
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
<?php foreach($errors as $error):?>
  <div class="error"><?php echo $error?></div>
<?php endforeach; ?>
<?php if (!$config['enabled']): ?>
  <p class="error">Sorry, dieser Dienst steht gerade nicht zur Verfügung.</p>
<?php elseif ($success): ?>
  <p class="success">
    Deine Nachricht wurde erfolgreich verschickt.
    Danke für dein Feedback.
  </p>
  <p>
    <a href="/">Zurück zum Formular</a>
  </p>
<?php else: ?>
  <form action="/" method="post">
    <p>
      <label for="recipient">Nachricht an</label>
      <select name="recipient" id="recipient" size="1">
<?php foreach($primary_recipients as $key => $recp): ?>
<?php $selected = ($_POST['recipient'] == 'p' . $key) ?>
        <option value="p<?php echo $key; ?>"<?php if ($selected) { echo ' selected'; } ?>><?php echo $recp[0] ?></option>
<?php endforeach; ?>
        <option disabled>_________</option>
<?php foreach($secondary_recipients as $key => $recp): ?>
<?php $selected = ($_POST['recipient'] == 's' . $key) ?>
        <option value="s<?php echo $key; ?>"<?php if ($selected) { echo ' selected'; } ?>><?php echo $recp[0] ?></option>
<?php endforeach; ?>
      </select><br />
    </p>
    <label for="message">Deine Nachricht</label>
    <textarea name="message" id="message" rows="8" cols="40"><?php
if (isset($_POST['message'])) {
  echo htmlspecialchars($_POST['message']);
}
?></textarea>
    <div id="char-counter">
    5000 Zeichen verfügbar.
    </div>
    <div style="clear: both;">
    <script type="text/javascript">
    var area = document.getElementById("message");
    var message = document.getElementById("char-counter");
    var maxLength = 5000;
    var checkLength = function() {
        if(area.value.length <= maxLength) {
            message.innerHTML = "Noch " + (maxLength-area.value.length) + " Zeichen.";
        } else {
          message.innerHTML = Math.abs(maxLength-area.value.length) + " Zeichen zu viel.";
        }
    }
    setInterval(checkLength, 100);
    </script>
    
    <div>Bitte tippe die beiden Wörter ein:</div>
    <!--ReCaptcha Start-->
    <div class="captcha-container">
    <?php echo recaptcha_get_html($recaptcha_public_key); ?>
    </div>
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