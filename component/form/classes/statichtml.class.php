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
 * Adds arbitrary HTML into a form.
 *
 * We should usually be trying to use the component and Template statics
 * before we consider this one.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class StaticHtml extends FormElement
{
    /**
     * The constructor.
     *
     * @param string $value The html string to be inserted into the form.
     */
    public function __construct(
        string $value,
    ) {
        parent::__construct(
            id: '',
            name: '',
            value: $value
        );
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/static.html';
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new static(
            '<p>This is some random html from the StaticHtml form component</p>'
        );
    }
}
