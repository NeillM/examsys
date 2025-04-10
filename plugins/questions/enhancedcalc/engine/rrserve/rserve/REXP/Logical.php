<?php
/**
* Rserve client for PHP
* Supports Rserve protocol 0103 only (used by Rserve 0.5 and higher)
* $Revision$
* @author Cl�ment TURBELIN
* Developped using code from Simple Rserve client for PHP by Simon Urbanek Licensed under GPL v2 or at your option v3
* This code is inspired from Java client for Rserve (Rserve package v0.6.2) developped by Simon Urbanek(c)
*/

/**
* R Double vector
*/
class Rserve_REXP_Logical extends Rserve_REXP_Vector {
	#[\Override]
	public function isInteger() { return true; }
	#[\Override]
	public function isNumeric() { return true; }
	#[\Override]
	public function isLogical() { return true; }
	
	#[\Override]
	public function getType() {
		return Rserve_Parser::XT_ARRAY_BOOL;
	}
	
}