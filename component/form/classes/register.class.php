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

namespace component\form;

use component\ComponentRegister;

/**
 * Stores a list of all components in the form collection
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Register implements ComponentRegister
{
    /**
     * List of components in the form collection
     *
     * @var string[]
     */
    protected static $components = [
        'Checkbox',
        'CheckboxGroup',
        'Color',
        'Button',
        'Date',
        'Email',
        'File',
        'Form',
        'GeneralGroup',
        'Hidden',
        'Number',
        'RadioGroup',
        'Range',
        'Reset',
        'Select',
        'Search',
        'StaticComponent',
        'StaticHtml',
        'StaticTemplate',
        'Submit',
        'Telephone',
        'TextArea',
        'Text',
        'Time',
        'Url',
    ];

    #[\Override]
    public static function getComponentList(): array
    {
        return self::$components;
    }
}
