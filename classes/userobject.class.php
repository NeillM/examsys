<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cczsa1
 * Date: 05/11/12
 * Time: 11:32
 *
 *
 * UserObject Class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */
class UserObject {

  // include old variables as private ones in this class
  private $password, $userID, $userroles, $title, $initials, $surname, $username, $email, $grade, $year, $special_needs, $record_no, $split_username;

  private $roles,$staffModules,$db;

  function __construct(&$db) {
    $this->db=$db;
  }

  function old_load($array) {
    list($this->password, $this->userID, $this->userroles, $this->title, $this->initials, $this->surname, $this->username, $this->email, $this->grade, $this->year, $this->special_needs, $this->record_no, $this->split_username) = $array;

    if (strpos($this->userroles, 'SysAdmin') !== false) {
      $this->roles['SysAdmin']=1;
    }
    if (strpos($this->userroles, 'Staff') !== false or strpos($userroles, 'Admin') !== false) { // Process staff first to get higher priority than students --no need
      $this->roles['Staff']=1;
    }
    if (strpos($this->userroles, 'Student') !== false) {
      $this->roles['Student']=1;
    }
    if (strpos($this->userroles, 'External Examiner') !== false) {
      $this->roles['ExternalExaminer']=1;
    }
    if (strpos($this->userroles, 'Invigilator') !== false) {
      $this->roles['Invigilator']=1;
    }



  }


  function old_getuserroles() {
    return $this->userroles;
  }


  function IsExternalExaminer($exclusive=0) {
    if($exclusive == 0) {
      if(isset($this->roles['ExternalExaminer'])) {
        return true;
      }
    }
    return false;
  }

  function IsSysAdmin($exclusive=0) {
    if($exclusive == 0) {
      if(isset($this->roles['SysAdmin'])) {
        return true;
      }
    }
    return false;
  }

  function IsInvigilator($exclusive=0) {
    if($exclusive == 0) {
      if(isset($this->roles['Invigilator'])) {
        return true;
      }
    }
    return false;
  }

  function IsStaff($exclusive=0) {
    if($exclusive == 0) {
      if(isset($this->roles['Staff'])) {
        return true;
      }
    }
    return false;
  }

  function HasRole($roles, $exclusive = 0) {
    if (is_string($roles)) {
      if ($exclusive == 0  or ($exclusive == 1 and count($this->roles) == 1)) {
        if (isset($this->roles[$roles])) {
          return true;
        }
      }
    } else {
      // assume array
      if($exclusive == 0 or ($exclusive == 1 and count($this->roles) == count($roles))) {
        foreach($roles as $role) {
          if(isset($this->roles[$role])) {
            return true;
          }
        }
      }
    }
    return false;
  }

  function ListUserRoles() {
    return array_keys($this->roles);
  }

  function GetYear() {
    return $this->year;
  }

  function &GetUserID() {
    return $this->userID;
  }

  function GetStaffModules() {
    if(count($this->staffModules)<1) {
      $this->LoadStaffModules();
    }
    return $this->staffModules;

  }


  function LoadStaffModules() {
    $this->staffModules = array();

    $result = $this->db->prepare("SELECT idMod,moduleID FROM modules_staff,modules WHERE modules_staff.idMod = modules.id AND memberID=? AND modules.moduleID IS NOT NULL ORDER BY modules.moduleID");
    $result->bind_param('i', $this->userID);
    $result->execute();
    $result->bind_result($idMod,$moduleID);
    while ($result->fetch()) {
      $this->staffModules[$idMod] = $moduleID;
    }
    $result->close();

    return $this->staffModules;

  }

  function IsSpecialNeeds() {
    if($this->special_needs != 0) {
      return true;
    }
    return false;
  }


  function GetGrade() {
    return $this->grade;
  }
}
