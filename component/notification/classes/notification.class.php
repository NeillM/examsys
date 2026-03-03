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
use render;

/**
 * A component that is used to display a notification to the user.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class Notification implements Component
{
    /** @var string An informational notice. */
    public const TYPE_NOTICE = 'notice';

    /** @var string A warning notice. */
    public const TYPE_WARNING = 'warning';

    /** @var string A notice about a problem. */
    public const TYPE_PROBLEM = 'problem';

    /**
     * The constructor.
     *
     * @param string $message The localised message to be displayed to end users.
     * @param array $classes An array of classes to be added to the notice.
     * @param bool $image Flags if the notification should display a decorative image (default: false)
     *                    If flagged as true you must also pass a class that defines the background image to be used.
     * @param string $type The type of notification to be displayed (default: {@see self::TYPE_NOTICE})
     */
    public function __construct(
        protected string $message,
        protected array $classes = [],
        bool $image = false,
        string $type = self::TYPE_NOTICE,
    ) {
        if ($image) {
            $this->classes[] = 'image';
        }

        $this->classes[] = match ($type) {
            self::TYPE_NOTICE,
            self::TYPE_WARNING,
            self::TYPE_PROBLEM => $type,
            default => self::TYPE_NOTICE,
        };
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@notification/notification.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        return [
            'classes' => $this->classes,
            'message' => $this->message,
        ];
    }

    #[\Override]
    public static function getExample(): Component
    {
        return new self(
            message: 'Example message',
        );
    }

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
}
