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

    if (isset($settings['tolerance_full'])) {
      if (!isset($settings['tolerance_fullneg'])) {
        $settings['tolerance_fullneg'] = $settings['tolerance_full'];
        $settings['tolerance_fullnegtyp'] = $settings['fulltoltyp'];
      }
      switch ($settings['fulltoltyp']) {
        case "%":
          $op = self::$cnx->evalString("tolerance_full = ANS * (" . $settings['tolerance_full'] . "/100)");
          $tolerance_full = self::$cnx->evalString("paste(capture.output(print((tolerance_full))),collapse='\\n');");
          $pos = strpos($tolerance_full, ' ');
          $useranswer['ans']['tolerance_full'] = substr($tolerance_full, $pos + 1);
          $op = self::$cnx->evalString("tolerance_fullANS = ANS + tolerance_full");
          $tolerance_fullans = self::$cnx->evalString("paste(capture.output(print((tolerance_fullANS))),collapse='\\n');");
          $pos = strpos($tolerance_fullans, ' ');
          $useranswer['ans']['tolerance_fullans'] = substr($tolerance_fullans, $pos + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("tolerance_fullANS =  " . $settings['tolerance_full']);
          $tolerance_full = self::$cnx->evalString("paste(capture.output(print((tolerance_fullANS))),collapse='\\n');");
          $pos = strpos($tolerance_full, ' ');
          $useranswer['ans']['tolerance_full'] = substr($tolerance_full, $pos + 1);
          $op = self::$cnx->evalString("tolerance_fullANS = ANS + tolerance_full");
          $tolerance_fullans = self::$cnx->evalString("paste(capture.output(print((tolerance_fullANS))),collapse='\\n');");
          $pos = strpos($tolerance_fullans, ' ');
          $useranswer['ans']['tolerance_fullans'] = substr($tolerance_fullans, $pos + 1);
          break;
        case "sf":
          $tolerance_fullans = self::$cnx->evalString("tolerance_fullANS =  signif(ANS," . $settings['tolerance_full'] . ")");
          $pos = strpos($tolerance_fullans, ' ');
          $useranswer['ans']['tolerance_fullans'] = substr($tolerance_fullans, $pos + 1);
          break;
      }
      switch ($settings['tolerance_fullnegtyp']) {
        case "%":
          $op = self::$cnx->evalString("tolerance_fullNEG = abs(ANS * (" . $settings['tolerance_fullneg'] . "/100))");
          $tolerance_fullneg = self::$cnx->evalString("paste(capture.output(print((tolerance_fullNEG))),collapse='\\n');");
          $neg = strpos($tolerance_fullneg, ' ');
          $useranswer['ans']['tolerance_fullneg'] = substr($tolerance_fullneg, $neg + 1);
          $op = self::$cnx->evalString("tolerance_fullNEGANS = ANS - tolerance_fullNEG");
          $tolerance_fullnegans = self::$cnx->evalString("paste(capture.output(print((tolerance_fullNEGANS))),collapse='\\n');");
          $neg = strpos($tolerance_fullnegans, ' ');
          $useranswer['ans']['tolerance_fullnegans'] = substr($tolerance_fullnegans, $neg + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("tolerance_fullNEGANS =  " . $settings['tolerance_fullneg']);
          $tolerance_fullneg = self::$cnx->evalString("paste(capture.output(print((tolerance_fullNEGANS))),collapse='\\n');");
          $neg = strpos($tolerance_fullneg, ' ');
          $useranswer['ans']['tolerance_fullneg'] = substr($tolerance_fullneg, $neg + 1);
          $op = self::$cnx->evalString("tolerance_fullNEGANS = ANS - tolerance_fullNEG");
          $tolerance_fullnegans = self::$cnx->evalString("paste(capture.output(print((tolerance_fullNEGANS))),collapse='\\n');");
          $neg = strpos($tolerance_fullnegans, ' ');
          $useranswer['ans']['tolerance_fullnegans'] = substr($tolerance_fullnegans, $neg + 1);
          break;
        case "sf":
          $tolerance_fullnegans = self::$cnx->evalString("tolerance_fullNEGANS =  signif(ANS," . $settings['tolerance_fullneg'] . ")");
          $neg = strpos($tolerance_fullnegans, ' ');
          $useranswer['ans']['tolerance_fullnegans'] = substr($tolerance_fullnegans, $neg + 1);
          break;
      }
      switch ($settings['fulltoltyp']) {
        case "sf":
          $uanssf = self::$cnx->evalString("UANSSF =  signif($uans," . $settings['tolerance_full'] . ")");
          $status = self::$cnx->evalString("UANSSF ==  tolerance_fullANS");
          if ($status === true) {
            //correct
            $useranswer['status']['tolerance_full'] = true;
          } else {
            $useranswer['status']['tolerance_full'] = false;
          }
          break;
        case "%":
        case "#":
          //    self::$cnx->evalString("greaterequal -< function(x,y) x < y");
          $string = "tolerance_fullANSNEG <= $uans";
          //   $status = self::$cnx->evalString("tolerance_fullNEGANS <= $uans");
          $status = self::$cnx->evalString("tolerance_fullNEGANS <= $uans");
          $status1 = self::$cnx->evalString("$uans <= tolerance_fullANS");

          if ($status === true and $status1 === true) {
            //correct
            $useranswer['status']['tolerance_full'] = true;
          } else {
            $useranswer['status']['tolerance_full'] = false;
          }
          break;
      }

    }
    if (isset($settings['tolerance_partial'])) {
      if (!isset($settings['tolerance_partialneg'])) {
        $settings['tolerance_partialneg'] = $settings['tolerance_partial'];
        $settings['tolerance_partialnegtyp'] = $settings['parttoltyp'];
      }
      switch ($settings['parttoltyp']) {
        case "%":
          $op = self::$cnx->evalString("tolerance_partial = ANS * (" . $settings['tolerance_partialneg'] . "/100)");
          $tolerance_partial = self::$cnx->evalString("paste(capture.output(print((tolerance_partial))),collapse='\\n');");
          $pos = strpos($tolerance_partial, ' ');
          $useranswer['ans']['tolerance_partial'] = substr($tolerance_partial, $pos + 1);
          $op = self::$cnx->evalString("tolerance_partialANS = ANS + tolerance_partial");
          $tolerance_partialans = self::$cnx->evalString("paste(capture.output(print((tolerance_partialANS))),collapse='\\n');");
          $pos = strpos($tolerance_partialans, ' ');
          $useranswer['ans']['tolerance_partialans'] = substr($tolerance_partialans, $pos + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("tolerance_partialANS =  " . $settings['tolerance_partialneg']);
          $tolerance_partial = self::$cnx->evalString("paste(capture.output(print((tolerance_partialANS))),collapse='\\n');");
          $pos = strpos($tolerance_partial, ' ');
          $useranswer['ans']['tolerance_partial'] = substr($tolerance_partial, $pos + 1);
          $op = self::$cnx->evalString("tolerance_partialANS = ANS + tolerance_partial");
          $tolerance_partialans = self::$cnx->evalString("paste(capture.output(print((tolerance_partialANS))),collapse='\\n');");
          $pos = strpos($tolerance_partialans, ' ');
          $useranswer['ans']['tolerance_partialans'] = substr($tolerance_partialans, $pos + 1);
          break;
        case "sf":
          $tolerance_partialans = self::$cnx->evalString("tolerance_partialANS =  signif(ANS," . $settings['tolerance_partial'] . ")");
          $pos = strpos($tolerance_partialans, ' ');
          $useranswer['ans']['tolerance_partialans'] = substr($tolerance_partialans, $pos + 1);
          break;
      }
      switch ($settings['tolerance_partialnegtyp']) {
        case "%":
          $op = self::$cnx->evalString("tolerance_partialNEG = abs(ANS * (" . $settings['tolerance_partialneg'] . "/100))");
          $tolerance_partialneg = self::$cnx->evalString("paste(capture.output(print((tolerance_partialNEG))),collapse='\\n');");
          $neg = strpos($tolerance_partialneg, ' ');
          $useranswer['ans']['tolerance_partialneg'] = substr($tolerance_partialneg, $neg + 1);
          $op = self::$cnx->evalString("tolerance_partialNEGANS = ANS - tolerance_partialNEG");
          $tolerance_partialnegans = self::$cnx->evalString("paste(capture.output(print((tolerance_partialNEGANS))),collapse='\\n');");
          $neg = strpos($tolerance_partialnegans, ' ');
          $useranswer['ans']['tolerance_partialnegans'] = substr($tolerance_partialnegans, $neg + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("tolerance_partialNEGANS =  " . $settings['tolerance_partialneg']);
          $tolerance_partialneg = self::$cnx->evalString("paste(capture.output(print((tolerance_partialNEGANS))),collapse='\\n');");
          $neg = strpos($tolerance_partialneg, ' ');
          $useranswer['ans']['tolerance_partialneg'] = substr($tolerance_partialneg, $neg + 1);
          $op = self::$cnx->evalString("tolerance_partialNEGANS = ANS - tolerance_partialNEG");
          $tolerance_partialnegans = self::$cnx->evalString("paste(capture.output(print((tolerance_partialNEGANS))),collapse='\\n');");
          $neg = strpos($tolerance_partialnegans, ' ');
          $useranswer['ans']['tolerance_partialnegans'] = substr($tolerance_partialnegans, $neg + 1);
          break;
        case "sf":
          $tolerance_partialnegans = self::$cnx->evalString("tolerance_partialNEGANS =  signif(ANS," . $settings['tolerance_partialneg'] . ")");
          $neg = strpos($tolerance_partialnegans, ' ');
          $useranswer['ans']['tolerance_partialnegans'] = substr($tolerance_partialnegans, $neg + 1);
          break;
      }
      switch ($settings['parttoltyp']) {
        case "sf":
          $uanssf = self::$cnx->evalString("UANSSF =  signif($uans," . $settings['tolerance_partial'] . ")");
          $status = self::$cnx->evalString("UANSSF ==  tolerance_partialANS");
          if ($status === true) {
            //correct
            $useranswer['status']['tolerance_partial'] = true;
          } else {
            $useranswer['status']['tolerance_partial'] = false;
          }
          break;
        case "%":
        case "#":
          $status = self::$cnx->evalString("tolerance_partialNEGANS <= $uans");
          $status1 = self::$cnx->evalString("$uans <= tolerance_partialANS");

          if ($status === true and $status1 === true) {
            //correct
            $useranswer['status']['tolerance_partial'] = true;
          } else {
            $useranswer['status']['tolerance_partial'] = false;
          }
          break;
      }

    }

    //dp display
    if (isset($settings['strictdisplay']) and $settings['strictdisplay'] === true and isset($settings['dp'])) {
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
      $op = self::$cnx->evalString("DP = format(round(" . $uans ."," . $settings['dp'] . "), nsmall = " . $settings['dp'] . ")");
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

    $correctdataanswrs = array(&$useranswer['cans'], &$useranswer['ans']['tolerance_full'], &$useranswer['ans']['tolerance_fullans'], &$useranswer['ans']['tolerance_fullneg'], &$useranswer['ans']['tolerance_fullnegans'], &$useranswer['ans']['tolerance_partial'], &$useranswer['ans']['tolerance_partialans'], &$useranswer['ans']['tolerance_partialneg'], &$useranswer['ans']['tolerance_partialnegans']);


    // if dp set then make all answers in that formating
    if (isset($settings['dp'])) {
      // saved answers need to be corrected to correct dp
      if (isset($settings['strictzeros']) and $settings['strictzeros'] === true) {
        //trailing 0s needed
        foreach ($correctdataanswrs as $key => $value) {
          $op = self::$cnx->evalString("DPDISP = format(round(" . $value . "," . $settings['dp'] . "), nsmall = " . $settings['dp'] . ")");
          $dpform = self::$cnx->evalString("paste(capture.output(print((DPDISP))),collapse='\\n');");
          $dpform = str_replace('"', '', $dpform);
          $pos = strpos($dpform, ' ');
          $correctdataanswrs[$key] = substr($dpform, $pos + 1);
        }
      } else {
        //no trailing 0s needed
        foreach ($correctdataanswrs as $key => $value) {
          $op = self::$cnx->evalString("DPDISP = round(" . $value . "," . $settings['dp'] . ")");
          $dpform = self::$cnx->evalString("paste(capture.output(print((DPDISP))),collapse='\\n');");
          $dpform = str_replace('"', '', $dpform);
          $pos = strpos($dpform, ' ');
          $correctdataanswrs[$key] = substr($dpform, $pos + 1);
        }
      }
    }

    if (isset($settings['sf'])) {
      // saved answers need to be corrected to correct sf

        foreach ($correctdataanswrs as $key => $value) {
          $op = self::$cnx->evalString("SFDISP = signif(" . $value . "," . $settings['sf'] . ")");
          $sfform = self::$cnx->evalString("paste(capture.output(print((SFDISP))),collapse='\\n');");
          $sfform = str_replace('"', '', $sfform);
          $pos = strpos($sfform, ' ');
          $correctdataanswrs[$key] = substr($sfform, $pos + 1);

      }
    }


    if (isset($settings['strictdisplay']) and $settings['strictdisplay'] === true and isset($setttings['sf'])) {
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