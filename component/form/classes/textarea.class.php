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
 * A textarea for use in forms.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class TextArea extends FormElement
{
    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/textarea
     *
     * @param string $id The unique id of the select
     * @param string $name The name of the select
     * @param string $label The label for the select
     * @param string|null $autocomplete The autocomplete hint for the form element (optional)
     * @param bool|null $autocorrect Controls if autocorrect is flagged as on (default: null)
     * @param array $classes Classes to be added to the select
     * @param int $cols The number of text columns in the textarea (default: 60)
     * @param string $value The default option (optional)
     * @param string $description Additional help text for the form element (optional)
     * @param bool $disabled If the select is disabled. (default: false)
     * @param int $maxlength The maximum number of characters the user can enter (default: unlimited)
     * @param int $minlength The minimum number of characters the user can enter (default: 0)
     * @param bool $required If a value is required (default: false)
     * @param int $rows The number of rows of text to be displayed at one time (default: 10)
     * @param bool|null $spellcheck Flags if spell checking is enabled on the element (default: null)
     */
    public function __construct(
        string $id,
        string $name,
        protected string $label,
        ?string $autocomplete = null,
        ?bool $autocorrect = false,
        protected array $classes = [],
        int $cols = 60,
        string $value = '',
        protected string $description = '',
        bool $disabled = false,
        int $maxlength = 0,
        int $minlength = 0,
        bool $required = false,
        int $rows = 10,
        ?bool $spellcheck = null,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            value: $value,
        );

        $this->setAttribute('cols', $cols);
        $this->setAttribute('rows', $rows);

        if ($autocomplete) {
            $this->setAttribute('autocomplete', $autocomplete);
        }
        if ($autocorrect) {
            $this->setAttribute('autocorrect', $autocorrect ? 'on' : 'off');
        }
        if ($maxlength) {
            $this->setAttribute('maxlength', $maxlength);
        }
        if ($minlength) {
            $this->setAttribute('minlength', $minlength);
        }
        if ($spellcheck !== null) {
            $this->setAttribute('spellcheck', $spellcheck ? 'true' : 'false');
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
        return '@form/textarea.html';
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
            ]
        );
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'textarea',
            name: 'textarea',
            label: 'Text Area',
        );
    }
}
