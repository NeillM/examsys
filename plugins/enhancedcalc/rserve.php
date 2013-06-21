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
 * The already logged in authentication class
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */


require_once('../rserve/Connection.php');


class enhancedcalc_rserve {

  protected $impliments_api_calc_version = 1;
  static protected $cnx = false;

  protected $config;
  protected $configObj;
  protected $db;


  function __construct($configObj) {
    $this->configObj = $configObj;
    $this->config = $this->configObj->getbyref('enhancedcalculation');
  }

  function calculate($useranswer, &$settings) {
    //self::$cnx=false;

    $useransweradd = array();
    //useranswer contains the variable values and the answer supplied by user
    //settings contains the formula as well as tolerances etc.

    if (is_null(self::$cnx)) {
      //connection failed
      return array(Q_MARKING_UNMARKED, array());
    }


    // if the box isnt on this timeout is ignored and is likely to be different
    if (!isset($this->config['timeout'])) {
      $timeoutarray = array('seconds' => 5, 'milliseconds' => 1);
    } else {
      $timeoutarray = array('seconds' => $this->config['timeout'], 'milliseconds' => 1);
    }

    if (self::$cnx === false) {
      try {
        self::$cnx = @new Rserve_Connection($this->config['host'], $this->config['port'], $timeoutarray);
      } catch (exception $except) {
        self::$cnx = null;

        return array(Q_MARKING_UNMARKED, array());
      }
    } else {
      //reset connection
      $result = self::$cnx->evalString('rm(list=ls(all=TRUE))');
    }

    $formula = $settings['formula'];
    $varname = array_keys($useranswer['vars']);
    $varvalue = array_values($useranswer['vars']);
    $formula = str_replace($varname, $varvalue, $formula);

    $op = self::$cnx->evalString("ANS = $formula");

    $correctanswer = self::$cnx->evalString("paste(capture.output(print((ANS))),collapse='\\n');");

    $pos = strpos($correctanswer, ' ');

    $useranswer['cans'] = substr($correctanswer, $pos + 1);

    if (isset($useranswer['uansnumb'])) {
      $uans = $useranswer['uansnumb'];
    } else {
      $uans = $useranswer['uans'];
    }

    $status = self::$cnx->evalString("ANS == $uans");
    if ($status === true) {
      //correct
      $useranswer['status']['exact'] = true;
    } else {
      $useranswer['status']['exact'] = false;
    }

    if (isset($settings['fulltol'])) {
      if (!isset($settings['fulltolneg'])) {
        $settings['fulltolneg'] = $settings['fulltol'];
        $settings['fulltolnegtyp'] = $settings['fulltoltyp'];
      }
      switch ($settings['fulltoltyp']) {
        case "%":
          $op = self::$cnx->evalString("FULLTOL = ANS * (" . $settings['fulltol'] . "/100)");
          $fulltol = self::$cnx->evalString("paste(capture.output(print((FULLTOL))),collapse='\\n');");
          $pos = strpos($fulltol, ' ');
          $useranswer['ans']['fulltol'] = substr($fulltol, $pos + 1);
          $op = self::$cnx->evalString("FULLTOLANS = ANS + FULLTOL");
          $fulltolans = self::$cnx->evalString("paste(capture.output(print((FULLTOLANS))),collapse='\\n');");
          $pos = strpos($fulltolans, ' ');
          $useranswer['ans']['fulltolans'] = substr($fulltolans, $pos + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("FULLTOLANS =  " . $settings['fulltol']);
          $fulltol = self::$cnx->evalString("paste(capture.output(print((FULLTOLANS))),collapse='\\n');");
          $pos = strpos($fulltol, ' ');
          $useranswer['ans']['fulltol'] = substr($fulltol, $pos + 1);
          $op = self::$cnx->evalString("FULLTOLANS = ANS + FULLTOL");
          $fulltolans = self::$cnx->evalString("paste(capture.output(print((FULLTOLANS))),collapse='\\n');");
          $pos = strpos($fulltolans, ' ');
          $useranswer['ans']['fulltolans'] = substr($fulltolans, $pos + 1);
          break;
        case "sf":
          $fulltolans = self::$cnx->evalString("FULLTOLANS =  signif(ANS," . $settings['fulltol'] . ")");
          $pos = strpos($fulltolans, ' ');
          $useranswer['ans']['fulltolans'] = substr($fulltolans, $pos + 1);
          break;
      }
      switch ($settings['fulltolnegtyp']) {
        case "%":
          $op = self::$cnx->evalString("FULLTOLNEG = abs(ANS * (" . $settings['fulltolneg'] . "/100))");
          $fulltolneg = self::$cnx->evalString("paste(capture.output(print((FULLTOLNEG))),collapse='\\n');");
          $neg = strpos($fulltolneg, ' ');
          $useranswer['ans']['fulltolneg'] = substr($fulltolneg, $neg + 1);
          $op = self::$cnx->evalString("FULLTOLNEGANS = ANS - FULLTOLNEG");
          $fulltolnegans = self::$cnx->evalString("paste(capture.output(print((FULLTOLNEGANS))),collapse='\\n');");
          $neg = strpos($fulltolnegans, ' ');
          $useranswer['ans']['fulltolnegans'] = substr($fulltolnegans, $neg + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("FULLTOLNEGANS =  " . $settings['fulltolneg']);
          $fulltolneg = self::$cnx->evalString("paste(capture.output(print((FULLTOLNEGANS))),collapse='\\n');");
          $neg = strpos($fulltolneg, ' ');
          $useranswer['ans']['fulltolneg'] = substr($fulltolneg, $neg + 1);
          $op = self::$cnx->evalString("FULLTOLNEGANS = ANS - FULLTOLNEG");
          $fulltolnegans = self::$cnx->evalString("paste(capture.output(print((FULLTOLNEGANS))),collapse='\\n');");
          $neg = strpos($fulltolnegans, ' ');
          $useranswer['ans']['fulltolnegans'] = substr($fulltolnegans, $neg + 1);
          break;
        case "sf":
          $fulltolnegans = self::$cnx->evalString("FULLTOLNEGANS =  signif(ANS," . $settings['fulltolneg'] . ")");
          $neg = strpos($fulltolnegans, ' ');
          $useranswer['ans']['fulltolnegans'] = substr($fulltolnegans, $neg + 1);
          break;
      }
      switch ($settings['fulltoltyp']) {
        case "sf":
          $uanssf = self::$cnx->evalString("UANSSF =  signif($uans," . $settings['fulltol'] . ")");
          $status = self::$cnx->evalString("UANSSF ==  FULLTOLANS");
          if ($status === true) {
            //correct
            $useranswer['status']['fulltol'] = true;
          } else {
            $useranswer['status']['fulltol'] = false;
          }
          break;
        case "%":
        case "#":
          //    self::$cnx->evalString("greaterequal -< function(x,y) x < y");
          $string = "FULLTOLANSNEG <= $uans";
          //   $status = self::$cnx->evalString("FULLTOLNEGANS <= $uans");
          $status = self::$cnx->evalString("FULLTOLNEGANS <= $uans");
          $status1 = self::$cnx->evalString("$uans <= FULLTOLANS");

          if ($status === true and $status1 === true) {
            //correct
            $useranswer['status']['fulltol'] = true;
          } else {
            $useranswer['status']['fulltol'] = false;
          }
          break;
      }

    }
    if (isset($settings['parttol'])) {
      if (!isset($settings['parttolneg'])) {
        $settings['parttolneg'] = $settings['parttol'];
        $settings['parttolnegtyp'] = $settings['parttoltyp'];
      }
      switch ($settings['parttoltyp']) {
        case "%":
          $op = self::$cnx->evalString("PARTTOL = ANS * (" . $settings['parttolneg'] . "/100)");
          $parttol = self::$cnx->evalString("paste(capture.output(print((PARTTOL))),collapse='\\n');");
          $pos = strpos($parttol, ' ');
          $useranswer['ans']['parttol'] = substr($parttol, $pos + 1);
          $op = self::$cnx->evalString("PARTTOLANS = ANS + PARTTOL");
          $parttolans = self::$cnx->evalString("paste(capture.output(print((PARTTOLANS))),collapse='\\n');");
          $pos = strpos($parttolans, ' ');
          $useranswer['ans']['parttolans'] = substr($parttolans, $pos + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("PARTTOLANS =  " . $settings['parttolneg']);
          $parttol = self::$cnx->evalString("paste(capture.output(print((PARTTOLANS))),collapse='\\n');");
          $pos = strpos($parttol, ' ');
          $useranswer['ans']['parttol'] = substr($parttol, $pos + 1);
          $op = self::$cnx->evalString("PARTTOLANS = ANS + PARTTOL");
          $parttolans = self::$cnx->evalString("paste(capture.output(print((PARTTOLANS))),collapse='\\n');");
          $pos = strpos($parttolans, ' ');
          $useranswer['ans']['parttolans'] = substr($parttolans, $pos + 1);
          break;
        case "sf":
          $parttolans = self::$cnx->evalString("PARTTOLANS =  signif(ANS," . $settings['parttol'] . ")");
          $pos = strpos($parttolans, ' ');
          $useranswer['ans']['parttolans'] = substr($parttolans, $pos + 1);
          break;
      }
      switch ($settings['parttolnegtyp']) {
        case "%":
          $op = self::$cnx->evalString("PARTTOLNEG = abs(ANS * (" . $settings['parttolneg'] . "/100))");
          $parttolneg = self::$cnx->evalString("paste(capture.output(print((PARTTOLNEG))),collapse='\\n');");
          $neg = strpos($parttolneg, ' ');
          $useranswer['ans']['parttolneg'] = substr($parttolneg, $neg + 1);
          $op = self::$cnx->evalString("PARTTOLNEGANS = ANS - PARTTOLNEG");
          $parttolnegans = self::$cnx->evalString("paste(capture.output(print((PARTTOLNEGANS))),collapse='\\n');");
          $neg = strpos($parttolnegans, ' ');
          $useranswer['ans']['parttolnegans'] = substr($parttolnegans, $neg + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("PARTTOLNEGANS =  " . $settings['parttolneg']);
          $parttolneg = self::$cnx->evalString("paste(capture.output(print((PARTTOLNEGANS))),collapse='\\n');");
          $neg = strpos($parttolneg, ' ');
          $useranswer['ans']['parttolneg'] = substr($parttolneg, $neg + 1);
          $op = self::$cnx->evalString("PARTTOLNEGANS = ANS - PARTTOLNEG");
          $parttolnegans = self::$cnx->evalString("paste(capture.output(print((PARTTOLNEGANS))),collapse='\\n');");
          $neg = strpos($parttolnegans, ' ');
          $useranswer['ans']['parttolnegans'] = substr($parttolnegans, $neg + 1);
          break;
        case "sf":
          $parttolnegans = self::$cnx->evalString("PARTTOLNEGANS =  signif(ANS," . $settings['parttolneg'] . ")");
          $neg = strpos($parttolnegans, ' ');
          $useranswer['ans']['parttolnegans'] = substr($parttolnegans, $neg + 1);
          break;
      }
      switch ($settings['parttoltyp']) {
        case "sf":
          $uanssf = self::$cnx->evalString("UANSSF =  signif($uans," . $settings['parttol'] . ")");
          $status = self::$cnx->evalString("UANSSF ==  PARTTOLANS");
          if ($status === true) {
            //correct
            $useranswer['status']['parttol'] = true;
          } else {
            $useranswer['status']['parttol'] = false;
          }
          break;
        case "%":
        case "#":
          $status = self::$cnx->evalString("PARTTOLNEGANS <= $uans");
          $status1 = self::$cnx->evalString("$uans <= PARTTOLANS");

          if ($status === true and $status1 === true) {
            //correct
            $useranswer['status']['parttol'] = true;
          } else {
            $useranswer['status']['parttol'] = false;
          }
          break;
      }

    }

    //dp display
    if (isset($settings['strictdp']) and $settings['strictdp'] === true and isset($settings['dp'])) {
      $strpos = strpos($uans, '.');
      $strpos1 = stripos($uans, 'e', $strpos);
      if ($strpos1 === false) {
        $strpos1 = strlen($uans);
      }
      $dps = $strpos1 - $strpos - 1;
      $useranswer['ans']['strictdps'] = $dps;
      $useranswer['ans']['strictpos'] = $strpos;
      $useranswer['ans']['strictpos1'] = $strpos1;
      $op = self::$cnx->evalString("STRICTDP = format(round($uans," . $settings['dp'] . "), nsmall = " . $settings['dp'] . ")");
      $dpans = self::$cnx->evalString("paste(capture.output(print((STRICTDP))),collapse='\\n');");
      $dpans = str_replace('"', '', $dpans);
      $pos = strpos($dpans, ' ');
      $useranswer['ans']['strictdp'] = substr($dpans, $pos + 1);
      $status = self::$cnx->evalString("$uans == " . $useranswer['ans']['strictdp']);

      if ($status === true) {
        //correct
        $useranswer['status']['strictdp'] = true;
      } else {
        $useranswer['status']['strictdp'] = false;
      }
      if ($dps == $settings['dp']) {
        //correct
        $useranswer['status']['strictdpsize'] = true;
      } else {
        $useranswer['status']['strictdpsize'] = false;
      }
    }

    if (isset($settings['dp'])) {
      $strpos = strpos($uans, '.');
      $strpos1 = stripos($uans, 'e', $strpos);
      if ($strpos1 === false) {
        $strpos1 = strlen($uans);
      }
      $dps = $strpos1 - $strpos - 1;
      $useranswer['ans']['dps'] = $dps;
      $useranswer['ans']['pos'] = $strpos;
      $useranswer['ans']['pos1'] = $strpos1;
      $op = self::$cnx->evalString("DP = format(round(ANS," . $settings['dp'] . "), nsmall = " . $settings['dp'] . ")");
      $dpans = self::$cnx->evalString("paste(capture.output(print((DP))),collapse='\\n');");
      $dpans = str_replace('"', '', $dpans);
      $pos = strpos($dpans, ' ');
      $useranswer['ans']['dp'] = substr($dpans, $pos + 1);
      $status = self::$cnx->evalString("$uans == " . $useranswer['ans']['dp']);

      if ($status === true) {
        //correct
        $useranswer['status']['dp'] = true;
      } else {
        $useranswer['status']['dp'] = false;
      }
      if ($dps == $settings['dp']) {
        //correct
        $useranswer['status']['dpsize'] = true;
      } else {
        $useranswer['status']['dpsize'] = false;
      }
    }

    if (isset($settings['strictsf']) and $settings['strictsf'] === true and isset($setttings['sf'])) {
      $op = self::$cnx->evalString("STRICTSF = signif($uans," . $settings['sf'] . ")");
      $sfans = self::$cnx->evalString("paste(capture.output(print((STRICTSF))),collapse='\\n');");
      $pos = strpos($sfans, ' ');
      $useranswer['ans']['strictsf'] = substr($sfans, $pos + 1);
      $status = self::$cnx->evalString("$uans == STRICTSF");

      if ($status === true) {
        //correct
        $useranswer['status']['strictsf'] = true;
      } else {
        $useranswer['status']['strictsf'] = false;
      }
    }
    if (isset($settings['sf'])) {
      $op = self::$cnx->evalString("SF = signif(ANS, " . $settings['sf'] . ")");
      $sfans = self::$cnx->evalString("paste(capture.output(print((SF))),collapse='\\n');");
      $pos = strpos($sfans, ' ');
      $useranswer['ans']['sf'] = substr($sfans, $pos + 1);
      $status = self::$cnx->evalString("$uans == SF");

      if ($status === true) {
        //correct
        $useranswer['status']['sf'] = true;
      } else {
        $useranswer['status']['sf'] = false;
      }
    }

    return array(true, $useranswer);

  }

}