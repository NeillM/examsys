<?php
/**
* Ugly Test for REXP creation
* Work in progress...
*/

require '../Connection.php';
require_once 'config.php';

function testBinary($values, $type, $options = [], $msg = '') {
	echo 'Test '.$type.' '.$msg.'<br/>';
	$cn = 'Rserve_REXP_'.$type;
	$r = new $cn();

	$tt  = strtolower((string) $type);

	if(is_subclass_of($r, 'Rserve_REXP_Vector')) {
		if( is_subclass_of($r,'Rserve_REXP_List') AND @$options['named']) {
			$r->setValues($values, TRUE);
		} else {
			$r->setValues($values);
		}
	} else {
		$r->setValue($values);
	}
	$bin = Rserve_Parser::createBinary($r);
	var_dump(Rserve_Parser::parseDebug($bin, 0));
	$r2 = Rserve_Parser::parseREXP($bin, 0);
	var_dump($r2);
	$cn2 = $r2::class;
	if( strtolower($cn2) != strtolower($cn)) {
		echo 'Differentes classes';
	} else {
		echo 'Class Type ok';
	}
}

testBinary([1,2,3], 'Integer'  );

testBinary([1.1,2.2,3.3], 'Double'  );

testBinary( [TRUE, FALSE, TRUE, NULL], 'Logical');