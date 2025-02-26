<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

namespace plugins\questions\enhancedcalc\engine\rrserve;

use EnhancedCalc;
use Exception;
use Rserve_Connection;

require_once(dirname(__DIR__) . '/rserve/Connection.php');

/**
 *
 * R based maths functions for Calculation questions
 *
 * @author Simon Atack, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */
class Engine extends \plugins\questions\enhancedcalc\Engine
{
    /** @var Rserve_Connection|false */
    protected static $cnx = false;

    public function __construct($config)
    {
        // RServe uses a different rounding mode to PHPEval.
        $this->default_rounding_mode = PHP_ROUND_HALF_EVEN;
        parent::__construct($config);
    }

    public function connect()
    {
        $this->reset_error();

        if (is_null(self::$cnx)) {
            // Connection failed!
            $this->set_error('Can Not Connect');
            return false;
        }

        if (self::$cnx === false) {
            try {
                // if the box isnt on this timeout is ignored and is likely to be different
                if (!isset($this->config['timeout'])) {
                    $timeoutarray = ['seconds' => 5, 'milliseconds' => 1];
                } else {
                    $timeoutarray = ['seconds' => $this->config['timeout'], 'milliseconds' => 1];
                }
                self::$cnx = @new Rserve_Connection($this->config['host'], $this->config['port'], $timeoutarray);
            } catch (Exception $except) {
                self::$cnx = null;
                $this->set_error('Can Not Connect');
                return false;
            }

            $this->setup_R();
            return true;
        } else {
            // We are connected
            return true;
        }
    }

    /**
     * Resets the stored connection so that a different connection can be attempted.
     *
     * This should only be used in automatic testing.
     *
     * @return void
     */
    public static function resetConnection()
    {
        self::$cnx = false;
    }

    public function setup_R()
    {
        self::$cnx->evalString('options(digits=15); 1==1;');
        self::$cnx->evalString('toStr <- function(V) { return(paste(capture.output(print(V)),collapse=\'\n\')) }');
        self::$cnx->evalString('POW <- pow <- function(a,b) { return(a^b) }');
        self::$cnx->evalString('POW <- pow <- function(a,b) { return(a^b) }');
        self::$cnx->evalString('excel_round <- function(x, digits) round(x*(1+1e-15), digits)');
    }

    public function calculate_correct_ans($vars, $formula)
    {
        if (!$this->connect()) {
            throw new Exception('Cannot Connect');
            return false;
        }

        if (!is_array($vars)) {
            throw new Exception('No variables');
            return false;
        }

        $formula = $this->substituteMethodCalls($formula);
        $formula_vars_subed = EnhancedCalc::substitute_vars($vars, $formula);
        $correctanswer = $this->eval_string($formula_vars_subed);

        return $correctanswer;
    }

    private function eval_string($val)
    {
        if (!$this->connect()) {
            return false;
        }
        return $this->extract_value(self::$cnx->evalString('toStr(' . $val . ')'));
    }

    private function eval_string_multi($val)
    {
        if (!$this->connect()) {
            return false;
        }
        $cmd = 'c(';
        foreach ($val as $v) {
            $cmd .= 'toStr(' . $v . '),';
        }
        $cmd = rtrim($cmd, ',');
        $cmd .= ')';
        return $this->extract_value(self::$cnx->evalString($cmd));
    }

    private function extract_value($R_rreturn)
    {
        if (!is_array($R_rreturn)) {
            $R_rreturn = explode("\n", $R_rreturn);
        }

        $ret = [];
        foreach ($R_rreturn as $key => $val) {
            $val = trim($val);
            if ($val == '') {
                continue;
            }
            if ($val == '[1] TRUE') {
                $ret[] = true;
            } elseif ($val == '[1] FALSE') {
                $ret[] =  false;
            } else {
                $val = str_replace('"', '', $val);
                $pos = mb_strpos($val, ' ');
                $ret[] = mb_substr($val, $pos + 1);
            }
        }

        if (count($ret) == 1) {
            return $ret[0];
        } else {
            return $ret;
        }
    }

    public function get_error()
    {
        return $this->error_msg;
    }

    /**
     * The formula used for getting pi in this engine.
     *
     * @return string
     */
    protected function piFormula(): string
    {
        return 'pi';
    }
}
