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

class Radio extends Input
{
    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/radio
     *
     * @param string $id The unique id of the input
     * @param string $name The name of the form input
     * @param string $label The label for the input
     * @param string $value The value sent when the radio button is selected
     * @param string|null $autocomplete The autocomplete hint for the form element (optional)
     * @param array $classes Classes added to the input (optional)
     * @param bool $disabled If the radio button is disabled. (default: false)
     * @param string $description Additional help text for the form element
     * @param bool $required The radio button is required (default: false)
     * @param bool $selected The checked state of the radio button (default: false)
     */
    public function __construct(
        string $id,
        string $name,
        string $label,
        string $value,
        ?string $autocomplete = null,
        array $classes = [],
        bool $disabled = false,
        string $description = '',
        bool $required = false,
        bool $selected = false,
    ) {
        parent::__construct(
            type:'radio',
            id: $id,
            name: $name,
            label: $label,
            value: $value,
            autocomplete: $autocomplete,
            classes: $classes,
            description: $description,
        );

        if ($selected) {
            $this->setAttribute('checked');
        }
        if ($disabled) {
            $this->setAttribute('disabled');
        }
        if ($required) {
            $this->setAttribute('required');
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
            id: 'radio',
            name: 'radio',
            label: 'Radio button',
            value: 'example',
            selected: true,
        );
    }
}
