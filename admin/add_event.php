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

require '../include/sysadmin_auth.inc';
?>
<!DOCTYPE html>
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['addevent'] ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:80%}
    .swatch {display:inline-block; width:40px; height:40px; border: 6px solid #F1F5FB}
    .dialog_header {font-size:200%; border-bottom: 1px solid #CCD9EA; background-image: url('../artwork/calendar_icon.png'); background-repeat:no-repeat; background-position: 10px 3px; padding-left:66px; line-height:56px; height:56px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<?php

if (isset($_POST['submit'])) {
  $title = trim($_POST['title']);
  $message = trim($_POST['message']);
  $thedate = $_POST['fyear'] . $_POST['fmonth'] . $_POST['fday'] . $_POST['ftime'];
  $duration = $_POST['duration'];
  $bgcolor = '#' . $_POST['color'];

  $result = $mysqli->prepare("INSERT INTO extra_cal_dates VALUES (NULL, ?, ?, ?, ?, ?)");
  $result->bind_param('sssis', $title, $message, $thedate, $duration, $bgcolor);
  $result->execute();  
  $result->close();
?>
  <script>
    $(function () {
      window.opener.location.reload();
      window.close();
    });
  </script>
  </head>
  <body>
    
  </body>
  </html>
<?php
  exit();
}

?>
  <script type="text/javascript">
    $(function () {
      $('.swatch').click(function() {
        current = $('#color').val();
        $('#' + current).css('border-color', '#F1F5FB');

        newvalue = $(this).attr('id');
        $('#' + newvalue).css('border-color', '#FFBD69');
        $('#color').val(newvalue)
        
      });
      
    });
  </script>
  </head>
<body>
  <div class="dialog_header"><?php echo $string['addevent'] ?></div>
  <form name="theform" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" style="padding:10px">
    
  <table style="width:99%">
    <tr>
      <td><?php echo $string['title'] ?></td>
      <td><input type="text" style="width:100%" name="title" required /></td>
    </tr>
    <tr>
      <td><?php echo $string['message'] ?></td>
      <td><textarea name="message" rows="6" style="width:100%"></textarea></td>
    </tr>
    <tr>
      <td><?php echo $string['date'] ?></td>
      <td><?php
      $date_array = getdate();
      // Available from Day
      $current_day = date('j');
      echo "<select id=\"fday\" name=\"fday\" class=\"datecopy\">\n";
      for ($i=1; $i<=31; $i++) {
        echo '<option value="';
        if ($i < 10) echo '0';
        echo "$i\"";
        if ($i == $current_day) echo ' selected';
        echo '>';
        if ($i < 10) echo '0';
        echo "$i</option>\n";
      }
      echo "</select><select id=\"fmonth\" name=\"fmonth\" class=\"datecopy\">\n";
      $current_month = date('n');
      if ($current_month > 12) $current_month = 1;
      $months = array('', 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
      for ($i=1; $i<=12; $i++) {
        $trans_month = mb_substr($string[$months[$i]],0,3,'UTF-8');
        if ($i < 10) {
          if ($i == $current_month) {
            echo "<option value=\"0$i\" selected>$trans_month</option>\n";
          } else {
            echo "<option value=\"0$i\">$trans_month</option>\n";
          }
        } else {
          if ($i == $current_month) {
            echo "<option value=\"$i\" selected>$trans_month</option>\n";
          } else {
            echo "<option value=\"$i\">$trans_month</option>\n";
          }
        }
      }
      echo "</select><select id=\"fyear\" name=\"fyear\" class=\"datecopy\">\n";
      for ($i = $date_array['year']; $i < ($date_array['year']+21); $i++) {
        if ($current_month == 1 and $i == ($date_array['year'] + 1)) {
          echo "<option value=\"$i\" selected>$i</option>\n";
        } else {
          echo "<option value=\"$i\">$i</option>\n";
        }
      }
      echo "</select><select id=\"ftime\" name=\"ftime\" class=\"datecopy\">\n";
      // Available from Hour
      $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
      foreach ($times as $key => $value) {
        echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
      }
      echo "</select>\n";
      ?></td>
    </tr>
    <tr>
      <td><?php echo $string['duration'] ?></td>
      <td>
        <select name="duration">
          <option value="5">5 <?php echo $string['mins'] ?></option>
          <option value="10">10 <?php echo $string['mins'] ?></option>
          <option value="20">20 <?php echo $string['mins'] ?></option>
          <option value="30">30 <?php echo $string['mins'] ?></option>
          <option value="60" selected>60 <?php echo $string['mins'] ?></option>
          <option value="90">1.5 <?php echo $string['hours'] ?></option>
          <option value="120">2 <?php echo $string['hours'] ?></option>
          <option value="180">3 <?php echo $string['hours'] ?></option>
          <option value="240">4 <?php echo $string['hours'] ?></option>
          <option value="300">5 <?php echo $string['hours'] ?></option>
          <option value="360">6 <?php echo $string['hours'] ?></option>
          <option value="420">7 <?php echo $string['hours'] ?></option>
          <option value="480">8 <?php echo $string['hours'] ?></option>
          <option value="540">9 <?php echo $string['hours'] ?></option>
          <option value="600">10 <?php echo $string['hours'] ?></option>
          <option value="660">11 <?php echo $string['hours'] ?></option>
          <option value="720">12 <?php echo $string['hours'] ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <td><?php echo $string['colour'] ?></td>
      <td>
        <div class="swatch" id="3A3838" style="background-color:#3A3838"></div>
        <div class="swatch" id="323F4F" style="background-color:#323F4F"></div>
        <div class="swatch" id="2E75B5" style="background-color:#2E75B5"></div>
        <div class="swatch" id="C55A11" style="background-color:#C55A11"></div>
        <div class="swatch" id="7B7B7B" style="background-color:#7B7B7B"></div>
        <div class="swatch" id="BF9000" style="background-color:#BF9000"></div>
        <div class="swatch" id="2F5496" style="background-color:#2F5496; border-color:#FFBD69"></div>
        <div class="swatch" id="538135" style="background-color:#538135"></div>
        <input type="hidden" name="color" id="color" size="10" value="2F5496" />
      </td>
    </tr>
    <tr>
      <td colspan="2" style="padding-top:20px; text-align:center"><input type="submit" name="submit" value="<?php echo $string['ok'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" onclick="window.close()" /></td>
    </tr>
  </table>
    
  </form>
</body>
</html>