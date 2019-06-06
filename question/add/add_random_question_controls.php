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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;</title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <style type="text/css">
    body {margin-right:4px; margin-bottom:2px; background-color:#F0F0EA}
  </style>
  <script src="../../js/require.js"></script>
  <script src="../../js/main.min.js"></script>
  <script src="../../js/randomquestionscontrolsinit.min.js"></script>
</head>
<body>

<div style="text-align:right">
    <input type="hidden" name="questions_to_add" id="questions_to_add" size="100" value="" />
    <input type="submit" name="submit" id="addrandomquestions" value="Add Questions" />
</div>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>