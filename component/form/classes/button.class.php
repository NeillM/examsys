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
 * A form button.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Button extends FormElement
{
    /** @var string The type of button.  */
    protected string $type = 'button';

    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/button
     *
     * @param string $name The name of the form element
     * @param string $value The value of the form element
     * @param array $classes Classes to be added to the input (optional)
     */
    public function __construct(
        string $name,
        string $value,
        protected array $classes = [],
    ) {
        parent::__construct(
            id: $name,
            name: $name,
            value: $value,
        );
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/button.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        return array_merge(
            parent::getData($renderer),
            [
                'type' => $this->type,
            ]
        );
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            name: 'button',
            value: 'A button',
        );
    }
}
