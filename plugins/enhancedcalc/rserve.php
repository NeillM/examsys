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

    if(is_null(self::$cnx)) {
      //connection failed
      return array(Q_MARKING_UNMARKED,array());
    }


    // if the box isnt on this timeout is ignored and is likely to be different
    if(!isset($this->config['timeout'])) {
      $timeoutarray=array('seconds'=>5,'milliseconds'=>1);
    } else {
      $timeoutarray=array('seconds'=>$this->config['timeout'],'milliseconds'=>1);
    }

    if (self::$cnx===false) {
      try {
        self::$cnx = @new Rserve_Connection($this->config['host'], $this->config['port'],$timeoutarray);
      } catch (exception $except) {
        self::$cnx=null;
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
    $uans = $useranswer['uans'];


    $status = self::$cnx->evalString("ANS == $uans");
    if ($status === true) {
      //correct
      $useranswer['exactstatus'] = Q_MARKING_EXACT;
    } else {
      $useranswer['exactstatus'] = Q_MARKING_WRONG;
    }

    if (isset($settings['fulltol'])) {
      if (!isset($settings['negfulltol'])) {
        $settings['negfulltol'] = $settings['fulltol'];
        $settings['negfulltoltyp'] = $settings['fulltoltyp'];
      }
      switch ($settings['fulltoltyp']) {
        case "%":
          $op = self::$cnx->evalString("FULLTOL = ANS * (" . $settings['fulltol'] . "/100)");
          $fulltol = self::$cnx->evalString("paste(capture.output(print((FULLTOL))),collapse='\\n');");
          $pos = strpos($fulltol, ' ');
          $useranswer['fulltol'] = substr($fulltol, $pos + 1);
          $op = self::$cnx->evalString("FULLTOLANS = ANS + FULLTOL");
          $fulltolans = self::$cnx->evalString("paste(capture.output(print((FULLTOLANS))),collapse='\\n');");
          $pos = strpos($fulltolans, ' ');
          $useranswer['fulltolans'] = substr($fulltolans, $pos + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("FULLTOLANS =  " . $settings['fulltol']);
          $fulltol = self::$cnx->evalString("paste(capture.output(print((FULLTOLANS))),collapse='\\n');");
          $pos = strpos($fulltol, ' ');
          $useranswer['fulltol'] = substr($fulltol, $pos + 1);
          $op = self::$cnx->evalString("FULLTOLANS = ANS + FULLTOL");
          $fulltolans = self::$cnx->evalString("paste(capture.output(print((FULLTOLANS))),collapse='\\n');");
          $pos = strpos($fulltolans, ' ');
          $useranswer['fulltolans'] = substr($fulltolans, $pos + 1);
          break;
        case "sf":
          $fulltolans = self::$cnx->evalString("FULLTOLANS =  signif(ANS," . $settings['fulltol'] . ")");
          $pos = strpos($fulltolans, ' ');
          $useranswer['fulltolans'] = substr($fulltolans, $pos + 1);
          break;
      }
      switch ($settings['fulltolnegtyp']) {
        case "%":
          $op = self::$cnx->evalString("FULLTOLNEG = abs(ANS * (" . $settings['fulltolneg'] . "/100))");
          $fulltolneg = self::$cnx->evalString("paste(capture.output(print((FULLTOLNEG))),collapse='\\n');");
          $neg = strpos($fulltolneg, ' ');
          $useranswer['fulltolnegans'] = substr($fulltolneg, $neg + 1);
          $op = self::$cnx->evalString("FULLTOLNEGANS = ANS - FULLTOLNEG");
          $fulltolnegans = self::$cnx->evalString("paste(capture.output(print((FULLTOLNEGANS))),collapse='\\n');");
          $neg = strpos($fulltolnegans, ' ');
          $useranswer['fulltolneg'] = substr($fulltolnegans, $neg + 1);
          break;
        case "#":
          $op = self::$cnx->evalString("FULLTOLNEGANS =  " . $settings['fulltolneg']);
          $fulltolneg = self::$cnx->evalString("paste(capture.output(print((FULLTOLNEGANS))),collapse='\\n');");
          $neg = strpos($fulltolneg, ' ');
          $useranswer['fulltolnegans'] = substr($fulltolneg, $neg + 1);
          $op = self::$cnx->evalString("FULLTOLNEGANS = ANS - FULLTOLNEG");
          $fulltolnegans = self::$cnx->evalString("paste(capture.output(print((FULLTOLNEGANS))),collapse='\\n');");
          $neg = strpos($fulltolnegans, ' ');
          $useranswer['fulltolneg'] = substr($fulltolnegans, $neg + 1);
          break;
        case "sf":
          $fulltolnegans = self::$cnx->evalString("FULLTOLNEGANS =  signif(ANS," . $settings['fulltolneg'] . ")");
          $neg = strpos($fulltolnegans, ' ');
          $useranswer['fulltolnegans'] = substr($fulltolnegans, $neg + 1);
          break;
      }
      switch ($settings['fulltoltyp']) {
        case "sf":
          $uanssf = self::$cnx->evalString("UANSSF =  signif($uans," . $settings['fulltol'] . ")");
          $status = self::$cnx->evalString("UANSSF ==  FULLTOLANS");
          if ($status === true) {
            //correct
            $useranswer['fulltolstatus'] = Q_MARKING_EXACT;
          } else {
            $useranswer['fulltolstatus'] = Q_MARKING_WRONG;
          }
          break;
        case "%":
        case "#":
          $status = self::$cnx->evalString("FULLTOLANSNEG <= $uans");
          $status1 = self::$cnx->evalString("$uans <= FULLTOLANS");

          if ($status === true and $status1 === true) {
            //correct
            $useranswer['fulltolstatus'] = Q_MARKING_EXACT;
          } else {
            $useranswer['fulltolstatus'] = Q_MARKING_WRONG;
          }
          break;
      }
      //partial tolerance




    }

    return array(Q_MARKING_WRONG, $useranswer);
  }

}