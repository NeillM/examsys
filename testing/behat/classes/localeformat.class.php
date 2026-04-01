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

namespace testing\behat;

/**
 * Defines values that need to be localised before they can be used as input in Behat.
 *
 * We would love to use Intl, but it cannot output the formats required by Mink.
 *
 * There also does not appear to be a good way to force browsers to use a locale we define.
 *
 * It is possible that in the future we will be able to find a Browser Driver for Mink that
 * runs JavaScript and does not require this code.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class LocaleFormat
{
    /** @var string Format for a date input field. */
    public const FORMAT_DATE = 'date';

    /** @var string Format for a time input field. */
    public const FORMAT_TIME = 'time';

    /** @var string The locale to use if the user one is not supported. */
    protected const DEFAULT_LOCALE = 'en-GB';

    /**
     * Stores the localisation formats we support.
     *
     * We will be happy to add more formats here.
     *
     * @var \string[][]
     */
    protected static $locale_formats = [
        // Not a real locale, but will be passed when the browser does not support JavaScript.
        // we are making the assumption that in this case it is the Goutte engine running which
        // appears to use standard formatting.
        'no-javascript' => [
            self::FORMAT_DATE => 'Y-m-d',
            self::FORMAT_TIME => 'H:i',
        ],
        'en-GB' => [
            self::FORMAT_DATE => 'd-m-Y',
            self::FORMAT_TIME => 'H:i',
        ],
        'en-US' => [
            self::FORMAT_DATE => 'm-d-Y',
            self::FORMAT_TIME => 'h:iA',
        ]
    ];

    /**
     * Get a string for formatting the output that will be sent to a form field.
     *
     * @param string $locale The locale that we want a format for.
     * @param string $type The type of entity we wish to format (should be one of the self::FORMAT_* contestants.)
     * @return string
     */
    public static function getFormat(string $locale, string $type): string
    {
        return self::$locale_formats[$locale][$type] ?? self::$locale_formats[self::DEFAULT_LOCALE][$type];
    }
}
