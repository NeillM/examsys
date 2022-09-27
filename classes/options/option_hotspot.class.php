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

/**
 *
 * Class for Hotspot options
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class OptionHOTSPOT extends OptionEdit
{
    /** @var string Incorrect hotspot data for CSV marking export */
    protected $incorrect = '';
    public static $_metafields = ['incorrect'];

    /**
     * Is this option blank?
     * @return boolean
     */
    public function is_blank()
    {
        return ($this->correct == '');
    }

    /**
     * Create a new hotspot option object by either loading an existing option from the database or populating
     * properties from an associative array
     * @param mixed $data
     */
    public function __construct($mysqli, $user_id, $question, $number, $lang_strings, $data = null)
    {
        parent::__construct($mysqli, $user_id, $question, $number, $lang_strings, $data);

        // Add class-specific fields
        $this->_fields_editable[] = 'incorrect';
        $this->_pretty_names['correct'] = $lang_strings['correctlayers'];
        $this->_pretty_names['incorrect'] = $lang_strings['incorrectlayers'];
    }

    /**
     * Check that the minimum set of fields exist in the given data to create a new option
     * @param array $data
     * @param array $files expects PHP FILES array
     * @param integer $index option number
     * @return boolean
     */
    public function minimum_fields_exist($data, $files, $index)
    {
        return true;
    }

    /**
     * Get the option incorrect answer
     * @return string
     */
    public function get_incorrect()
    {
        return $this->incorrect;
    }

    /**
     * Set the option correct answer, override parent to always record
     * @param string $value
     */
    public function set_correct($value)
    {
        $value = param::cleanBadChars($value);
        if ($value != $this->correct) {
            $leadin = '';
            $layers = explode(hotspot_helper::LAYER_SEPARATOR, $value);
            foreach ($layers as $i => $layer) {
                $parts = explode(hotspot_helper::CORRECT_SEPARATOR, $layer);
                $leadin = (($leadin === '') ? '' : $leadin . ', ') . QuestionUtils::numbersToLetters($i + 1) . ') ' . $parts[0];
            }
            $this->_question->set_leadin($leadin);
            $this->correct = $value;
        }
    }

    /**
     * Set the option incorrect answer
     * @param string $value
     */
    public function set_incorrect($value)
    {
        if ($value != $this->incorrect) {
            $this->incorrect = $value;
        }
    }
}
