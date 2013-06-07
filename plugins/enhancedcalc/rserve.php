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
  static protected $cnx;

  protected $config;
  protected $configObj;
  protected $db;


  function __construct($configObj) {
    $this->configObj = $configObj;
    $this->config = $this->configObj->getbyref('enhancedcalculation');
  }

  function calculate(&$useranswer, &$settings) {

    //useranswer contains the variable values and the answer supplied by user
    //settings contains the formula as well as tolerances etc.

    if (!isset($this->cnx)) {
      $this->cnx = new Rserve_Connection($this->config['host'], $this->config['port']);
    } else {
      //reset connection
      $result = $this->cnx->evalString('rm(list=ls(all=TRUE))');
    }


    $formula = $settings['formula'];


    $varname = array_keys($useranswer['vars']);
    $varvalue = array_values($useranswer['vars']);
    $formula = str_replace($varname, $varvalue, $formula);

    $op = $this->cnx->evalString("ANS = $formula");

    $correctanswer = $this->cnx->evalString("paste(capture.output(print((ANS))),collapse='\\n');");

    $pos = strpos($correctanswer, ' ');

    $useranswer['cans'] = substr($correctanswer, $pos + 1);
    $uans = $useranswer['uans'];


    if ($this->cnx->evalString("ANS == $uans") === true) {
      //correct
      return Q_MARKING_EXACT;
    }

    return Q_MARKING_WRONG;
  }

}