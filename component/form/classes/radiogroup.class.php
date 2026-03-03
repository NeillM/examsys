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
 * A fieldset containing only radio buttons.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class RadioGroup extends Fieldset
{
    /**
     * The constructor.
     *
     * @param string $id The unique id of the form element
     * @param string $name The name of the form element
     * @param string $label The label for the fieldset
     * @param array $classes Classes to be added to the fieldset (optional)
     * @param string $description Additional help text for the form element (optional)
     * @param string $default The default value of the fieldset (optional)
     * @param string $orientation The orientation of fields that are in the fieldset (default: Fieldset::ORIENTATION_VERTICAL)
     */
    public function __construct(
        string $id,
        string $name,
        string $label,
        array $classes = [],
        string $description = '',
        string $default = '',
        protected string $required = '',
        string $orientation = self::ORIENTATION_VERTICAL,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            label: $label,
            classes: ['radio-group'] + $classes,
            description: $description,
            default: $default,
            orientation: $orientation,
        );
    }

    /**
     * Adds a new radio button to the fieldset.
     *
     * @param string $value The value to be sent when the option is selected
     * @param string $label The localised label for the option
     * @param string $description The localised help text for the option (optional)
     * @return void
     */
    public function addOption(string $value, string $label, string $description = '', bool $disabled = false): void
    {
        $option = new Radio(
            id: $this->id . '-' . $value,
            name: $this->name,
            label: $label,
            value: $value,
            disabled: $disabled,
            description: $description,
            required: $this->required,
            selected: $value == $this->value,
        );
        $this->options[] = $option;
    }

    #[\Override]
    public static function getExample(): Component
    {
        $example = new self(
            id: 'radio-group',
            name: 'radio-group',
            label: 'Radio group',
            default: 'opt3',
            orientation: Fieldset::ORIENTATION_HORIZONTAL,
        );
        $example->addOption('opt1', 'Option 1');
        $example->addOption('opt2', 'Option 2', 'Option 2 has some additional description');
        $example->addOption('opt3', 'Option 3', disabled: true);
        return $example;
    }
}
