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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('Rog&#333;: ' . $string['questionsbank']); ?></title>
  <script src="../../js/require.js"></script>
  <script src="../../js/main.min.js"></script>
  <script src="../../js/questionsframeinit.min.js"></script>
  <link rel="stylesheet" type="text/css" href="../../css/add_questions.css" />
</head>
<body>
<div>
    <div class="wrapper">
        <div id="qbuttons"></div>
        <div id="qlist">
            <iframe id="iframeurl" src="add_questions_list.php?type=unused" name="iframeurl" frameborder="0">
                <p><?php echo $string['browsererr'];?></p>
            </iframe>
            <iframe id="previewurl" src ="preview_default.php" name="previewurl"  frameborder="0">
                <p><?php echo $string['browsererr'];?></p>
            </iframe>
        </div>
        <div name="controls" id="controls"></div>
    </div>
</div>
<?php
// Dataset.
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['paperid'] = param::required('paperID', param::INT, param::FETCH_GET);
$miscdataset['attributes']['module'] = param::optional('module', '',param::INT, param::FETCH_GET);
$miscdataset['attributes']['folder'] = param::optional('folder', '',param::INT, param::FETCH_GET);
$miscdataset['attributes']['disp'] = param::required('display_pos', param::INT, param::FETCH_GET);
$miscdataset['attributes']['srcofy'] = param::required('scrOfY', param::FLOAT, param::FETCH_GET);
$miscdataset['attributes']['max'] = param::required('max_screen', param::INT, param::FETCH_GET);
$render->render($miscdataset, array(), 'dataset.html');
?>
</body>

</html>
