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
 * Listing of available campuses.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 */

require '../../include/sysadmin_auth.inc';
require '../../include/campus_options.inc';
require '../../include/toprightmenu.inc';

$campusobj = new campus($mysqli);
$campuses = $campusobj->get_all_campus_details();
foreach ($campuses as $key => $campus) {
    if ($campus['isdefault']) {
        $campuses[$key]['isdefault'] = "<img src=\"../../artwork/tick.gif\" id=\"yes\" />";
    } else {
        $campuses[$key]['isdefault'] = "<img src=\"../../artwork/cross.gif\" id=\"no\" />";
    }
}
$render = new render($configObject);
$toprightmenu = draw_toprightmenu(744);
$config['cfg_page_charset'] = $configObject->get('cfg_page_charset');
$config['cfg_install_type'] = $configObject->get('cfg_install_type');
$config['rogo_version'] = $configObject->get('rogo_version');
$lang['title'] = $string['campuses'];
$header = array(array('class' => 'col10', 'style' => 'width:80%', 'value' => $string['campus']),
array('class' => 'col', 'style' => 'width:20%', 'value' => $string['isdefault']));
$additionaljs ="
    <script type=\"text/javascript\" src=\"../../js/jquery_tablesorter/jquery.tablesorter.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/list.js\"></script>
    <script>
        function edit(id) {
          document.location.href='./edit_campuses.php?campus=' + id;
        }
        
        $(function () {
          if ($(\"#maindata\").find(\"tr\").size() > 1) {
            $(\"#maindata\").tablesorter({ 
              sortList: [[0,0]] 
            });
          }
          
          $(\".l\").click(function(event) {
            event.stopPropagation();
            selLine($(this).attr('id'),event);
          });
          
          $(\".l\").dblclick(function() {
            edit($(this).attr('id'));
          });
        });
    </script>";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/list.css\"/>";
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php",
 $string['computerlabs'] => "../list_labs.php");
$render->render_admin_header($lang, $config, $breadcrumb, $toprightmenu, $additionaljs, $addtionalcss);
$render->render_admin_list($campuses, $header);
$render->render_admin_footer();
                     