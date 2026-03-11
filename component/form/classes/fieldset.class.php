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

use component\Helper;
use render;

/**
 * The basics of a for a fieldset element.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
abstract class Fieldset extends FormElement
{
    /** @var string Form elements are arranged horizontally. */
    public const ORIENTATION_HORIZONTAL = 'horizontal';

    /** @var string Form elements are arranged vertically. */
    public const ORIENTATION_VERTICAL = 'vertical';

    /** @var FormElement[] An array of form elements to be rendered in the Fieldset. */
    protected array $options = [];

    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/fieldset
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
        protected string $label,
        protected array $classes = [],
        protected string $description = '',
        string $default = '',
        protected string $orientation = self::ORIENTATION_VERTICAL,
    ) {
        parent::__construct(
            id: $id,
            name: $name,
            value: $default,
        );
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@form/fieldset.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        $options = [];
        foreach ($this->options as $option) {
            $options[] = $option->getData($renderer);
        }

        return array_merge(
            parent::getData($renderer),
            [
                'classes' => $this->classes,
                'description' => $this->description,
                'label' => $this->label,
                'options' => $options,
                'orientation' => $this->orientation,
            ]
        );
    }

    #[\Override]
    public function getJavascriptForHead(): array
    {
        // Include JavaScript for all child elements.
        $js = [];
        foreach ($this->options as $element) {
            $js[] = $element->getJavascriptForHead();
        }
        return Helper::combineJS([], ...$js);
    }

    #[\Override]
    public function getJavascriptForFooter(): array
    {
        // Include JavaScript for all child elements.
        $js = [];
        foreach ($this->options as $element) {
            $js[] = $element->getJavascriptForFooter();
        }
        return Helper::combineJS([], ...$js);
    }

    #[\Override]
    public function getStrings(): array
    {
        // Include the strings for all the child elements.
        return Helper::combineLang([], ...$this->options);
    }
}
