<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 14/12/12
 * Time: 14:33
 * To change this template use File | Settings | File Templates.
 */
/**
 *
 * Handles Guest account access in rogo
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';


class guestlogin_auth extends outline_authentication {

  function register_callback_routines() {
    $this->register_callback(array($this, 'loginbutton'), 'displaystdform', $this->number, $this->name);
    $this->register_callback(array($this, 'errordisp'), 'displayerrform', $this->number, $this->name);
  }

  function errordisp(&$displayerrformobj) {
    global $string;
    if ($_SERVER['PHP_SELF'] == '/index.php') {
      $this->savetodebug('adding temp account notice to error screen');
      $message2 = $string['ifstuckinvigilator'] . " <a href=\"guest_account.php\" style=\"color:blue\"><strong>" . $string['tempaccount'] . "</strong></a>";
      $displayerrformobj->li[] = $message2;
    }
  }

  function loginbutton(&$displaystdformobj) {
    $this->savetodebug('Button Check');
    //$displaybutton = false;
    // detect if we should display login button
    $paper_match = FALSE;
    $ip_match = FALSE;
    $query = "SELECT labs FROM properties WHERE start_date < DATE_ADD(NOW(), interval 15 minute) AND end_date > NOW() AND paper_type IN ('1','2') AND labs != ''";
    $results = $this->db->prepare($query);
    if ($this->db->error) {
      try {
        $e = $this->db->error;
        $en = $this->db->errno;
        throw new Exception("MySQL error $e <br> Query:<br> $query", $en);
      } catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br >";
        echo nl2br($e->getTraceAsString());
      }
    }
    $results->execute();
    $results->store_result();
    $results->bind_result($labs);
    while ($results->fetch()) {
      $paper_match = TRUE;
      $query = "SELECT address FROM ip_addresses WHERE lab IN ($labs)";
      $sub_results = $this->db->prepare($query);
      if ($this->db->error) {
        try {
          $e = $this->db->error;
          $en = $this->db->errno;
          throw new Exception("MySQL error $e <br> Query:<br> $query", $en);
        } catch (Exception $e) {
          echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br >";
          echo nl2br($e->getTraceAsString());
        }
      }
      $sub_results->execute();
      $sub_results->store_result();
      $sub_results->bind_result($address);
      $labs_list = '';
      while ($sub_results->fetch()) {
        $labs_list = $labs_list . ' ' . $address;
        if (NetworkUtils::get_ipaddress() == $address) $ip_match = TRUE;
      }
      $sub_results->close();
    }
    $results->close();

    $this->savetodebug('Status paper_match:' . var_export($paper_match, TRUE) . ' ip_match:' . var_export($ip_match, TRUE) . ' ip address:' . var_export(NetworkUtils::get_ipaddress(), TRUE) . ' <br> ' . $labs . ' ' . $labs_list);
    if ($paper_match === TRUE and $ip_match === TRUE) { //($displaybutton === TRUE) {
      $this->savetodebug('Adding New Button');
      $newbutton = new displaystdformobjbutton();
      $newbutton->type = 'submit';
      $newbutton->value = ' Guest Login ';
      $newbutton->name = 'guestlogin';
      $displaystdformobj->buttons[] = $newbutton;
    }

  }

}
