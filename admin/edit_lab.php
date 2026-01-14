<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';
require '../include/errors.php';

$labID = check_var('labID', 'REQUEST', true, false, true);

// Find lab
$results = $mysqli->prepare('SELECT labs.name, campus.id, building, room_no, timetabling, it_support, plagarism'
    . ' FROM labs, campus'
    . ' WHERE labs.campus = campus.id'
    . ' AND labs.id = ?'
    . ' LIMIT 1');
$results->bind_param('i', $labID);
$results->execute();
$results->store_result();
$results->bind_result($name, $campus, $building, $room_no, $timetabling, $it_support, $plagarism);

if ($results->num_rows == 0) { // Lab not found
    $results->close();
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$results->fetch();
$results->close();

// Find associated addresses
$addresses = [];
$result = $mysqli->prepare('SELECT address, low_bandwidth FROM client_identifiers WHERE lab = ?');
$result->bind_param('i', $labID);
$result->execute();
$result->bind_result($address, $low_bandwidth);
while ($result->fetch()) {
    $addresses[$address] = $address;
}
$result->close();

$bad_addresses = [];

$campusobj = new campus($mysqli);
$campuses = $campusobj->get_all_campus_details();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
        <title>ExamSys: <?php echo $string['editcomputerlab']; ?></title>

        <link rel="stylesheet" type="text/css" href="../css/body.css" />
        <link rel="stylesheet" type="text/css" href="../css/header.css" />
        <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
        <link rel="stylesheet" type="text/css" href="../css/lab.css" />

        <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
        <script src='../js/require.js'></script>
        <script src='../js/main.min.js'></script>

    </head>

    <body>
        <?php
        require '../include/lab_options.inc';
        require '../include/toprightmenu.inc';

        echo draw_toprightmenu(231);
        ?>
        <div id="content">
            <form id="theform" action="" method="post" autocomplete="off">
                <div class="head_title">
                    <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
                    <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><a href="./index.php"><?php echo $string['administrativetools']; ?></a><a href="./list_labs.php"><?php echo $string['computerlabs'] ?></a></div>
                    <div class="page_title"><?php echo $string['editlab'] ?></div>
                </div>

                <div class="invalidlab"></div>
                <div class="inuselab"></div>
                <br />

                <table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; margin-left:10px; margin-right:10px">
                    <tr>
                        <td style="vertical-align:top; width:200px">
                            <div><label for="addresses"><?php echo $string['ipaddresses'] ?></label></div>
                            <textarea cols="20" rows="28" style="width:200px; height:590px" name="addresses" id="addresses" required><?= implode(PHP_EOL, $addresses); ?></textarea>
                        </td>
                        <td style="width:50px"></td>
                        <td style="vertical-align:top">
                            <div><label for="name"><?php echo $string['name'] ?></label></div>
                            <div><input type="text" size="40" maxlength="255" name="name" id="name" value="<?= $name; ?>" required /></div>
                            <br />

                            <div><label for="campus"><?= $string['campus'] ?></label></div>
                            <div>
                                <select name="campus" id="campus">
                                    <?php foreach ($campuses as $key => $campusarray) : ?>
                                        <option value="<?= $key; ?>"<?php if ($campus == $key) :
                                            ?> selected<?php
                                                       endif; ?>><?= $campusarray['campusname']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <br />

                            <div><label for="building"><?php echo $string['building'] ?></label></div>
                            <div><input type="text" size="40" maxlength="255" name="building" id="building" value="<?= $building; ?>" required /></div>
                            <br />

                            <div><label for="room_no"><?php echo $string['roomnumber'] ?></label></div>
                            <div><input type="text" size="10" maxlength="255" name="room_no" id="room_no" value="<?= $room_no; ?>" required /></div>
                            <br />

                            <div><?php echo $string['bandwidth'] ?></div>
                            <div>
                                <input type="radio" name="low_bandwidth" id="low_bandwidth_1" value="1"<?php if ($low_bandwidth) :
                                    ?> checked<?php
                                                                                                       endif; ?> /><label for="low_bandwidth_1"><?php echo $string['low'] ?></label>
                                &nbsp;&nbsp;&nbsp;
                                <input type="radio" name="low_bandwidth" id="low_bandwidth_2" value="0"<?php if (!$low_bandwidth) :
                                    ?> checked<?php
                                                                                                       endif; ?> /><label for="low_bandwidth_2"><?php echo $string['high'] ?></label>
                            </div>
                            <br />

                            <div><label for="timetabling"><?php echo $string['timetabling'] ?></label></div>
                            <div>
                                <textarea name="timetabling" id="timetabling" rows="3" cols="100"><?= $timetabling; ?></textarea>
                            </div>
                            <br />

                            <div><label for="it_support"><?php echo $string['itsupport'] ?></label></div>
                            <div>
                                <textarea name="it_support" id="it_support" rows="3" cols="100"><?= $it_support; ?></textarea>
                            </div>
                            <br />

                            <div><label for="plagarism"><?php echo $string['plagarism'] ?></label></div>
                            <div>
                                <textarea name="plagarism" id="plagarism" rows="3" cols="100"><?= $plagarism; ?></textarea>
                            </div>
                            <br />
                            <br />

                            <input type="submit" name="submit" value="<?php echo $string['save'] ?>" class="ok" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
        // JS utils dataset.
        $render = new render($configObject);
        $miscdataset['name'] = 'dataset';
        $miscdataset['attributes']['posturl'] = 'do_edit_lab.php?labID=' . $labID;
        $render->render($miscdataset, [], 'dataset.html');
        $jsdataset['name'] = 'jsutils';
        $jsdataset['attributes']['xls'] = json_encode($string);
        $render->render($jsdataset, [], 'dataset.html');
        ?>
        <script src="../js/labinit.min.js"></script>
    </body>
</html>
