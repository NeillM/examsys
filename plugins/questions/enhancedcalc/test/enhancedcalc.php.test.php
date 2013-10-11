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
* Rogō caculation question unit tests.
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

global $cfg_web_root;
global $cfg_web_root;
require $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';

class EnhancedCalcTests extends \Enhance\TestFixture
{

  private $target;    

  // SetUp
  public function setUp() {
    $confobj = Config::get_instance();
    $this->target = \Enhance\Core::getCodeCoverageWrapper('enhancedcalc',array($confobj));
  }

  // TearDown
  public function tearDown() {
  
  }
  
  public function test_build_formula_by_units() {
      $answers = array(
                        array('formula'=>'$A+$B', 'units'=>'g')
                       );
      $res = $this->target->build_formula_by_units($answers);
      Enhance\Assert::areIdentical('$A+$B', $res['g']);
      
      $answers = array(
                        array('formula'=>'$A+$B', 'units'=>'g'),
                        array('formula'=>'($A+$B)/1000', 'units'=>'kg')
                       );
      $res = $this->target->build_formula_by_units($answers);
      Enhance\Assert::areIdentical('$A+$B', $res['g']);
      Enhance\Assert::areIdentical('($A+$B)/1000', $res['kg']);
      
      
      $answers = array(
                        array('formula'=>'$A+$B', 'units'=>'g,G'),
                        array('formula'=>'($A+$B)/1000', 'units'=>'kg,KG'),
                        array('formula'=>'($A+$B)/100000', 'units'=>'TON')
                       );
      $res = $this->target->build_formula_by_units($answers);
      Enhance\Assert::areIdentical('$A+$B', $res['g']);
      Enhance\Assert::areIdentical('$A+$B', $res['G']);
      Enhance\Assert::areIdentical('($A+$B)/1000', $res['kg']);
      Enhance\Assert::areIdentical('($A+$B)/1000', $res['KG']);
      Enhance\Assert::areIdentical('($A+$B)/100000', $res['TON']);
  }

  public function test_split_numb_from_unit() {
    $indata=array();
    // array(settings-show_units,useranswer['uansunit'],input,output1,output2)

    //basic set of maths
    $indata[] = array( false, '', '1', '1', '' );
    $indata[] = array( false, '', '-1', '-1', '' );
    $indata[] = array( false, '', '1e1', '1e1', '' );
    $indata[] = array( false, '', '1e-1', '1e-1', '' );
    $indata[] = array( false, '', '-1e1', '-1e1', '' );
    $indata[] = array( false, '', '-1e-1', '-1e-1', '' );


    $indata[] = array( false, '', '1.0', '1.0', '' );
    $indata[] = array( false, '', '-1.0', '-1.0', '' );
    $indata[] = array( false, '', '1.0e1', '1.0e1', '' );
    $indata[] = array( false, '', '1.0e-1', '1.0e-1', '' );
    $indata[] = array( false, '', '-1.0e1', '-1.0e1', '' );
    $indata[] = array( false, '', '-1.0e-1', '-1.0e-1', '' );


    $indata[] = array( false, '', '16541243456464808416531310561351456.0', '16541243456464808416531310561351456.0', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0', '-16541243456464808416531310561351456.0', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.0e1', '16541243456464808416531310561351456.0e1', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.0e-1', '16541243456464808416531310561351456.0e-1', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0e1', '-16541243456464808416531310561351456.0e1', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0e-1', '-16541243456464808416531310561351456.0e-1', '' );


    $indata[] = array( false, '', '16541243456464808416531310561351456.0', '16541243456464808416531310561351456.0', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0', '-16541243456464808416531310561351456.0', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.0e1234654987016', '16541243456464808416531310561351456.0e1234654987016', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.0e-1234654987016', '16541243456464808416531310561351456.0e-1234654987016', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0e1234654987016', '-16541243456464808416531310561351456.0e1234654987016', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0e-1234654987016', '-16541243456464808416531310561351456.0e-1234654987016', '' );


    $indata[] = array( false, '', '16541243456464808416531310561351456.0124367895', '16541243456464808416531310561351456.0124367895', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0124367895', '-16541243456464808416531310561351456.0124367895', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.0124367895e1234654987016', '16541243456464808416531310561351456.0124367895e1234654987016', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.0124367895e-1234654987016', '16541243456464808416531310561351456.0124367895e-1234654987016', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0124367895e1234654987016', '-16541243456464808416531310561351456.0124367895e1234654987016', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0124367895e-1234654987016', '-16541243456464808416531310561351456.0124367895e-1234654987016', '' );


    // should it be not supporting decimals on e ( i think so)
    $indata[] = array( false, '', '16541243456464808416531310561351456.0124367895', '16541243456464808416531310561351456.0124367895', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0124367895', '-16541243456464808416531310561351456.0124367895', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.0124367895e1234654987016.1230654987', '16541243456464808416531310561351456.0124367895e1234654987016', '.1230654987' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.0124367895e-1234654987016.1230654987', '16541243456464808416531310561351456.0124367895e-1234654987016', '.1230654987' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0124367895e1234654987016.1230654987', '-16541243456464808416531310561351456.0124367895e1234654987016', '.1230654987' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.0124367895e-1234654987016.1230654987', '-16541243456464808416531310561351456.0124367895e-1234654987016', '.1230654987' );

    $indata[] = array( false, '', '16541243456464808416531310561351456.13214564568151456025103213245646598798', '16541243456464808416531310561351456.13214564568151456025103213245646598798', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.13214564568151456025103213245646598798', '-16541243456464808416531310561351456.13214564568151456025103213245646598798', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.13214564568151456025103213245646598798e1', '16541243456464808416531310561351456.13214564568151456025103213245646598798e1', '' );
    $indata[] = array( false, '', '16541243456464808416531310561351456.13214564568151456025103213245646598798e-1', '16541243456464808416531310561351456.13214564568151456025103213245646598798e-1', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.13214564568151456025103213245646598798e1', '-16541243456464808416531310561351456.13214564568151456025103213245646598798e1', '' );
    $indata[] = array( false, '', '-16541243456464808416531310561351456.13214564568151456025103213245646598798e-1', '-16541243456464808416531310561351456.13214564568151456025103213245646598798e-1', '' );


    foreach ($indata as $data) {
      $settingsdata['show_units'] = $data[0];
      $this->target->set_settings($settingsdata);
      if ($data[1] != '') {
        $useranswerdata['uansunit'] = $data[1];
        $this->target->set_useranswer($useranswerdata);
      }
      $returned = $this->target->split_numb_from_unit($data[2]);
      Enhance\Assert::areIdentical($data[3], $returned[0]);
      Enhance\Assert::areIdentical($data[4], $returned[1]);
    }
  }

  private function test_tsgd() {
    Enhance\Assert::areIdentical('A6S7','AS');
    return "AS";
  }
}
?>
