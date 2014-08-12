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

Class StaffHelp {
  private $userObject;
  private $configObject;
  private $string;
  private $notice;
  private $db;

  public function __construct($userObject, $configObject, $string, $notice, $db) {
    $this->userObject   = $userObject;
    $this->configObject = $configObject;
    $this->string       = $string;
    $this->notice       = $notice;
    $this->db           = $db;
  }
  
  public function display_toolbar($id) {
    echo '<form name="myform" action="post" method="post" onsubmit="search(); return false;">';
    echo '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
    echo '<tr style="height:25px">';
    echo '<td style="width:8px; height:25px">&nbsp;</td><td colspan="3"><img src="../back_off.png" width="23" height="22" title="' . $this->string['back'] . '" alt="' . $this->string['back'] . '" name="back" onclick="history.back()" onmouseover="roll(\'back\',\'../back_on.png\')" onmouseout="roll(\'back\',\'../back_off.png\')" /><img src="../forwards_off.png" width="23" height="22" title="' . $this->string['forwards'] . '" alt="' . $this->string['forwards'] . '" name="forwards" onclick="history.forward()" onmouseover="roll(\'forwards\',\'../forwards_on.png\')" onmouseout="roll(\'forwards\',\'../forwards_off.png\')" /><img src="../home_off.png" width="23" height="22" title="' . $this->string['home'] . '" alt="' . $this->string['home'] . '" name="home" onclick="goHome()" onmouseover="roll(\'home\',\'../home_on.png\')" onmouseout="roll(\'home\',\'../home_off.png\')" />';
    if ($this->userObject->has_role('SysAdmin')) {
      echo '<img src="../divider.png" width="6" height="22" alt="|" /><a href="delete_page.php?id=' . $id . '"><img src="../delete_off.png" width="23" height="22" title="' . $this->string['delete'] . '" alt="' . $this->string['delete'] . '" name="delete" onmouseover="roll(\'delete\',\'../delete_on.png\')" onmouseout="roll(\'delete\',\'../delete_off.png\')" /></a><img src="../divider.png" width="6" height="22" alt="|" /><img src="../new_off.png" width="23" height="22" border="0" title="' . $this->string['new'] . '" alt="' . $this->string['new'] . '" name="new" onclick="newPage()" onmouseover="roll(\'new\',\'../new_on.png\')" onmouseout="roll(\'new\',\'../new_off.png\')" /><a href="new_pointer.php"><img src="../pointer_off.png" width="23" height="22" title="' . $this->string['pointer'] . '" alt="' . $this->string['pointer'] . '" name="pointer" onmouseover="roll(\'pointer\',\'../pointer_on.png\')" onmouseout="roll(\'pointer\',\'../pointer_off.png\')" /></a><a href="edit_page.php?id=' . $id . '"><img src="../edit_off.png" width="23" height="22" title="' . $this->string['edit'] . '" alt="' . $this->string['edit'] . '" name="edit" onmouseover="roll(\'edit\',\'../edit_on.png\')" onmouseout="roll(\'edit\',\'../edit_off.png\')" /></a><img src="../divider.png" width="6" height="22" alt="|" /><img src="../recycle_bin_off.png" width="23" height="22" title="' . $this->string['recyclebin'] . '" alt="' . $this->string['recyclebin'] . '" name="recycle_bin" onclick="recycleBin()" onmouseover="roll(\'recycle_bin\',\'../recycle_bin_on.png\')" onmouseout="roll(\'recycle_bin\',\'../recycle_bin_off.png\')" /><img src="../info_off.png" width="23" height="22" title="' . $this->string['info'] . '" alt="' . $this->string['info'] . '" name="info" onclick="infoPage()" onmouseover="roll(\'info\',\'../info_on.png\')" onmouseout="roll(\'info\',\'../info_off.png\')" /></td>';
    }
    echo '<td style="text-align:right; width:50%"><input type="text" id="searchbox" name="searchstring" value="" style="width:200px; border:1px solid white; font-size:80%" onmouseover="document.getElementById(\'searchbox\').style.borderColor=\'#FFBD69\'" onmouseout="document.getElementById(\'searchbox\').style.borderColor=\'white\'" /></td><td style="padding-left:4px; width:20px"><img onclick="search()" src="../search.png" width="16" height="16" title="' . $this->string['search'] . '" alt="' . $this->string['search'] . '" /></td><td onclick="search()" style="width:50px; font-size:9pt; font-family:Arial,sans-serif; padding-left:2px; padding-top:4px; vertical-align:top">' . $this->string['search'] . '</td></tr></table></form>';
  }

  public function display_toc($id) {
    if ($this->userObject->has_role('SysAdmin')) {
      $sql = 'SELECT articleid, title FROM staff_help WHERE roles IN ("SysAdmin", "Admin", "Staff") AND deleted IS NULL AND language = ? ORDER BY title, id';
    } elseif ($this->userObject->has_role('Admin')) {
      $sql = 'SELECT articleid, title FROM staff_help WHERE roles IN ("Admin", "Staff") AND deleted IS NULL AND language = ? ORDER BY title, id';
    } else {
      $sql = 'SELECT articleid, title FROM staff_help WHERE roles = "Staff" AND deleted IS NULL AND language = ? ORDER BY title, id';
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
      if (isset($help_toc_titles[$id])) {
        $slash_pos = strpos($help_toc_titles[$id], '/');

        if ($slash_pos !== false) {
          $target_parent = substr($help_toc_titles[$id], 0, $slash_pos);


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
            echo "<div><nobr><a href=\"\" class=\"book\" onclick=\"updateMenu('submenu$id','button$id'); return false;\"><img src=\"../$icon\" id=\"button$id\" class=\"icon16_active\" />" . $parent . "</a></nobr></div>\n";
            echo "<div style=\"display:block; margin-left:18px\" id=\"submenu$id\">";
          } else {
            $icon = 'closed_book.png';
            echo "<div><nobr><a href=\"\" class=\"book\" onclick=\"updateMenu('submenu$id','button$id'); return false;\"><img src=\"../$icon\" id=\"button$id\" class=\"icon16_active\" />" . $parent . "</a></nobr></div>\n";
            echo "<div style=\"display:none; margin-left:18px\" id=\"submenu$id\">";
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

      echo "<div id=\"title$id\"><nobr><a href=\"index.php?id=$id\"><img src=\"../$icon\" class=\"icon16_active\" />$tmp_title</a></nobr></div>\n";
    }

    if ($old_parent != '') echo "</div>\n";
    echo "<input type=\"hidden\" id=\"old_highlight\" value=\"0\" />";    
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

  function display_page($id) {
    if ($this->userObject->has_role('SysAdmin')) {
      $sql = 'SELECT title, body, type, deleted FROM staff_help WHERE articleid = ? AND language = ? AND roles IN ("SysAdmin", "Admin", "Staff")';
    } elseif ($this->userObject->has_role('Admin')) {
      $sql = 'SELECT title, body, type, deleted FROM staff_help WHERE articleid = ? AND language = ? AND roles IN ("Admin", "Staff")';
    } else {
      $sql = 'SELECT title, body, type, deleted FROM staff_help WHERE articleid = ? AND language = ? AND roles = "Staff"';
    }

    $search_results = $this->db->prepare($sql);
    $search_results->bind_param('is', $id, $_SESSION['ROGO_language']);
    $search_results->execute();
    $search_results->store_result();
    $search_results->bind_result($tmp_title, $tmp_body, $type, $deleted);
    while ($search_results->fetch()) {
      $edit_id = $id;
      if ($type == 'pointer') {
        $pointer_results = $this->db->prepare("SELECT title, body, deleted FROM staff_help WHERE articleid = ? AND language = ?");
        $pointer_results->bind_param('is', $tmp_body, $_SESSION['ROGO_language']);
        $pointer_results->execute();
        $pointer_results->store_result();
        $pointer_results->bind_result($tmp_title, $tmp_body, $deleted);
        $pointer_results->fetch();
        $pointer_results->close();
        $edit_id = $tmp_body;
      }
    }
    $search_results->free_result();
    $search_results->close();

    if ($tmp_body == '' and $tmp_title == '') {
      $msg = sprintf($this->string['furtherassistance'], $this->configObject->get('support_email'), $this->configObject->get('support_email'));
      $this->notice->display_notice_and_exit($this->db, $this->string['pagenotfound'], $msg, $this->string['pagenotfound'], '/artwork/page_not_found.png', '#C00000');
    }

    if ($id != '1' and !$this->userObject->has_role('SysAdmin')) {   // Don't record the homepage or SysAdmin activities.
      $result = $mysqli->prepare("INSERT INTO help_log VALUES (NULL, 'staff', ?, NOW(), ?)");
      $result->bind_param('ii', $this->userObject->get_user_ID(), $id);
      $result->execute();  
      $result->close();
    }

    if ($id == 1) {
      // ID 1 is for the homepage.
      echo "<div>\n";
    } else {
      echo "<div class=\"path\">" . $this->getPath($tmp_title) . "</div>";
      echo "<div style=\"padding:20px; font-size:160%; font-weight:bold; margin-bottom:5px; color:#295AAD\">" . $this->getTitle($tmp_title) . "</div>\n<hr style=\"width:100%; background-color:#B6B6B6; color:#B6B6B6; height:1px; border:0px\" />\n";
      echo "<div style=\"margin-left:20px; margin-right:20px\">\n";
    }

    $offset = 0;

    // Perform replacement on certain strings.
    $tmp_body = str_replace('$support_email', '<a href="mailto:' . $this->configObject->get('support_email') . '">' . $this->configObject->get('support_email') . '</a>', $tmp_body);

    $tmp_body = str_replace('$local_server', NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'], $tmp_body);

    if (isset($highlight)) {
      do {
        $found = stripos($tmp_body, $highlight, $offset);
        if ($found !== false) {
          $first_part = substr($tmp_body, 0 , $found);
          $open_bracket = strrpos($first_part, '<');
          $close_bracket = strrpos($first_part, '>');
          if (($open_bracket < $found and $found < $close_bracket) or ($close_bracket < $open_bracket)) {
            $offset = $found + strlen($highlight);
          } else {
            $tmp_body = substr($tmp_body, 0, $found) . '<span style="background-color:#FFFF00">' . substr($tmp_body, $found, strlen($highlight)) . '</span>' . substr($tmp_body, $found + strlen($highlight));
            $offset = $found + 48;
          }
        }
      } while ($found !== false);
    }
    echo $tmp_body;
    if ($id > 1) {
      echo "<br clear=\"all\" />\n<hr style=\"width:100%; background-color:#B6B6B6; color:#B6B6B6; height:1px; border:0px; margin-bottom:5px\" />\n</div>\n";
      echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\"><tr>";
      echo "<td style=\"padding-left:20px\"><a style=\"color:#003366\" href=\"#\" class=\"gototop\"><img src=\"../../artwork/top_icon.gif\" width=\"9\" height=\"12\" alt=\"" . $this->string['top'] . "\" /></a>&nbsp;<a style=\"color:#003366\" href=\"#\"class=\"gototop\">" . $this->string['top'] . "</a></td><td style=\"padding-right:20px; text-align:right\">&copy; 2014, The University of Nottingham</td></tr>";
      if ($this->userObject->has_role('SysAdmin')) {
        echo '<tr><td colspan="2" style="padding-right:20px; text-align:right; color:#316AC5">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $this->configObject->get('cfg_root_path') . '/help/staff/index.php?id=' . $id . '</tr>';
      }
      echo "</table>\n";
    }    
  }
  
  
}

Class StudentHelp {
  private $userObject;
  private $configObject;
  private $string;
  private $notice;
  private $db;

  public function __construct($userObject, $configObject, $string, $notice, $db) {
    $this->userObject   = $userObject;
    $this->configObject = $configObject;
    $this->string       = $string;
    $this->notice       = $notice;
    $this->db           = $db;
  }
  
  public function display_toolbar($id) {
    echo '<form name="myform" action="post" method="post" onsubmit="search(); return false;">';
    echo '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
    echo '<tr style="height:25px">';
    echo '<td style="width:8px; height:25px">&nbsp;</td><td colspan="3"><img src="../back_off.png" width="23" height="22" title="' . $this->string['back'] . '" alt="' . $this->string['back'] . '" name="back" onclick="history.back()" onmouseover="roll(\'back\',\'../back_on.png\')" onmouseout="roll(\'back\',\'../back_off.png\')" /><img src="../forwards_off.png" width="23" height="22" title="' . $this->string['forwards'] . '" alt="' . $this->string['forwards'] . '" name="forwards" onclick="history.forward()" onmouseover="roll(\'forwards\',\'../forwards_on.png\')" onmouseout="roll(\'forwards\',\'../forwards_off.png\')" /><img src="../home_off.png" width="23" height="22" title="' . $this->string['home'] . '" alt="' . $this->string['home'] . '" name="home" onclick="goHome()" onmouseover="roll(\'home\',\'../home_on.png\')" onmouseout="roll(\'home\',\'../home_off.png\')" />';
    if ($this->userObject->has_role('SysAdmin')) {
      echo '<img src="../divider.png" width="6" height="22" alt="|" /><a href="delete_page.php?id=' . $id . '"><img src="../delete_off.png" width="23" height="22" title="' . $this->string['delete'] . '" alt="' . $this->string['delete'] . '" name="delete" onmouseover="roll(\'delete\',\'../delete_on.png\')" onmouseout="roll(\'delete\',\'../delete_off.png\')" /></a><img src="../divider.png" width="6" height="22" alt="|" /><img src="../new_off.png" width="23" height="22" border="0" title="' . $this->string['new'] . '" alt="' . $this->string['new'] . '" name="new" onclick="newPage()" onmouseover="roll(\'new\',\'../new_on.png\')" onmouseout="roll(\'new\',\'../new_off.png\')" /><a href="new_pointer.php"><img src="../pointer_off.png" width="23" height="22" title="' . $this->string['pointer'] . '" alt="' . $this->string['pointer'] . '" name="pointer" onmouseover="roll(\'pointer\',\'../pointer_on.png\')" onmouseout="roll(\'pointer\',\'../pointer_off.png\')" /></a><a href="edit_page.php?id=' . $id . '"><img src="../edit_off.png" width="23" height="22" title="' . $this->string['edit'] . '" alt="' . $this->string['edit'] . '" name="edit" onmouseover="roll(\'edit\',\'../edit_on.png\')" onmouseout="roll(\'edit\',\'../edit_off.png\')" /></a><img src="../divider.png" width="6" height="22" alt="|" /><img src="../recycle_bin_off.png" width="23" height="22" title="' . $this->string['recyclebin'] . '" alt="' . $this->string['recyclebin'] . '" name="recycle_bin" onclick="recycleBin()" onmouseover="roll(\'recycle_bin\',\'../recycle_bin_on.png\')" onmouseout="roll(\'recycle_bin\',\'../recycle_bin_off.png\')" /><img src="../info_off.png" width="23" height="22" title="' . $this->string['info'] . '" alt="' . $this->string['info'] . '" name="info" onclick="infoPage()" onmouseover="roll(\'info\',\'../info_on.png\')" onmouseout="roll(\'info\',\'../info_off.png\')" /></td>';
    }
    echo '<td style="text-align:right; width:50%"><input type="text" id="searchbox" name="searchstring" value="" style="width:200px; border:1px solid white; font-size:80%" onmouseover="document.getElementById(\'searchbox\').style.borderColor=\'#FFBD69\'" onmouseout="document.getElementById(\'searchbox\').style.borderColor=\'white\'" /></td><td style="padding-left:4px; width:20px"><img onclick="search()" src="../search.png" width="16" height="16" title="' . $this->string['search'] . '" alt="' . $this->string['search'] . '" /></td><td onclick="search()" style="width:50px; font-size:9pt; font-family:Arial,sans-serif; padding-left:2px; padding-top:4px; vertical-align:top">' . $this->string['search'] . '</td></tr></table></form>';
  }

  public function display_toc($id) {
    $sql = 'SELECT articleid, title FROM student_help WHERE id != 1 AND deleted IS NULL AND language = ? ORDER BY title, id';
    
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
      if (isset($help_toc_titles[$id])) {
        $slash_pos = strpos($help_toc_titles[$id], '/');

        if ($slash_pos !== false) {
          $target_parent = substr($help_toc_titles[$id], 0, $slash_pos);


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
            echo "<div><nobr><a href=\"\" class=\"book\" onclick=\"updateMenu('submenu$id','button$id'); return false;\"><img src=\"../$icon\" id=\"button$id\" class=\"icon16_active\" />" . $parent . "</a></nobr></div>\n";
            echo "<div style=\"display:block; margin-left:18px\" id=\"submenu$id\">";
          } else {
            $icon = 'closed_book.png';
            echo "<div><nobr><a href=\"\" class=\"book\" onclick=\"updateMenu('submenu$id','button$id'); return false;\"><img src=\"../$icon\" id=\"button$id\" class=\"icon16_active\" />" . $parent . "</a></nobr></div>\n";
            echo "<div style=\"display:none; margin-left:18px\" id=\"submenu$id\">";
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

      echo "<div id=\"title$id\"><nobr><a href=\"index.php?id=$id\"><img src=\"../$icon\" class=\"icon16_active\" />$tmp_title</a></nobr></div>\n";
    }

    if ($old_parent != '') echo "</div>\n";
    echo "<input type=\"hidden\" id=\"old_highlight\" value=\"0\" />";    
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

  function display_page($id) {
    $sql = 'SELECT title, body, type, deleted FROM student_help WHERE id = ? AND language = ?';

    $search_results = $this->db->prepare($sql);
    $search_results->bind_param('is', $id, $_SESSION['ROGO_language']);
    $search_results->execute();
    $search_results->store_result();
    $search_results->bind_result($tmp_title, $tmp_body, $type, $deleted);
    while ($search_results->fetch()) {
      $edit_id = $id;
      if ($type == 'pointer') {
        $pointer_results = $this->db->prepare("SELECT title, body, deleted FROM student_help WHERE articleid = ? AND language = ?");
        $pointer_results->bind_param('is', $tmp_body, $_SESSION['ROGO_language']);
        $pointer_results->execute();
        $pointer_results->store_result();
        $pointer_results->bind_result($tmp_title, $tmp_body, $deleted);
        $pointer_results->fetch();
        $pointer_results->close();
        $edit_id = $tmp_body;
      }
    }
    $search_results->free_result();
    $search_results->close();

    if ($tmp_body == '' and $tmp_title == '') {
      $msg = sprintf($this->string['furtherassistance'], $this->configObject->get('support_email'), $this->configObject->get('support_email'));
      $this->notice->display_notice_and_exit($this->db, $this->string['pagenotfound'], $msg, $this->string['pagenotfound'], '/artwork/page_not_found.png', '#C00000');
    }

    if ($id != '1' and !$this->userObject->has_role('SysAdmin')) {   // Don't record the homepage or SysAdmin activities.
      $result = $mysqli->prepare("INSERT INTO help_log VALUES (NULL, 'student', ?, NOW(), ?)");
      $result->bind_param('ii', $this->userObject->get_user_ID(), $id);
      $result->execute();  
      $result->close();
    }

    if ($id == 1) {
      // ID 1 is for the homepage.
      echo "<div>\n";
    } else {
      echo "<div class=\"path\">" . $this->getPath($tmp_title) . "</div>";
      echo "<div style=\"padding:20px; font-size:160%; font-weight:bold; margin-bottom:5px; color:#295AAD\">" . $this->getTitle($tmp_title) . "</div>\n<hr style=\"width:100%; background-color:#B6B6B6; color:#B6B6B6; height:1px; border:0px\" />\n";
      echo "<div style=\"margin-left:20px; margin-right:20px\">\n";
    }

    $offset = 0;

    // Perform replacement on certain strings.
    $tmp_body = str_replace('$support_email', '<a href="mailto:' . $this->configObject->get('support_email') . '">' . $this->configObject->get('support_email') . '</a>', $tmp_body);

    $tmp_body = str_replace('$local_server', NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'], $tmp_body);

    if (isset($highlight)) {
      do {
        $found = stripos($tmp_body, $highlight, $offset);
        if ($found !== false) {
          $first_part = substr($tmp_body, 0 , $found);
          $open_bracket = strrpos($first_part, '<');
          $close_bracket = strrpos($first_part, '>');
          if (($open_bracket < $found and $found < $close_bracket) or ($close_bracket < $open_bracket)) {
            $offset = $found + strlen($highlight);
          } else {
            $tmp_body = substr($tmp_body, 0, $found) . '<span style="background-color:#FFFF00">' . substr($tmp_body, $found, strlen($highlight)) . '</span>' . substr($tmp_body, $found + strlen($highlight));
            $offset = $found + 48;
          }
        }
      } while ($found !== false);
    }
    echo $tmp_body;
    if ($id > 1) {
      echo "<br clear=\"all\" />\n<hr style=\"width:100%; background-color:#B6B6B6; color:#B6B6B6; height:1px; border:0px; margin-bottom:5px\" />\n</div>\n";
      echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:90%\"><tr>";
      echo "<td style=\"padding-left:20px\"><a style=\"color:#003366\" href=\"#\" class=\"gototop\"><img src=\"../../artwork/top_icon.gif\" width=\"9\" height=\"12\" alt=\"" . $this->string['top'] . "\" /></a>&nbsp;<a style=\"color:#003366\" href=\"#\"class=\"gototop\">" . $this->string['top'] . "</a></td><td style=\"padding-right:20px; text-align:right\">&copy; 2014, The University of Nottingham</td></tr>";
      if ($this->userObject->has_role('SysAdmin')) {
        echo '<tr><td colspan="2" style="padding-right:20px; text-align:right; color:#316AC5">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $this->configObject->get('cfg_root_path') . '/help/staff/index.php?id=' . $id . '</tr>';
      }
      echo "</table>\n";
    }    
  }
  
  
}
?>