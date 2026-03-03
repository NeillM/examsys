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

namespace component\notification;

use component\Component;
use component\notification\Notification;

/**
 * A component that is used to display a problem to the user.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Problem extends Notification
{
    /**
     * The constructor.
     *
     * @param string $message The localised message to be displayed to end users.
     * @param array $classes An array of classes to be added to the notice.
     * @param bool $image Flags if the notification should display a decorative image (default: false)
     *                    If flagged as true you must also pass a class that defines the background image to be used.
     */
    public function __construct(
        string $message,
        array $classes = [],
        bool $image = false,
    ) {
        parent::__construct(
            message: $message,
            classes: $classes,
            image: $image,
            type: parent::TYPE_PROBLEM,
        );
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            message: 'Example message',
        );
    }
}
