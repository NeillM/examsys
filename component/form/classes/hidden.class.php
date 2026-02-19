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

use component\Component;

/**
 * A hidden input for use in forms.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Hidden extends FormElement
{
    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/hidden
     *
     * @param string $id The unique id of the input
     * @param string $name The name of the form input
     * @param string $value The initial value of the input
     */
    public function __construct(
        string $id,
        string $name,
        string $value,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            value: $value,
        );
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/hidden.html';
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'hidden-input',
            name: 'hidden-input',
            value: 'a-hidden-value',
        );
    }
}
