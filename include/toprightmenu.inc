<?php
echo $configObject->get('cfg_js_root');

function draw_toprightmenu($helpID = 1) {
  global $string;
	
  $configObject = Config::get_instance();

  $html = '';
  $html .= '<div id="toprightmenu">';

  $html .= "<div class=\"trm_div\" onclick=\"launchHelp($helpID)\">" . $string['helpsupport'] . "</div>";
  $html .= '<div class="trm_div" id="signout">' . $string['signout'] . '</div>';
  $html .= '<div class="trm_div" id="aboutrogo">' . sprintf($string['aboutrogo'], $configObject->get('rogo_version')) . '</div>';

  $html .= '</div>';
	
  return $html;
}
?>