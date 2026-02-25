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

use render;

/**
 * The basics of a for input element.
 *
 * This will be extended by specific types of input.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
abstract class Input extends FormElement
{
    /**
     * The constructor.
     *
     * @param string $type The type of input (for example text or hidden)
     * @param string $id The unique id of the input
     * @param string $name The name of the form input
     * @param string $label The label for the input
     * @param string $value The initial value of the input
     * @param string|null $autocomplete The autocomplete hint for the form element (optional)
     * @param array $classes Classes to be added to the input
     * @param string $description Additional help text for the form element
     */
    public function __construct(
        protected string $type,
        string $id,
        string $name,
        protected string $label,
        string $value,
        ?string $autocomplete = null,
        protected array $classes = [],
        protected string $description = '',
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            value: $value,
        );

        if ($autocomplete) {
            $this->setAttribute('autocomplete', $autocomplete);
        }
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/input.html';
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
                'type' => $this->type,
            ]
        );
    }
}
