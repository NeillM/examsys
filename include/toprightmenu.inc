<?php
echo $configObject->get('cfg_js_root');

function draw_toprightmenu($helpID = 1) {
  $configObject = Config::get_instance();

  $html = '';
  $html .= '<div id="toprightmenu">';

  $html .= "<div class=\"trm_div\" onclick=\"launchHelp($helpID)\">Help &amp; Support</div>";
  $html .= '<div class="trm_div" id="logout">Log out</div>';
  $html .= '<div class="trm_div" id="aboutrogo">About Rogo ' . $configObject->get('rogo_version') . '</div>';

  $html .= '</div>';
	
  return $html;
}
?>