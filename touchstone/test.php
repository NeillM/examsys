<?php
/**
 * This is function encpw encrpts a password using md5 and a different salt per user. For 
 * storage in the DB
 *
 * @param string $u username
 * @param string $p password
 * @return string encrypted password
 *
 */
function encpw($u,$p) {
  $salt = '$1$' . substr(md5($u),0,12) . '$';
  return crypt($p,$salt);
}

$u='dcdavies';
$p='temp';

echo encpw($u,$p);
?>