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
 * A file input for use in forms.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class File extends Input
{
    /**
     * The constructor.
     *
     * For full details about the attributes see:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/file
     *
     * @param string $id The unique id of the input
     * @param string $name The name of the form input
     * @param string $label The label for the input
     * @param string $accept Comma separated list of file extensions that may be uploaded (optional))
     * @param string $capture Which type of device to get a file from, mostly used in mobile browsers (optional)
     * @param array $classes Classes added to the input (optional)
     * @param string $description Additional help text for the form element (optional)
     * @param bool $readonly The form element is read only (default: false)
     * @param bool $required The form element is required (default: false)
     * @param bool $multiple Flags if multiple files may be uploaded (default: false)
     */
    public function __construct(
        string $id,
        string $name,
        string $label,
        string $accept = '',
        string $capture = '',
        array $classes = [],
        string $description = '',
        bool $readonly = false,
        bool $required = false,
        bool $multiple = false,
    ) {
        parent::__construct(
            type:'file',
            id: $id,
            name: $name,
            label: $label,
            value: '',
            classes: $classes,
            description: $description,
        );

        // Optional attributes.
        if ($accept) {
            $this->setAttribute('accept', $accept);
        }
        if ($capture) {
            $this->setAttribute('capture', $capture);
        }
        if ($required) {
            $this->setAttribute('required');
        }
        if ($readonly) {
            $this->setAttribute('readonly');
        }
        if ($multiple) {
            $this->setAttribute('multiple');
        }
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            id: 'file',
            name: 'file',
            label: 'File',
        );
    }

    #[\Override]
    public function requiresMultiPartFormData(): bool
    {
        return true;
    }
}
