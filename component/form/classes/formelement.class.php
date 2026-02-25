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
 * The base data of all elements that go inside forms.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
abstract class FormElement implements Component
{
    /** @var array Attributes of the input */
    protected array $attributes = [];

    /**
     * The constructor.
     *
     * @param string $id The unique id of the element
     * @param string $name The name of the form element
     * @param string $value The value of the form element
     */
    public function __construct(
        protected string $id,
        protected string $name,
        protected string $value,
    ) {
        // Intentionally empty.
    }

    /**
     * Sets the value of an attribute of the input.
     *
     * @param string $name The name of the attribute.
     * @param string $value The value of the attribute, when not passed the attribute will have no value (optional).
     * @return void
     */
    protected function setAttribute(string $name, string $value = ''): void
    {
        $this->attributes[$name] = $value;
    }

    #[\Override]
    abstract public function defaultTemplate(): string;

    #[\Override]
    public function getData(render $renderer): array
    {
        return [
            'attributes' => $this->attributes,
            'id' => $this->id,
            'name' => $this->name,
            // We need to output the template name so that the correct thing is rendered when
            // the input is rendered via the form template.
            'template' => $this->defaultTemplate(),
            'value' => $this->value,
        ];
    }

    #[\Override]
    abstract public static function getExample(): Component;

    #[\Override]
    public function getJavascriptForHead(): array
    {
        return [];
    }

    #[\Override]
    public function getJavascriptForFooter(): array
    {
        return [];
    }

    #[\Override]
    public function getStrings(): array
    {
        return [];
    }

    /**
     * Returns if the form must be submitted using multipart form data encoding for it  to work correctly.
     *
     * @return bool
     */
    public function requiresMultiPartFormData(): bool
    {
        return false;
    }
}
