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
 * Adds the output of a template into a form.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class StaticTemplate extends StaticHtml
{
    /**
     * The constructor.
     *
     * @param array $data The data to be used to render the template
     * @param string $template The name of the template to be used to render the component
     * @param array $strings The language strings for the template (optional)
     * @param array $javascript Paths to any JavaScript required by the template,
     *                          it will be included in the footer of the page (optional)
     */
    public function __construct(
        protected array $data,
        protected string $template,
        protected array $strings = [],
        protected array $javascript = [],
    ) {
        parent::__construct('');
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        return array_merge(
            parent::getData($renderer),
            [
                // We are using the component names here so that we do not need an additional branch
                // in the static template.
                'component' => $this->data,
                'componenttemplate' => $this->template ?? $this->component->defaultTemplate(),
            ]
        );
    }

    #[\Override]
    public static function getExample(): Component
    {
        $data = [
            'icon' => '/artwork/comment_48.png',
            'titlecolour' => '#ff2222',
            'msg' => 'Example template output (notice)',
        ];
        return new self(
            data: $data,
            template: 'notice.html',
        );
    }

    #[\Override]
    public function getJavascriptForFooter(): array
    {
        return $this->javascript;
    }

    #[\Override]
    public function getStrings(): array
    {
        return $this->strings;
    }
}
