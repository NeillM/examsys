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
 * Adds the output of a component into a form.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class StaticComponent extends StaticHtml
{
    /**
     * The constructor.
     *
     * @param Component $component The component to be rendered
     * @param string|null $template The name of the template to be used to render the component (optional)
     */
    public function __construct(
        protected Component $component,
        protected ?string $template = null,
    ) {
        parent::__construct('');
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        return array_merge(
            parent::getData($renderer),
            [
                'component' => $this->component->getData($renderer),
                'componenttemplate' => $this->template ?? $this->component->defaultTemplate(),
            ]
        );
    }

    #[\Override]
    public static function getExample(): Component
    {
        $component = new \component\breadcrumb\Breadcrumb();
        $component->addBreadcrumb('Item 1');
        $component->addBreadcrumb('Item 2');
        $component->addCurrentPage('Item 3');
        return new self($component);
    }

    #[\Override]
    public function getJavascriptForHead(): array
    {
        return $this->component->getJavascriptForHead();
    }

    #[\Override]
    public function getJavascriptForFooter(): array
    {
        return $this->component->getJavascriptForFooter();
    }

    #[\Override]
    public function getStrings(): array
    {
        return $this->component->getStrings();
    }
}
