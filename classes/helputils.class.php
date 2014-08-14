<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * Utility class containing a set of generally methods for the online help system.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../../classes/networkutils.class.php';

Class OnlineHelp {
  private $userObject;
  private $configObject;
  private $string;
  private $notice;
  private $db;

  public function __construct($userObject, $configObject, $string, $notice, $type, $db) {
    $this->userObject   = $userObject;
    $this->configObject = $configObject;
    $this->string       = $string;
    $this->notice       = $notice;
    $this->type         = $type;
    $this->db           = $db;
    $this->highlight    = null;
  }
  
  public function set_highlight($highlight) {
    $this->highlight = $highlight;
  }
  
  public function display_toolbar($id) {
    echo "<script>\nvar id = $id;\n</script>\n";
    echo '<form name="myform" action="post" method="post" onsubmit="search(); return false;">';
    echo '<div class="toolbar_buttons"><img src="../back_off.png" width="23" height="22" title="' . $this->string['back'] . '" alt="' . $this->string['back'] . '" name="back" id="back" onmouseover="roll(\'back\',\'../back_on.png\')" onmouseout="roll(\'back\',\'../back_off.png\')" /><img src="../forwards_off.png" width="23" height="22" title="' . $this->string['forwards'] . '" alt="' . $this->string['forwards'] . '" name="forwards" id="forwards" onmouseover="roll(\'forwards\',\'../forwards_on.png\')" onmouseout="roll(\'forwards\',\'../forwards_off.png\')" /><img src="../home_off.png" width="23" height="22" title="' . $this->string['home'] . '" alt="' . $this->string['home'] . '" name="home" id="home" onmouseover="roll(\'home\',\'../home_on.png\')" onmouseout="roll(\'home\',\'../home_off.png\')" />';
    if ($this->userObject->has_role('SysAdmin')) {
      echo '<img src="../divider.png" width="6" height="22" alt="|" /><img src="../delete_off.png" width="23" height="22" title="' . $this->string['delete'] . '" alt="' . $this->string['delete'] . '" name="delete" id="delete" onmouseover="roll(\'delete\',\'../delete_on.png\')" onmouseout="roll(\'delete\',\'../delete_off.png\')" /><img src="../divider.png" width="6" height="22" alt="|" /><img src="../new_off.png" width="23" height="22" title="' . $this->string['new'] . '" alt="' . $this->string['new'] . '" name="new" id="new" onmouseover="roll(\'new\',\'../new_on.png\')" onmouseout="roll(\'new\',\'../new_off.png\')" /><img src="../pointer_off.png" width="23" height="22" title="' . $this->string['pointer'] . '" alt="' . $this->string['pointer'] . '" name="pointer" id="pointer" onmouseover="roll(\'pointer\',\'../pointer_on.png\')" onmouseout="roll(\'pointer\',\'../pointer_off.png\')" /><img src="../edit_off.png" width="23" height="22" title="' . $this->string['edit'] . '" alt="' . $this->string['edit'] . '" name="edit" id="edit" onmouseover="roll(\'edit\',\'../edit_on.png\')" onmouseout="roll(\'edit\',\'../edit_off.png\')" /><img src="../divider.png" width="6" height="22" alt="|" /><img src="../recycle_bin_off.png" width="23" height="22" title="' . $this->string['recyclebin'] . '" alt="' . $this->string['recyclebin'] . '" name="recycle_bin" id="recycle_bin" onmouseover="roll(\'recycle_bin\',\'../recycle_bin_on.png\')" onmouseout="roll(\'recycle_bin\',\'../recycle_bin_off.png\')" /><img src="../info_off.png" width="23" height="22" title="' . $this->string['info'] . '" alt="' . $this->string['info'] . '" name="info" id="info" onmouseover="roll(\'info\',\'../info_on.png\')" onmouseout="roll(\'info\',\'../info_off.png\')" />';
    }
    echo '</div><div class="toolbar_search"><input type="text" id="searchbox" name="searchstring" value="" placeholder="' . $this->string['search'] . '" /></td><td style="padding-left:4px; width:20px"><img onclick="search()" src="../search.png" width="16" height="16" title="' . $this->string['search'] . '" alt="' . $this->string['search'] . '" /></div></form>';
  }

  public function display_toc($pageid) {
    if (isset($_GET['scrOfY'])) {
      echo "<script>\nvar scrOfY = " . $_GET['scrOfY'] . ";\n</script>\n";
    } else {
      echo "<script>\nvar scrOfY = 0;\n</script>\n";
    }
    
    if ($this->type == 'student') {
      $sql = 'SELECT articleid, title FROM student_help WHERE id != 1 AND deleted IS NULL AND language = ? ORDER BY title, id';
    } else {
      if ($this->userObject->has_role('SysAdmin')) {
        $sql = 'SELECT articleid, title FROM staff_help WHERE roles IN ("SysAdmin", "Admin", "Staff") AND deleted IS NULL AND language = ? ORDER BY title, id';
      } elseif ($this->userObject->has_role('Admin')) {
        $sql = 'SELECT articleid, title FROM staff_help WHERE roles IN ("Admin", "Staff") AND deleted IS NULL AND language = ? ORDER BY title, id';
      } else {
        $sql = 'SELECT articleid, title FROM staff_help WHERE roles = "Staff" AND deleted IS NULL AND language = ? ORDER BY title, id';
      }
    }

    $sub_section = 0;
    $old_title = '';
    $parent = '';
    $old_parent = '';
    $help_toc = array();
    $help_toc_titles = array();
    
    $help_section = 0;
    $result = $this->db->prepare($sql);
    $result->bind_param('s', $_SESSION['ROGO_language']);
    $result->execute();
    $result->bind_result($id, $title);
    while ($result->fetch()) {
      $help_toc[$help_section]['id'] = $id;
      $help_toc[$help_section]['title'] = $title;
      $help_toc_titles[$id] = $title;
      $help_section++;
    }
    $result->close();
    
    $expand_id = 0;
    if ($id !== null) {
      if (isset($help_toc_titles[$pageid])) {
        $slash_pos = strpos($help_toc_titles[$pageid], '/');

        if ($slash_pos !== false) {
          $target_parent = substr($help_toc_titles[$pageid], 0, $slash_pos);


          for ($i=0; $i<$help_section; $i++) {
            if (strpos($help_toc[$i]['title'], $target_parent) === 0 and $expand_id == 0) {
              $expand_id = $help_toc[$i]['id'];
            }
          }
        }
      }
    }
    
    for ($i=0; $i<$help_section; $i++) {
      $id = $help_toc[$i]['id'];
      $slash_pos = strpos($help_toc[$i]['title'], '/');
      if ($slash_pos !== false) {
        $parent = substr($help_toc[$i]['title'], 0, $slash_pos);
        if ($old_parent != '' and $parent != $old_parent) {
          echo "</div>\n";
        }
        $tmp_title = substr($help_toc[$i]['title'], ($slash_pos + 1));

        if ($parent != $old_parent) {
          if ($expand_id == $id) {
            $icon = 'open_book.png';
            echo "<div class=\"book\" id=\"sect$id\"><img src=\"../$icon\" id=\"button$id\" class=\"icon16_active\" />" . $parent . "</div>\n";
            echo "<div class=\"open_submenu\" id=\"submenu$id\">";
          } else {
            $icon = 'closed_book.png';
            echo "<div class=\"book\" id=\"sect$id\"><img src=\"../$icon\" id=\"button$id\" class=\"icon16_active\" />" . $parent . "</div>\n";
            echo "<div class=\"closed_submenu\" id=\"submenu$id\">";
          }
        }
        $old_parent = $parent;
        $icon = 'single_page.png';      
      } else {
        if ($old_parent != '') {
          echo "</div>\n";
        }
        $tmp_title = $help_toc[$i]['title'];
        $icon = 'single_page.png';
        $parent = '';
        $old_parent = $parent;
      }
      if ($id == $pageid) {
        echo "<div id=\"title$id\" class=\"page\" style=\"font-weight:bold\"><img src=\"../$icon\" class=\"icon16_active\" />$tmp_title</div>\n";
      } else {
        echo "<div id=\"title$id\" class=\"page\"><img src=\"../$icon\" class=\"icon16_active\" />$tmp_title</div>\n";
      }
    }

    if ($old_parent != '') echo "</div>\n";
  }  

  private function getPath($path) {
    $parts = explode('/',$path);
    $path = '<a style="color:#666666" href="index.php?id=1">' . $this->string['home'] . '</a>';
    if (count($parts) > 1) {
      for ($i=0; $i<count($parts)-1; $i++) {
        $path .= " > <a style=\"color:#666666\" href=\"display_folder.php?title=" . $parts[$i] . "\">" . $parts[$i] . "</a>";
      }
    }

    return $path;
  }

  private function getTitle($path) {
    $parts = explode('/', $path);

    return $parts[count($parts) - 1];
  }
  
  public function get_page_details($articleid) {
    if ($this->type == 'student') {
      $sql = 'SELECT id, title, body, type, DATE_FORMAT(checkout_time,\'%Y%m%d%H%i%S\') AS checkout_time, checkout_authorID, NULL AS roles FROM student_help WHERE id = ? AND language = ? AND deleted IS NULL LIMIT 1';
    } else {
      if ($this->userObject->has_role('SysAdmin')) {
        $sql = 'SELECT id, title, body, type, DATE_FORMAT(checkout_time,\'%Y%m%d%H%i%S\') AS checkout_time, checkout_authorID, roles FROM staff_help WHERE articleid = ? AND language = ? AND roles IN ("SysAdmin", "Admin", "Staff") AND deleted IS NULL LIMIT 1';
      } elseif ($this->userObject->has_role('Admin')) {
        $sql = 'SELECT id, title, body, type, DATE_FORMAT(checkout_time,\'%Y%m%d%H%i%S\') AS checkout_time, checkout_authorID, roles FROM staff_help WHERE articleid = ? AND language = ? AND roles IN ("Admin", "Staff") AND deleted IS NULL LIMIT 1';
      } else {
        $sql = 'SELECT id, title, body, type, DATE_FORMAT(checkout_time,\'%Y%m%d%H%i%S\') AS checkout_time, checkout_authorID, roles FROM staff_help WHERE articleid = ? AND language = ? AND roles = "Staff" AND deleted IS NULL LIMIT 1';
      }
    }    
    $results = $this->db->prepare($sql);
    $results->bind_param('is', $articleid, $_SESSION['ROGO_language']);
    $results->execute();
    $results->store_result();

    $results->bind_result($id, $title, $body, $page_type, $checkout_time, $checkout_authorID, $roles);
    $results->fetch();
    $row_no = $results->num_rows;
    $results->close();
    
    if ($row_no == 0) {
      return false;
    }
    
    return array('id'=>$id, 'title'=>$title, 'body'=>$body, 'page_type'=>$page_type, 'checkout_time'=>$checkout_time, 'checkout_authorID'=>$checkout_authorID, 'roles'=>$roles);
  }
  
  public function save_page_details($title, $body, $roles, $articleid, $pointerid) {
    if ($articleid == $pointerid) {
      // Editing normal page.
      $result = $this->db->prepare("UPDATE staff_help SET title = ?, body = ?, body_plain = ?, checkout_time = NULL, checkout_authorID = NULL, roles = ? WHERE articleid = ? AND language = ?");
      $result->bind_param('ssssis', $title, $body, $tmp_body_plain, $_POST['page_roles'], $_POST['edit_id'], $_SESSION['ROGO_language']);
      $result->execute();
      $result->close();
    } else {
      // Editing a page pointed to.
      $result = $this->db->prepare("UPDATE staff_help SET title = ? WHERE articleid = ? AND language = ?");
      $result->bind_param('sis', $title, $articleid, $_SESSION['ROGO_language']);
      $result->execute();
      $result->close();

      $body_plain = strip_tags($body);
      $result = $this->db->prepare("UPDATE staff_help SET body = ?, body_plain = ?, checkout_time = NULL, checkout_authorID = NULL, roles = ? WHERE articleid = ? AND language = ?");
      $result->bind_param('sssis', $body, $body_plain, $roles, $pointerid, $_SESSION['ROGO_language']);
      $result->execute();
      $result->close();
    }
  }

  function display_page($id) {
    $page_details = $this->get_page_details($id);
    
    if ($page_details['page_type'] == 'pointer') {    // If pointer look up source page.
      $page_details = $this->get_page_details($page_details['body']);
    }    

    if ($page_details['body'] == '' and $page_details['title'] == '') {
      $msg = sprintf($this->string['furtherassistance'], $this->configObject->get('support_email'), $this->configObject->get('support_email'));
      $this->notice->display_notice_and_exit($this->db, $this->string['pagenotfound'], $msg, $this->string['pagenotfound'], '/artwork/page_not_found.png', '#C00000');
    }
    
    $this->record_in_log($id);

    if ($id == 1) {
      // ID 1 is for the homepage.
      echo "<div>\n";
    } else {
      echo "<div class=\"path\">" . $this->getPath($page_details['title']) . "</div>";
      echo "<div style=\"padding:20px; font-size:160%; font-weight:bold; margin-bottom:5px; color:#295AAD\">" . $this->getTitle($page_details['title']) . "</div>\n<hr style=\"width:100%; background-color:#B6B6B6; color:#B6B6B6; height:1px; border:0px\" />\n";
      echo "<div style=\"margin-left:20px; margin-right:20px\">\n";
    }

    $offset = 0;

    // Perform replacement on certain strings.
    $page_details['body'] = str_replace('$support_email', '<a href="mailto:' . $this->configObject->get('support_email') . '">' . $this->configObject->get('support_email') . '</a>', $page_details['body']);
    $page_details['body'] = str_replace('$local_server', NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'], $page_details['body']);

    if ($this->highlight !== null) {
      do {
        $found = stripos($page_details['body'], $this->highlight, $offset);
        if ($found !== false) {
          $first_part = substr($page_details['body'], 0 , $found);
          $open_bracket = strrpos($first_part, '<');
          $close_bracket = strrpos($first_part, '>');
          if (($open_bracket < $found and $found < $close_bracket) or ($close_bracket < $open_bracket)) {
            $offset = $found + strlen($this->highlight);
          } else {
            $page_details['body'] = substr($page_details['body'], 0, $found) . '<span style="background-color:#FFFF00">' . substr($page_details['body'], $found, strlen($this->highlight)) . '</span>' . substr($page_details['body'], $found + strlen($this->highlight));
            $offset = $found + 48;
          }
        }
      } while ($found !== false);
    }
    echo $page_details['body'];
    if ($id > 1) {    // Display footer
      echo "<div class=\"footer_line\"></div>\n";
      echo "<div class=\"footer_left gototop\"><img src=\"../../artwork/top_icon.gif\" width=\"9\" height=\"12\" />&nbsp;" . $this->string['top'] . "</div>\n";
      echo "<div class=\"footer_right\">&copy; 2014, The University of Nottingham";
      if ($this->userObject->has_role('SysAdmin')) {
        echo '<br /><span style="color:#316AC5">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $this->configObject->get('cfg_root_path') . '/help/staff/index.php?id=' . $id . '</span>';
      }
      echo "</div>\n";
    }    
  }
  
  private function record_in_log($id) {
    if ($id != '1' and !$this->userObject->has_role('SysAdmin')) {   // Don't record the homepage or SysAdmin activities.
      if ($this->type == 'student') {
        $sql = "INSERT INTO help_log VALUES (NULL, 'student', ?, NOW(), ?)";
      } else {
        $sql = "INSERT INTO help_log VALUES (NULL, 'staff', ?, NOW(), ?)";        
      }
      $result = $this->db->prepare($sql);
      $result->bind_param('ii', $this->userObject->get_user_ID(), $id);
      $result->execute();  
      $result->close();
    }
  }
  
  private function get_max_id() {
    $articleid = 0;
    if ($this->type == 'student') {
      $sql = 'SELECT MAX(articleid) FROM student_help';
    } else {
      $sql = 'SELECT MAX(articleid) FROM staff_help';      
    }
    $result = $this->db->prepare($sql);
    $result->execute();
    $result->bind_result($articleid);
    $result->fetch();
    $result->close();
    
    return $articleid;
  }
  
  public function create_page($title, $body, $roles = '') {
    $body_plain = strip_tags($body);
    
    $articleid = $this->get_max_id() + 1;

    if ($this->type == 'student') {
      $result = $this->db->prepare("INSERT INTO student_help VALUES (NULL, ?, ?, ?, 'page', NULL, NULL, NULL, ?, ?, '0000-00-00 00:00:00')");
      $result->bind_param('ssssi', title, $body, $body_plain, $_SESSION['ROGO_language'], $articleid);
    } else {
      $result = $this->db->prepare("INSERT INTO staff_help VALUES (NULL, ?, ?, ?, 'page', NULL, NULL, ?, NULL, ?, ?, '0000-00-00 00:00:00')");
      $result->bind_param('sssssi', $title, $body, $body_plain, $_POST['page_roles'], $_SESSION['ROGO_language'], $articleid);
    }
    $result->execute();  
    $result->close();
    
    return $this->db->insert_id;
  }
  
  public function create_pointer($title, $pageID) {
    $articleid = $this->get_max_id() + 1;
    
    if ($this->type == 'student') {
      $result = $this->db->prepare("INSERT INTO student_help VALUES (NULL, ?, ?, NULL, 'pointer', NULL, NULL, NULL, '" . $_SESSION['ROGO_language'] . "', ?, '0000-00-00 00:00:00')");
      $result->bind_param('ssi', $title, $pageID, $articleid);
      
    } else {
      $result = $this->db->prepare("INSERT INTO staff_help VALUES (NULL, ?, ?, NULL, 'pointer', NULL, NULL, 'Staff', NULL, '" . $_SESSION['ROGO_language'] . "', ?, '0000-00-00 00:00:00')");
      $result->bind_param('ssi', $title, $pageID, $articleid);
      
    }
    $result->execute();  
    $result->close();
    
    return $this->db->insert_id;
  }
  
  public function set_edit_lock($articleid) {
    if ($this->type == 'student') {
      $sql = 'UPDATE student_help SET checkout_time = NOW(), checkout_authorID = ? WHERE articleid = ? AND language = ?';
    } else {
      $sql = 'UPDATE staff_help SET checkout_time = NOW(), checkout_authorID = ? WHERE articleid = ? AND language = ?';
    }
    $result = $this->db->prepare($sql);
    $result->bind_param('iis', $this->userObject->get_user_ID(), $articleid, $_SESSION['ROGO_language']);
    $result->execute();
    $result->close();
  }
  
  public function release_edit_lock($articleid) {
    if ($this->type == 'student') {
      $sql = 'UPDATE student_help SET checkout_time = NULL, checkout_authorID = NULL WHERE articleid = ? AND language = ?';
    } else {
      $sql = 'UPDATE staff_help SET checkout_time = NULL, checkout_authorID = NULL WHERE articleid = ? AND language = ?';
    }
    $result = $this->db->prepare($sql);
    $result->bind_param('is', $articleid, $_SESSION['ROGO_language']);
    $result->execute();
    $result->close();
  }
  
  private function delete_id($pageID) {
    if ($this->type == 'student') {
      $table = 'student_help';
    } else {
      $table = 'staff_help';
    }
    $deleteQuery = $this->db->prepare("UPDATE $table SET deleted = NOW() WHERE articleid = ? AND language = ?");
    $deleteQuery->bind_param('is', $pageID, $_SESSION['ROGO_language']);
    $deleteQuery->execute();
    $deleteQuery->close();  
  }
  
  public function delete_page($originalID) {
    if ($this->type == 'student') {
      $table = 'student_help';
    } else {
      $table = 'staff_help';
    }

    $page_details = $this->get_page_details($originalID);

    if ($page_details['page_type'] == 'page') {
      // Search for any pointers to the current page.
      $result = $this->db->prepare("SELECT articleid, body FROM $table WHERE type = 'pointer' AND articleid != ? AND body = ? AND language = ?");
      $result->bind_param('iis', $originalID, $originalID, $_SESSION['ROGO_language']);
      $result->execute();
      $result->store_result();
      $result->bind_result($page_id, $body);
      while ($result->fetch()) {
        $this->delete_id($page_id);     // Delete the pointer page.
      }
      $result->close();
    }

    $this->delete_id($originalID);      // Delete the original page.
  }
  
  
}
?>