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
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package Rogō
 */

require_once '../include/sysadmin_auth.inc';
require_once '../include/sidebar_menu.inc';
require_once '../classes/networkutils.class.php';
require_once '../classes/dateutils.class.php';
echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset={$configObject->get('cfg_page_charset')}" />

    <title>{$string['detailed_authentication_information']}</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css" />
    <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
    <style type="text/css">
        .sechead {background-color:#EBF2F7; color:#00156E; border-bottom: 1px solid #CFDBEB}
        a {color:#215DC6}
        a.heading {color:#215DC6; font-weight:bold}
        a.heading:hover {color:#428EFF; font-weight:bold}
    </style>

    <script type="text/javascript" src="../js/staff_help.js"></script>

</head>

<body>
HTML;
include '../include/admin_options.inc';
echo <<<HTML

<div id="content" class="content">

<table class="header">
<tr>
<th><div class="breadcrumb"><a href="../staff/index.php">{$string['home']}</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">{$string['administrativetools']}</a></div><div style="font-size:200%; margin-left:10px; font-weight:bold">{$string['detailed_authentication_information']}</div></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(240); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="{$string['help']}" border="0" /></a></th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<br />
<div>
  <div style="font-size: 225%">Authentication Plugins:</div>
HTML;
$authinfo = $authentication->version_info();

$settinginfo = '';
foreach ($authinfo->plugins as $number => $item) {
  $settinginfo = '';
  if (count($item->settings) > 0 ) {
    $settinginfo .='<tr><th>Name</th><th>Value</th><tr>';
    foreach ($item->settings as $key => $name) {
      $settinginfo .= '<tr><td>' . $key . '</td><td>' . $name . '</td></tr>' . "\n";
    }
  }
  $callbackinfo = '<tr><th>Section</th><th>Function</th></tr>';

  foreach ($authentication->callbacktypes as $types) {

    $callbackinfo .= "<tr><td>$types</td><td><table>";
    $default = '';
    if (isset($item->callbackfunctions)) {
      foreach ($item->callbackfunctions as $numb => $callbk) {
        if ($callbk[1] == $types) {
          $default = '<tr><td>' . $callbk[0] . '</td></tr>';
        }
      }
    }
    $callbackinfo .= "\n" . $default . '</table></td></tr>' . "\n";
  }

  $extra = '';
  $extra1 = '';
  if (!is_null($item->error)) {
    $extra = ' background-color: #cc0000;';
    $extra1 = "<tr style=\"background-color: #FFFFFF;\"><td>ERROR</td><td>$item->error</td></tr>";
  }

  //<tr><td>Order #:</td><td>$number</td></tr>

  echo <<<HTML

<div style="float:left; margin: 5px; $extra">


<div style="font-size:200%; float:left; font-weight:bold; text-align: left; " >&nbsp;$item->name</div>

<div style="clear:both">
<table border=1">
<tr><td>Type:</td><td>$item->classname</td></tr>
<tr><td>Config Item #:</td><td>$item->number</td></tr>
$extra1
<tr><td>Plugin Version:</td><td>$item->version</td></tr>
<tr><td>API Implemented:</td><td>$item->api_implimented</td></tr>

<tr>
<td>Settings:</td>
<td>
<table>
$settinginfo
</table>
</td>
</tr>



</table>
</div>
</div>
HTML;

  /*
   <tr>
<td>Callbacks Registered</td>
<td>
<table>
$callbackinfo
</table>
</td>
</tr>
   */

}

echo <<<HTML
</div>
 <div style="font-size: 225%; clear:both; ">Registered Callback Functions:</div>
HTML;

foreach ($authinfo->callbacks as $callbacksection => $callbackitem) {

  echo <<<HTML

<div style="float:left; margin: 5px; ">


<div style="font-size:200%; float:left; font-weight:bold; text-align: left; " >&nbsp;$callbacksection</div>

<div style="clear:both">
<table border=1">
HTML;
  if (count($callbackitem) == 0) {
    echo <<<HTML
<tr><td>NONE Registered</td></tr>
HTML;
  } else {
    echo <<<HTML
    <tr><th>Order</th><th>Plugin<br>Name</th><th>Function<br>Name</th></tr>
HTML;
    foreach ($callbackitem as $orderno => $item) {
      echo <<<HTML
<tr><td>$orderno</td><td>($item->pluginconfigid) $item->plugindescname</td><td>$item->functionname</td></tr>
HTML;
    }


  }

  echo <<<HTML
</table></div>
</div>

HTML;

}


$authconfig = var_export($configObject->get('authentication'), TRUE);
$authconfig = htmlentities($authconfig);
$authconfig = str_replace("\n", "<br>", $authconfig);


echo <<<HTML
<div style="clear:both; float:none;" >

  <div style="font-size: 225%">Authentication Debug from current Login:</div>

HTML;
$authentication->display_debug();
echo <<<HTML
</div>
</div>
HTML;

echo <<<HTML
<div style="clear:both; float:none;" >

  <div style="font-size: 225%">Authentication Section of config file:</div>

HTML;
var_dump($configObject->get('authentication'));
echo <<<HTML
</div>
</div>
HTML;
