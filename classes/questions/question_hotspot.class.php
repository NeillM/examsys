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
 * Class for Image Hotspot questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class QuestionHOTSPOT extends QuestionEdit
{
    protected $_fields_required = array('type', 'leadin', 'option_order', 'owner_id', 'status');

    protected $_requires_media = true;
    protected $_requires_correction_intermediate = true;
    protected $_requires_html5 = true;
    public $max_options = 1;

    public function __construct($mysqli, $userObj, $lang_strings, $data = null)
    {
        parent::__construct($mysqli, $userObj, $lang_strings, $data);

        // Convert the max number of options into a list of variables
        $this->option_order = 'display order';
        $this->_fields_change = array('option_marks_correct', 'option_marks_incorrect', 'option_correct', 'option_incorrect', 'option_correct1');
        $this->_fields_unified['correct'] = $lang_strings['correctlayers'];
        $this->_fields_unified['incorrect'] = $lang_strings['incorrectlayers'];
    }

    /**
     * Set the question leadin, stripping any carriage returns
     * @param string $value
     */
    public function set_leadin($value)
    {
        $value = str_replace("\r\n", ' ', $value);
        if ($value != $this->leadin) {
            $this->set_modified_field('leadin', $this->leadin);
            $this->leadin = $value;
        }
    }
}
