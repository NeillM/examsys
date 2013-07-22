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
 * R based maths functions  for calculation  questions
 *
 * @author Simon Atack, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once('../rserve/Connection.php');

class enhancedcalc_rserve {

  protected $impliments_api_calc_version = 1;
  static protected $cnx = false;

  protected $config;
  protected $db;
  
  public $error = false;
  public $error_msg = '';

  function __construct($configObj) {
    $this->config = $configObj->getbyref('enhancedcalculation');
  }
  
  function connect() {
   
    $this->reset_error();
    
    if (is_null(self::$cnx)) {
      //connection failed
      $this->set_error("Can Not Connect"); 
      return false;
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
        $this->set_error("Can Not Connect");
        return false;
      }
      return true;
    } else {
      //We are connected
      return true;
    }
  }
  
  function caculate_correct_ans($vars,$formula) {
    
    if(!$this->connect()) {
      return false;
    }
    
    $varname = array_keys($vars);
    $varvalue = array_values($vars);
    $formula_vars_subed = str_replace($varname, $varvalue, $formula);
    
    //old caculation fomula use pow() - define a function in R for backward compatibility
    $this->evalString("POW <- pow <- function(a,b) { return(a^b) } ");
    
    $correctanswer = $this->evalString($formula_vars_subed);
   
    return $correctanswer;
  }
  
  function is_useranswer_correct($useranswer, $correctanswer) {
    
    $status = $this->evalString("$correctanswer == $useranswer");
      
    if ($status === true) {
      return true;
    } else {
      return false;
    }
    
  }
  
  function caculate_tolerance_percent($correctanswer,$percentage) {
   
    $tolerance_full = $this->evalString("$correctanswer * (" . $percentage . "/100)");
    $res['tolerance'] = $tolerance_full;

    $tolerance_fullans = $this->evalString("$correctanswer + $tolerance_full");
    $res['toleranceans'] = $tolerance_fullans;

    $tolerance_fullansneg = $this->evalString("$correctanswer - $tolerance_full");
    $res['tolerance_ansneg'] = $tolerance_fullansneg;

    return $res;
  }
  
  function caculate_tolerance_absolute($correctanswer,$value) {
    
    $res['tolerance'] = $value;
    
    $tolerance_fullans = $this->evalString("$correctanswer + $value");
    $res['tolerance_ans'] = $tolerance_fullans;
    
    $tolerance_fullansneg = $this->evalString("$correctanswer - $value");
    $res['tolerance_ansneg'] = $tolerance_fullansneg;
  
    return $res;
  }
  
  /*function caculate_tolerance_significant_figures($correctanswer,$sf) {
    
    $res['tolerance'] = $sf;
    
    $tolerance_fullans = $this->evalString("signif($correctanswer, $sf)");
    $res['tolerance_ans'] = $tolerance_fullans;
    
    $tolerance_fullnegans = $this->evalString("signif($tolerance_fullans, $sf)");
    $res['tolerance_negans'] = $tolerance_fullnegans;
    
    return $res;
  }*/
  
  function is_useranswer_within_tolerance($useranswer, $min, $max) {
        
    $status = $this->evalString("$useranswer <= $max");
    $status1 = $this->evalString("$useranswer >= $min");
    
    if ($status === true and $status1 === true) {
      //correct
      return true;
    } else {
      return false;
    }
  }
  
  function is_useranswer_within_tolerance_significant_figures($useranswer, $sf, $tolerance_fullANS) {
    
    $uanssf = $this->evalString("signif($useranswer," . $sf . ")");
    $status = $this->evalString("$uanssf ==  $tolerance_fullANS");
    if ($status === true) {
      //correct
      $useranswer['status']['tolerance_full'] = true;
    } else {
      $useranswer['status']['tolerance_full'] = false;
    }
  }
  
  function is_useranswer_correct_decimal_places($useranswer, $dp) {
    $dpans =  $this->evalString("format(round($useranswer," . $dp . "), nsmall = " . $dp . ")");
    $status = $this->evalString("$useranswer == $dpans");

    if ($status === true) {
      return true;
    } else {
      return false;
    }
  }
  
  function is_useranswer_correct_decimal_places_strictzeros($useranswer, $dp) {
    
    $status = $this->is_useranswer_correct_decimal_places($useranswer, $dp);
    
    if (!$status) {
      return false;
    }
    
    $strpos = strpos($useranswer, '.');
    $strpos1 = stripos($useranswer, 'e', $strpos);
    if ($strpos1 === false) {
      $strpos1 = strlen($useranswer);
    }
    
    $dps = $strpos1 - $strpos - 1;
    //$useranswer['ans']['strictdps'] = $dps;
    //$useranswer['ans']['strictpos'] = $strpos;
    //$useranswer['ans']['strictpos1'] = $strpos1;

    if ($dps == $dp) {
      return true;
    } else {
      return false;
    }
    
  }
  
  function format_number_dp($num,$dp) {
    return $this->evalString("round(" . $num . "," . $dp . ")");
  }
  
  function format_number_dp_strict_zeros($num,$dp) {
    
    return $this->evalString("format(round(" . $num . "," . $dp . "), nsmall = " . $dp . ")");
  }
  
  function format_number_sf($num,$sf) {
    return $this->evalString("signif(" . $num . "," . $sf . ")");
  }
  
  private function evalString($val) {
    if(!$this->connect()) {
      return false;
    }
    return $this->extract_value(self::$cnx->evalString("paste(capture.output(print((" . $val . "))),collapse='\\n');"));
  }
  
  private function extract_value($R_rreturn_string) {
    
    if($R_rreturn_string == '[1] TRUE') {
      return true;
    }
    
    if($R_rreturn_string == '[1] FALSE') {
      return false;
    }
    
    $R_rreturn_string = str_replace('"', '', $R_rreturn_string);
    $pos = strpos($R_rreturn_string, ' ');
    return substr($R_rreturn_string, $pos + 1);
  }

  private function set_error($msg) {
    $this->error = true;
    $this->mesage = $msg;
  }
  
  private function reset_error() {
    $this->error = false;
    $this->mesage = '';
  }
}