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
}
?>
