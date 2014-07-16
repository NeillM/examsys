<?php
if (isset($low_bandwidth) and $low_bandwidth == 1) {
  // Lowbandwidth
  ob_start('ob_gzhandler');   // enable compression
}
$top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
$logo_html = '<td width="160"><img src="../config/logo.png" width="160" height="67" alt="Logo" /></td></tr></table>';
$bottom_html = '<table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
$bottom_html .= '<tr><td style="width:40px"><img id="fire_exit" src="../artwork/fire_exit.png" alt="' . $string['fireexit'] . '" /></td><td style="color:white;width:250px;font-size:90%">&#169; 2014, The University of Nottingham</td><td style="color:white;width:190px;text-align:center">';
?>