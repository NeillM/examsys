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
 * A checkbox input for use in forms.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Checkbox extends Input
{
    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/checkbox
     *
     * @param string $id The unique id of the input
     * @param string $name The name of the form input
     * @param string $label The label for the input
     * @param string|null $autocomplete The autocomplete hint for the form element (optional)
     * @param string $value The value sent when the checkbox is set (default: on)
     * @param bool $checked The checked state of the checkbox (default: false)
     * @param array $classes Classes added to the input (optional)
     * @param bool $disabled If the checkbox is disabled. (default: false)
     * @param string $description Additional help text for the form element
     */
    public function __construct(
        string $id,
        string $name,
        string $label,
        string $value = 'on',
        ?string $autocomplete = null,
        bool $checked = false,
        array $classes = [],
        bool $disabled = false,
        string $description = '',
    ) {
        parent::__construct(
            type:'checkbox',
            id: $id,
            name: $name,
            label: $label,
            value: $value,
            autocomplete: $autocomplete,
            classes: $classes,
            description: $description,
        );

        if ($checked) {
            $this->setAttribute('checked');
        }
        if ($disabled) {
            $this->setAttribute('disabled');
        }
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/input_label_after.html';
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'checkbox',
            name: 'checkbox',
            label: 'Checkbox',
            checked: true,
        );
    }
}
