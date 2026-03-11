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
 * A fieldset that contains an arbitrary set of form elements.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class GeneralGroup extends Fieldset
{
    /**
     * The constructor.
     *
     * @param string $id The unique id of the form element
     * @param string $name The name of the form element
     * @param string $label The label for the fieldset
     * @param array $classes Classes to be added to the fieldset (optional)
     * @param string $description Additional help text for the form element (optional)
     * @param string $orientation The orientation of fields that are in the fieldset (default: Fieldset::ORIENTATION_VERTICAL)
     */
    public function __construct(
        string $id,
        string $name,
        string $label,
        array $classes = [],
        string $description = '',
        string $orientation = self::ORIENTATION_VERTICAL,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            label: $label,
            classes: array_merge(['general-group'], $classes),
            description: $description,
            orientation: $orientation,
        );
    }

    /**
     * Adds a form element to the fieldset.
     *
     * @param FormElement $option
     * @return void
     */
    public function addOption(FormElement $option): void
    {
        $this->options[] = $option;
    }

    #[\Override]
    public static function getExample(): Component
    {
        $example = new self(
            id: 'general-fieldset',
            name: 'general-fieldset',
            label: 'Fieldset',
        );
        $example->addOption(Text::getExample());
        $example->addOption(Select::getExample());
        return $example;
    }
}
