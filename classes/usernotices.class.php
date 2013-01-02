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
* A Class to hold functions designed to display notices to users. Including  
* access denied messages
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once $path . '/classes/rogostaticsingleton.class.php';

Class UserNotices extends RogoStaticSingleton {
  
  public static $inst = NULL;
  public static $class_name = 'UserNotices';

  /**
  * constructor
  */
  public function __construct() {}

  /**
   * This function will output a message to the user 
   *
   * @param string $title string title to display
   * @param string $msg string the message
   * @param string $icon name of the icon image file
   * @param string $title_color color of the tile text
   *
   */
  private function display_notice($title, $msg, $icon, $title_color = 'black') {
    $configObject = Config::Instance();
    $rp = $configObject->get('cfg_root_path');
    echo "<html>\n";
    echo "<head>\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
    echo "<meta http-equiv=\"content-type\" content=\"text/html;charset={$rp}\" />\n";
    echo "<title>$title</title>\n";
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$rp}/css/body.css\" />\n";
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$rp}/css/notice.css\" />\n";
    echo "</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\">";
    echo "<img src=\"$rp" . $icon . "\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"color:$title_color\">$title</h1>\n";
    echo "<hr />\n<p>$msg</p>";
  }

  /**
   * This function will output an access denied warning and terminate script 
   * execution
   *
   * @param string $message string message to display
   * @param string $output_header if true output 401 headers
   *
   */
  public function access_denied($message, $output_header = false, $output_footer = true) {
    global $mysqli, $string;
    
    $this->display_notice(  $string['accessdenied'], 
                                  $message, 
                                  '/artwork/access_denied.png', 
                                  '#C00000'
                                );

    if ($output_header == true) {
      echo '<div>';
      echo '<form method="POST">';
      echo '<p>Username:<input type="text" size="20" name="PHP_USER"><br /></p>';
      echo '<p>Password:<input type="password" size="20" name="PHP_PW"><br /></p>';
      echo '<p><input type="submit" name="submit-UserPW" value="Login"><br /></p>';
      echo '</form>';
      echo '</div>';
    }

    if ($output_footer) {
      echo "\n</body>\n</html>";
      if(is_object($mysqli)) {
        $mysqli->close();
      }
    }
    exit();
  }

}

?>