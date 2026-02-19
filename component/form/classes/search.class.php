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
 * A search input for use in forms.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Search extends Text
{
    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/search
     *
     * @param string $id The unique id of the input
     * @param string $name The name of the form input
     * @param string $label The label for the input
     * @param string $value The initial value of the input
     * @param string|null $autocomplete The autocomplete hint for the form element (optional)
     * @param array $classes Classes added to the input (optional)
     * @param string $description Additional help text for the form element (optional)
     * @param string|null $list The id of a datalist element (optional)
     * @param bool $readonly The form element is read only (default: false)
     * @param bool $required The form element is required (default: false)
     * @param int|null $maxLength The maximum number of characters that mat be entered (optional)
     * @param int|null $minLength The minimum number of characters that mat be entered (optional)
     * @param string|null $pattern The pattern the input must meet (optional)
     * @param bool|null $spellcheck If spell checking is enabled (default: no value)
     * @param int $size The size of the input (default: 20)
     */
    public function __construct(
        string $id,
        string $name,
        string $label,
        string $value = '',
        ?string $autocomplete = null,
        array $classes = [],
        string $description = '',
        ?string $list = null,
        bool $readonly = false,
        bool $required = false,
        ?int $maxLength = null,
        ?int $minLength = null,
        ?string $pattern = null,
        ?bool $spellcheck = null,
        int $size = 20,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            label: $label,
            value: $value,
            autocomplete: $autocomplete,
            classes: $classes,
            description: $description,
            list: $list,
            readonly: $readonly,
            required: $required,
            maxLength: $maxLength,
            minLength: $minLength,
            pattern: $pattern,
            spellcheck: $spellcheck,
            size: $size
        );

        $this->type = 'search';
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'search',
            name: 'search',
            label: 'Search',
        );
    }
}
