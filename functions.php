<?php
/**
 * removes all files in the cache directory except the argument.
 *
 * @param string $cache_file the base name of the current cache file
 * @return boolean true on success, false on failure
 **/
function af_remove_old_caches($cache_file) {
  $dirs = new DirectoryIterator('../cache');
  foreach ($dirs as $file) {
    if ($file->isDir()) {
      continue;
    }
    if ($file->getFilename() != $cache_file &&
        $file->getFilename()[0] != '.') {
      if (!unlink($file->getRealPath())) {
        return false;
      }
    }
  }
  return true;
}

/**
 * filters the user-POSTed message
 *
 * @param string $msg
 * @return string the filtered message
 **/
function af_filter_message($msg) {
  return trim($msg);
}

/**
 * from the value generated from the HTML select tag, parse
 * the recipient.
 *
 * @param string $val the input value, usually $_POST['recipient']
 * @param string $primary_recipients
 * @param string $secondary_recipients
 * @return mixed as defined in the config file, false on failure
 **/
function af_parse_recipient($val, $primary_recipients, $secondary_recipients) {
  $recipient = false;
  $first_char = $val[0];
  $recp_id = substr($val, 1);
  // false -> secondary recp.
  $recp_is_primary = ($first_char == 'p');

  if ($recp_is_primary && array_key_exists($recp_id, $primary_recipients)) {
    $recipient = $primary_recipients[$recp_id];
  } elseif (!$recp_is_primary &&
            array_key_exists($recp_id, $secondary_recipients)) {
    $recipient = $secondary_recipients[$recp_id];
  }
  
  return $recipient;
}

/**
 * format a message for sending in a mail
 *
 * @param string $msg
 * @return string
 **/
function af_format_message($msg) {
  $msg = wordwrap($msg, 75, "\n", true);
  $msg = str_replace("\n", "\r\n", $msg);
  return $msg;
}