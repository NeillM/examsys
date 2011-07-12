<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* User related functions
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require_once '../classes/userutils.class.php';
require_once '../classes/passwordutils.class.php';

$header = array();
$needed_cols = array('student_id','full name','first names','surname','title','course code','year of course','local email');

function add_users_from_file($fileName) {
  global $header, $needed_cols, $mysqli;
  
  $degrees = array();
  $users[] = array();
  
  $lines = file($fileName);
  foreach ($lines as $separate_line) {
    if (strpos($separate_line,'","') !== false) {
      $separate_line = '","' . $separate_line . ',"';
      $split_on = '","';
    } else {
      $split_on = ',';	
    }
    $separate_line = str_replace("\n","",$separate_line);
    $separate_line = str_replace("\r","",$separate_line);

    if (count($header) == 0) {
      //build the header array
      $separate_line = strtolower($separate_line);
      $error = array();
      $tmp_header = explode($split_on,$separate_line);
      foreach ($tmp_header as $tmp_string) {
        $tmp_string = str_replace('"','',$tmp_string);
        $header[] = $tmp_string;
      }
      $header = array_flip($header);
      foreach ($needed_cols as $col) {
        $col = str_replace('"','',$col);
        if (!isset($header[$col])) {
          $error['error'][] = "<li>Missing '$col' Colum from import please add it.</li>";
        }
      }
      if (count($error) > 0) {
        return $error;
      } else {
        continue;
      }
    }

    $fields = explode($split_on,$separate_line);
                     
    $sid = str_replace('"','',trim($fields[$header['student_id']]));
    $forname = UserUtils::my_ucwords(trim($fields[$header['first names']])); 
    $initial = explode(' ',$forname);
    $initials = '';
    foreach ($initial as $name) {
      $initials .= substr($name,0,1);
    }
    $initials = strtoupper($initials);
    $surname = UserUtils::my_ucwords(trim($fields[$header['surname']]));
    $title = UserUtils::my_ucwords(trim($fields[$header['title']]));
    $degree = trim($fields[$header['course code']]); 
    $year = trim($fields[$header['year of course']]);
    $email = trim($fields[$header['local email']]);
    if (isset($header['username'])) {
      $username = $fields[$header['username']];
    } else {
      $username = explode("@",$email);
      $username = $username[0];
    }
                            
    if ($degree == "A101") { //Do extra work for GEM
      if (date("m") >= 2 and date("m") <= 6 and $year == 2) {
        $year++;
      } elseif ($year > 2) {
        $year++;
      }
    }
                        
    $school = trim($fields[11]);
    			
    if ($title == 'Mr') {
      $gender = 'Male'; 
    } elseif ($title == 'Miss' or $title == 'Mrs' or $title == 'Ms') {
      $gender = 'Female';
    } else {
      $gender = 'NULL';
    }
    
    $invalid_user = false;
    if (UserUtils::usernameExists($username, $mysqli) === false) {
      //add new users
       
      $password = PasswordUtils::gen_password();
      $encpw_password = PasswordUtils::encpw($username, $password);

      $added = UserUtils::createUser($username, $encpw_password, $title, $forname, $surname, $email, $degree, $gender, $year, 'Student', $sid, $mysqli);

      if ($added == true) {
        $users['added'][] = $username;
      }
      $users['degrees'][$degree] = $degree;
      
      //send Welcome Email if ticked
      if (isset($_POST['welcome']) and $_POST['welcome'] != '') {
        send_welcome_email($title, $surname, $username, $password, $email);
      }
    } elseif ($username != '' and stristr('ps_',$username) === false) {
      //update existing student records
      $result = $mysqli->prepare("UPDATE users SET yearofstudy=?, title=?, first_names=?, surname=?, grade=? WHERE username=?");
      $result->bind_param('ssssss', $year, $title, $forname, $surname, $degree, $username);
      $result->execute();
      $result->close();

      $users['updated'][] = $username;
      $users['degrees'][$degree] = $degree;
    } else {
      $invalid_user = true;
    }
	  
    if ($invalid_user != true AND isset($header['modules']) AND isset($header['session'])) {
      //add students to modules
      $module = trim($fields[$header['modules']]);
      $session = trim($fields[$header['session']]);
      echo "Insert " . trim($fields[$header['modules']]) . " $tmp_userID - " . trim($fields[$header['session']]) . "<br />\n";
      $result = $mysqli->prepare("INSERT INTO student_modules VALUES(NULL, ?, ?, ?, 1)");
      $result->bind_param('iss', $tmp_userID, $module, $session);
      $result->execute();
      $result->close();
    }
  }

  return $users;
}

function send_welcome_email($title, $surname, $username, $password, $user_email) {
  $subject = "New TouchStone account";
  $headers = "From: $support_email\n";
  $headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";
  $message = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">
<html>
<head>
<title>Create new user account</title>
<style>
body, td, p, div {font-family:Arial,sans-serif; background-color:white; color:#003366; font-size:10pt}
</style>
</head>
<body>
<p>Dear $title $surname,</p>
<p>A new account has been created to access the online assessment and survey system TouchStone. Your personal authentication details are the same as your university log in details.</p>
<p><strong>Note:</strong></p>
<ul>
<li>Never share your university username/password with anyone.</li>
<li>Cheating in summative examinations is an academic offence and will not be tolerated.</li>
</ul>\n
</body>\n</html>";

 mail($user_email, $subject, $message, $headers) or print "<p>Could not send mail to <strong>$user_email</strong>.</p>";
}
?>