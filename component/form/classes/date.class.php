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
 * A date input for use in forms.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Date extends Input
{
    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/date
     *
     * @param string $id The unique id of the input
     * @param string $name The name of the form input
     * @param string $label The label for the input
     * @param string $value The initial value of the input
     * @param string|null $autocomplete The autocomplete hint for the form element (optional)
     * @param array $classes Classes to be added to the input (optional)
     * @param string $description Additional help text for the form element (optional)
     * @param string $max The maximum date that may be entered (optional)
     * @param string $min The minimum date that may be entered (optional)
     * @param bool $readonly The form element is read only (default: false)
     * @param bool $required The form element is required (default: false)
     * @param int $step The step in number of days from the minimum that may be entered (optional)
     */
    public function __construct(
        string $id,
        string $name,
        string $label,
        string $value,
        ?string $autocomplete = null,
        array $classes = [],
        string $description = '',
        string $max = '',
        string $min = '',
        bool $readonly = false,
        bool $required = false,
        int $step = 0,
    ) {
        parent::__construct(
            type: 'date',
            id: $id,
            name: $name,
            label: $label,
            value: $value,
            autocomplete: $autocomplete,
            classes: $classes,
            description: $description,
        );

        if ($max) {
            $this->setAttribute('max', $max);
        }
        if ($min) {
            $this->setAttribute('min', $min);
        }
        if ($required) {
            $this->setAttribute('required');
        }
        if ($readonly) {
            $this->setAttribute('readonly');
        }
        if ($step) {
            $this->setAttribute('step', $step);
        }
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'date',
            name: 'date',
            label: 'Date',
            value: '2026-01-01',
            max: '2029-12-31',
            min: '2025-01-01',
            step: 4,
        );
    }
}
