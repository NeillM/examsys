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

require_once('rserve/Connection.php');

class enhancedcalc_rserve {

  protected $impliments_api_calc_version = 1;
  static protected $cnx = false;

  protected $config;
  protected $toStrDefined;
  protected $powDefined;
  
  public $error = false;
  public $error_msg = '';

  function __construct($config) {
    $this->config = $config;
    $this->toStrDefined = false;
    $this->powDefined = false;
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
  
  function calculate_correct_ans($vars,$formula) {
    
    if(!$this->connect()) {
      return false;
    }
    
    $varname = array_keys($vars);
    $varvalue = array_values($vars);
    $formula_vars_subed = str_replace($varname, $varvalue, $formula);
    
    //old caculation fomula use pow() - define a function in R for backward compatibility
    if (stripos($formula,'pow(') !== true and $this->powDefined === false) {
      self::$cnx->evalString("POW <- pow <- function(a,b) { return(a^b) }");
      $this->powDefined = true;
    }
    
    if($this->toStrDefined === false) {
      self::$cnx->evalString("toStr <- function(V) { return(paste(capture.output(print(V)),collapse='\\n')) }");
      $this->toStrDefined = true;
    }
    
    $correctanswer = $this->evalString($formula_vars_subed);
   
    return $correctanswer;
  }
  
  function is_useranswer_correct($useranswer, $correctanswer) {
    
    if($useranswer == '') {
      return false;
    }
    
    try {
      $status = $this->evalString("$correctanswer == $useranswer");
    } catch(Exception $e) {
      //there is an error it cant be correct
      return false;
    }
    if ($status === true) {
      return true;
    } else {
      return false;
    }
    
  }
  
  function distance_from_correct_answer($useranswer, $correctanswer) {
    
    if($useranswer == '') {
      return 'ERROR';
    }
    
    try {
       $res = $this->evalString("(abs($useranswer - $correctanswer)/$correctanswer) * 100");
    } catch(Exception $e) {
      //there is an error it cant be correct
      return 'ERROR';
    }
      
    return $res;
  }
  
  function calculate_tolerance_percent($correctanswer,$percentage) {
    $cmd[] = "$correctanswer * (" . $percentage . "/100)";
    $cmd[] = "$correctanswer + ($correctanswer * (" . $percentage . "/100))";
    $cmd[] = "$correctanswer - ($correctanswer * (" . $percentage . "/100))";
    
    $result = $this->evalStringMulti($cmd);
    $res['tolerance'] = $result[0];
    $res['tolerance_ans'] = $result[1];
    $res['tolerance_ansneg'] = $result[2];

    return $res;
  }
  
  function calculate_tolerance_absolute($correctanswer,$value) {
    
    $cmd[] = "$correctanswer + $value";
    $cmd[] = "$correctanswer - $value";

    $result = $this->evalStringMulti($cmd);

    $res['tolerance'] = $value;
    $res['tolerance_ans'] = $result[0];
    $res['tolerance_ansneg'] = $result[1];
  
    return $res;
  }
  
  function is_useranswer_within_tolerance($useranswer, $min, $max) {
    
    if($useranswer == '') {
      return false;
    }
    
    try {
       $status = $this->evalString("$useranswer <= $max & $useranswer >= $min");
    } catch(Exception $e) {
      //there is an error it cant be correct
      return false;
    }
    
    
    if ($status === true) {
      //correct
      return true;
    } else {
      return false;
    }
  }
  
  function is_useranswer_within_significant_figures($useranswer, $sf) {
    
    if($useranswer == '') {
      return false;
    }
    
    $status = $this->evalString("signif($useranswer," . $sf . ") ==  $useranswer");
    if ($status === true) {
      //correct
      return true;
    } else {
      return false;
    }
  }
  
  function is_useranswer_correct_decimal_places($useranswer, $dp) {
    
    if($useranswer == '') {
      return false;
    }
    
    $status = $this->evalString("round($useranswer," . $dp . ") == $useranswer");
    if ($status === true) {
      return true;
    } else {
      return false;
    }
  }
  
  function is_useranswer_correct_decimal_places_strictzeros($useranswer, $dp) {
    
    if($useranswer == '') {
      return false;
    }
    
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
    return $this->extract_value(self::$cnx->evalString("toStr(" . $val . ")"));
  }
  
  private function evalStringMulti($val) {
    if(!$this->connect()) {
      return false;
    }
    $cmd = 'c(';
    foreach($val as $v) {
      $cmd .= "toStr(" . $v . "),";
    }
    $cmd = rtrim($cmd, ",");
    $cmd .= ')';
    return $this->extract_value(self::$cnx->evalString($cmd));
  }
  
  private function extract_value($R_rreturn) {
    
    if(!is_array($R_rreturn)) {
      $R_rreturn = explode("\n",$R_rreturn);
    }
    
    $ret = array();
    foreach($R_rreturn as $key => $val) {
      $val = trim($val);
      if($val == '') {
        continue;
      } 
      if($val == '[1] TRUE') {
        $ret[] = true;
      } else if($val == '[1] FALSE') {
        $ret[] =  false;
      } else {
        $val = str_replace('"', '', $val);
        $pos = strpos($val, ' ');
        $ret[] = substr($val, $pos + 1);
      }
    }
    
    if(count($ret) == 1) {
      return $ret[0];
    } else {
      return $ret;
    }
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