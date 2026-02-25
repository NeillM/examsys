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
use render;

/**
 * A select for use in forms.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Select extends FormElement
{
    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/select
     *
     * @param string $id The unique id of the select
     * @param string $name The name of the select
     * @param string $label The label for the select
     * @param array $options The list of options for the select. The key will be the value sent to the form,
     *                       while the value will be a localised string.
     * @param string|null $autocomplete The autocomplete hint for the form element (optional)
     * @param array $classes Classes to be added to the select
     * @param string $default The default option (optional)
     * @param string $description Additional help text for the form element (optional)
     * @param bool $disabled If the select is disabled. (default: false)
     * @param bool $multiple If multiple values can be selected (default: false)
     * @param bool $required If a value is required (default: false)
     * @param int $size The number of options to be displayed at one time (optional)
     */
    public function __construct(
        string $id,
        string $name,
        protected string $label,
        protected array $options,
        ?string $autocomplete = null,
        protected array $classes = [],
        string $default = '',
        protected string $description = '',
        bool $disabled = false,
        bool $multiple = false,
        bool $required = false,
        int $size = 0,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            value: $default,
        );

        if ($autocomplete) {
            $this->setAttribute('autocomplete', $autocomplete);
        }
        if ($size) {
            $this->setAttribute('size', $size);
        }
        if ($disabled) {
            $this->setAttribute('disabled');
        }
        if ($multiple) {
            $this->setAttribute('multiple');
        }
        if ($required) {
            $this->setAttribute('required');
        }
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/select.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        return array_merge(
            parent::getData($renderer),
            [
                'classes' => $this->classes,
                'description' => $this->description,
                'label' => $this->label,
                'options' => $this->options,
            ]
        );
    }

    #[\Override]
    public static function getExample(): Component
    {
        $options = [
            'opt1' => 'Option 1',
            'opt2' => 'Option 2',
            'opt3' => 'Option 3',
        ];
        return new self(
            id: 'select',
            name: 'select',
            label: 'Select',
            options: $options,
            default: 'opt2',
        );
    }
}
