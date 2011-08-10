<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  if (!defined('STDIN')) {
//    exit;
  }
  //require '../config/config.inc';
  set_time_limit(0);
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
  echo "\nStarting update from version 4.0 to 4.1\n";
  ob_start();
  
  // 15/06/2011
  // Add index to improve performance for standards setting index page
  $result = $mysqli->prepare("SHOW INDEX FROM ebel");
  $result->execute();
  $result->store_result();
  $result->fetch();
  if ($result->num_rows() == 1) {
    $adjust = $mysqli->prepare("ALTER TABLE ebel ADD INDEX SETTER_AND_DATE (setterID, date_set)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE ebel ADD INDEX SETTER_AND_DATE (setterID, date_set)</div>\n";
    ob_flush();
    flush();
  }
  
  // 16/06/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='log_late' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='year'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 1) {
    $adjust = $mysqli->prepare("ALTER TABLE log_late DROP COLUMN year");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log_late DROP COLUMN year</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE log_late DROP COLUMN student_grade");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log_late DROP COLUMN student_grade</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE log_late DROP COLUMN ipaddress");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE log_late DROP COLUMN ipaddress</div>\n";
  }

  // 16/06/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sys_errors' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='fixed'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN fixed datetime");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN fixed datetime</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN php_self text");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN php_self text</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN query_string text");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN query_string text</div>\n";
    
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN request_method enum('GET', 'HEAD', 'POST', 'PUT', 'DELETE')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN request_method enum('GET', 'HEAD', 'POST', 'PUT', 'DELETE')</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  
  // Get a list of reviews where group = 'Yes'
  $group_reviews = $mysqli->prepare("SELECT DISTINCT paperID FROM standards_setting WHERE group_review = 'Yes' AND paperID > 0");
  $group_reviews->execute();
  $group_reviews->store_result();
  $group_reviews->bind_result($paperID);
  while($group_reviews->fetch()) {
    $group_list = '';
    // Get a list of other ANGOFF reviews for the paper
    $individual_reviews = $mysqli->prepare("SELECT DISTINCT setterID, std_set FROM standards_setting WHERE paperID = ? AND method = 'Modified Angoff' AND group_review = 'No'");
    $individual_reviews->bind_param('i', $paperID);
    $individual_reviews->execute();
    $individual_reviews->store_result();
    $individual_reviews->bind_result($setterID, $std_set);
    while($individual_reviews->fetch()) {
      // Add to list of user IDs/dates <user_id>,<date>;<user_id>,<date>
      $group_list .= $setterID . ',' . str_replace(array(' ', '-', ':'), '', $std_set) . ';';
    }
    $individual_reviews->close();  
    $group_list = rtrim($group_list, ';');
    
    // Update the group review setting group field to name/date string
    if($group_list != ''){
      $update = $mysqli->prepare("UPDATE standards_setting SET group_review = ? WHERE paperID = ? AND method = 'Modified Angoff' AND group_review = 'Yes'");
      $update->bind_param('si', $group_list, $paperID);
      $update->execute();
      $update->close();
      echo "<div>UPDATE standards_setting SET group_review = '$group_list' WHERE paperID = $paperID AND method = 'Modified Angoff' AND group_review = 'Yes'</div>\n";
    }
  }
  $group_reviews->close();  
  
  // 29/06/2011
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='selfenroll'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN selfenroll tinyint");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules ADD COLUMN selfenroll tinyint</div>\n";

    $adjust = $mysqli->prepare("UPDATE modules SET selfenroll=0");
    $adjust->execute();
    $adjust->close();
    echo "<div>UPDATE modules SET selfenroll=0</div>\n";
  }
  
  // 30/06/2011 - Change schools from text to integers
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='schoolid'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    // Add new integer column
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN schoolid int");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules ADD COLUMN schoolid int</div>\n";

    // Look up existing school names
    $schools = array();
    $sch_data = $mysqli->prepare("SELECT id, school FROM schools");
    $sch_data->execute();
    $sch_data->store_result();
    $sch_data->bind_result($schoolid, $school_name);
    while ($sch_data->fetch()) {
      $schools[$school_name] = $schoolid; 
    }
    $sch_data->close();
    
    // Populate the new field
    foreach($schools as $school_name=>$schoolid) {
      $adjust = $mysqli->prepare("UPDATE modules SET schoolid=? WHERE school=?");
      $adjust->bind_param('is', $schoolid, $school_name);
      $adjust->execute();
      $adjust->close();
    }
    // Drop the old textual column
    $adjust = $mysqli->prepare("ALTER TABLE modules DROP COLUMN school");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules DROP COLUMN school</div>\n";
  }
  

  // 04/07/2011 - Drop 'Faculty' column from users.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='faculty'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 1) {
    $adjust = $mysqli->prepare("ALTER TABLE users DROP COLUMN faculty");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE users DROP COLUMN faculty</div>\n";
  }
  
  // 04/07/2011 - Create new 'admin_access' table to hold which modules 'Admin' can access.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='admin_access' AND TABLE_SCHEMA='$cfg_db_database' AND COLUMN_NAME='adminID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE admin_access (adminID int not null primary key auto_increment, userID int, schools_id int)");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE admin_access (adminID int not null primary key auto_increment, userID int, schools_id int)</div>\n";
  }
  
  // 04/07/2011 - New table to handle forgotten password requests.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='password_tokens' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE password_tokens (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, token CHAR(16) NOT NULL, time DATETIME NOT NULL);");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE password_tokens (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT NOT NULL, token CHAR(16) NOT NULL, time DATETIME NOT NULL);</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 06/07/2011 - New table users_metadata.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='users_metadata' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE users_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, userID INT, moduleID int, type varchar(255), value varchar(255), calendar_year enum('2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20'));");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE users_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, userID INT, moduleID int, type varchar(255), value varchar(255), calendar_year enum('2010/11','2011/12','2012/13','2013/14','2014/15','2015/16','2016/17','2017/18','2018/19','2019/20'));</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 11/07/2011 - Add new column for retiring papers.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='properties' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='retired'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE properties ADD COLUMN retired datetime");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE properties ADD COLUMN retired datetime</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 25/07/2011 - New table paper_metadata_security.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='paper_metadata_security' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE paper_metadata_security (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, paperID int, name varchar(255), value varchar(255))");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE paper_metadata_security (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, paperID int, name varchar(255), value varchar(255))</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 27/07/2011 - New table questions_metadata.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions_metadata' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='id'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("CREATE TABLE questions_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, questionID int, type varchar(255), value varchar(255))");
    $adjust->execute();
    $adjust->close();
    echo "<div>CREATE TABLE questions_metadata (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, questionID int, type varchar(255), value varchar(255))</div>\n";
    ob_flush();
    flush();
  }
  $result->close();
  
  // 01/08/2011 - Add new column for paperID in the errors table.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sys_errors' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='paperID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN paperID int");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN paperID int</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 01/08/2011 - Add new column for paperID in the errors table.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='sys_errors' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='post_data'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE sys_errors ADD COLUMN post_data text");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE sys_errors ADD COLUMN post_data text</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 01/08/2011 - Change to database structure for more flexible marking
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='questions' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='display_method'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE questions CHANGE COLUMN score_method display_method text");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE questions CHANGE COLUMN score_method display_method text</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE questions ADD COLUMN score_method enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark')");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE questions ADD COLUMN score_method enum('Mark per Question','Mark per Option','Allow partial Marks','Bonus Mark')</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("UPDATE questions SET score_method = 'Mark per Option' WHERE q_type != 'Calculation'");
    $adjust->execute();
    $adjust->close();
    
    // Update the BonusMark setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='BonusMark'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Bonus Mark' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    // Update the StrictOrder setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='StrictOrder'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Mark per Option' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    // Update the AllItemsCorrect setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='AllItemsCorrect'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Mark per Question' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    
    // Update the SelectedPositive setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='SelectedPositive'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Mark per Option' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    // Update the OrderNeighbours setting
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='OrderNeighbours'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE questions SET display_method='', score_method='Allow partial Marks' WHERE q_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    $adjust = $mysqli->prepare("ALTER TABLE options CHANGE COLUMN marks marks_correct float");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE options CHANGE COLUMN marks marks_correct float</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE options ADD COLUMN marks_incorrect float");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE options ADD COLUMN marks_incorrect float</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("ALTER TABLE options ADD COLUMN marks_partial float");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE options ADD COLUMN marks_partial float</div>\n";
    ob_flush();
    flush();

    $adjust = $mysqli->prepare("UPDATE options SET marks_incorrect=0");
    $adjust->execute();
    $adjust->close();

    $adjust = $mysqli->prepare("UPDATE options SET marks_partial=0");
    $adjust->execute();
    $adjust->close();
    
    // Update options for negative marking
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='TF_NegativeAbstain' OR display_method='YN_NegativeAbstain'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE options SET marks_incorrect=-1 WHERE o_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();

    // Update options for half-negative marking
    $q_data = $mysqli->prepare("SELECT q_id FROM questions WHERE display_method='TF_NegativeAbstainHalf'");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($q_id);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE options SET marks_incorrect=-0.5 WHERE o_id=$q_id");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
  }
  $result->close();

  // 01/08/2011 - Change to database structure for more flexible marking
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='schools' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='facultyID'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE schools ADD COLUMN facultyID int");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE schools ADD COLUMN facultyID int</div>\n";
    ob_flush();
    flush();
    
    // Populate the new field with Faculty IDs
    $q_data = $mysqli->prepare("SELECT id, name FROM faculty");
    $q_data->execute();
    $q_data->store_result();
    $q_data->bind_result($faculty_id, $faculty_name);
    while ($q_data->fetch()) {
      $adjust = $mysqli->prepare("UPDATE schools SET facultyID=$faculty_id WHERE faculty='$faculty_name'");
      $adjust->execute();
      $adjust->close();
    }
    $q_data->close();
    
    $adjust = $mysqli->prepare("ALTER TABLE schools DROP COLUMN faculty");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE schools DROP COLUMN faculty</div>\n";
    ob_flush();
    flush();
  }
  $result->close();

  // 10/08/2011 - Add new column for negative marking setting for modules.
  $result = $mysqli->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='modules' AND TABLE_SCHEMA='touchstone' AND COLUMN_NAME='neg_marking'");
  $result->execute();
  $result->store_result();
  $result->bind_result($column_type);
  $result->fetch();
  if ($result->num_rows() == 0) {
    $adjust = $mysqli->prepare("ALTER TABLE modules ADD COLUMN neg_marking TINYINT(1)");
    $adjust->execute();
    $adjust->close();
    echo "<div>ALTER TABLE modules ADD COLUMN neg_marking TINYINT(1)</div>\n";
    ob_flush();
    flush();
    
    $adjust = $mysqli->prepare("UPDATE modules SET neg_marking=1");
    $adjust->execute();
    $adjust->close();
  }
  $result->close();

  //Close the database
  $mysqli->close();
  ob_end_flush();
  echo "\nFinished!\n";
?>