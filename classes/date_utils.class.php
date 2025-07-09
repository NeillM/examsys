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

/**
 *
 * Utility class for date related functionality
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class date_utils
{
    /**
     * @var int MINUTESECS seconds in a minute
     */
    public const MINUTESECS = 60;

    /**
     * @var int HOURSECS seconds in a hour
     */
    public const HOURSECS = 3600;

    /**
     * @var int DAYSECS seconds in a day
     */
    public const DAYSECS = 86400;

    /**
     * @var int WEEKSECS seconds in a week
     */
    public const WEEKSECS = 604800;

    /**
     * @var int YEARSECS seconds in a year
     */
    public const YEARSECS = 31557600;

    /**
     * Creates HTML dropdown menus to select day, month, year and hour (in half hour increments).
     * @param string $prefix            - Prefix string to make the name of the selector.
     * @param string $input_date    - Default time/date to populate the selector.
     * @param bool $split_time    - False = one dropdown for hours & minutes, True = two separate dropdowns for hours and minutes.
     * @param int $start_year     - Start year for the year dropdown (e.g. 2001).
     * @param int $end_year       - End year for the year dropdown (e.g. 2015).
     *
     * @deprecated Use accessible_timedate_select() instead for better accessibility support.
     * @return string - The HTML of the time/date selector.
     */
    public static function timedate_select($prefix, $imput_date, $split_time, $start_year, $end_year, $string)
    {
        $split_year = mb_substr((string) $imput_date, 0, 4);
        $split_month = mb_substr((string) $imput_date, 4, 2);
        $split_day = mb_substr((string) $imput_date, 6, 2);
        $split_hour = mb_substr((string) $imput_date, 8, 2);
        $split_minute = mb_substr((string) $imput_date, 10, 2);

        $html = '';

        // Day
        $html .= '<select name="' . $prefix . 'day" id="' . $prefix . "day\">\n";
        for ($i = 1; $i < 32; $i++) {
            if ($i < 10) {
                if ($i == $split_day) {
                    $html .= "<option value=\"0$i\" selected>";
                } else {
                    $html .= "<option value=\"0$i\">";
                }
            } else {
                if ($i == $split_day) {
                    $html .= "<option value=\"$i\" selected>";
                } else {
                    $html .= "<option value=\"$i\">";
                }
            }
            if ($i < 10) {
                $html .= '0';
            }
            $html .= "$i</option>\n";
        }
        $html .= '</select>';

        // Month
        $html .= '<select name="' . $prefix . 'month" id="' . $prefix . "month\">\n";
        $months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
        for ($i = 0; $i < 12; $i++) {
            $trans_month = mb_substr((string) $string[$months[$i]], 0, 3, 'UTF-8');
            if (($split_month - 1) == $i) {
                if ($i < 9) {
                    $html .= '<option value="0' . ($i + 1) . "\" selected>$trans_month</option>\n";
                } else {
                    $html .= '<option value="' . ($i + 1) . "\" selected>$trans_month</option>\n";
                }
            } else {
                if ($i < 9) {
                    $html .= '<option value="0' . ($i + 1) . "\">$trans_month</option>\n";
                } else {
                    $html .= '<option value="' . ($i + 1) . "\">$trans_month</option>\n";
                }
            }
        }
        $html .= '</select>';

        // Year
        $html .= '<select name="' . $prefix . 'year" id="' . $prefix . 'year">';
        for ($i = $start_year; $i <= $end_year; $i++) {
            if ($i == $split_year) {
                $html .= "<option value=\"$i\" selected>$i</option>\n";
            } else {
                $html .= "<option value=\"$i\">$i</option>\n";
            }
        }
        $html .= '</select>';

        if ($split_time) {
            $html .= '<select name="' . $prefix . 'hour" id="' . $prefix . "hour\">\n";
            for ($key = 0; $key < 24; $key++) {
                if ($key < 10) {
                    $key = '0' . $key;
                }
                if ($key == $split_hour) {
                    $html .= '<option value="' . $key . '" selected>' . $key . "</option>\n";
                } else {
                    $html .= '<option value="' . $key . '">' . $key . "</option>\n";
                }
            }
            $html .= '</select>';

            $html .= '<select name="' . $prefix . 'minute" id="' . $prefix . "minute\">\n";
            for ($key = 0; $key < 60; $key++) {
                if ($key < 10) {
                    $key = '0' . $key;
                }
                if ($key == $split_minute) {
                    $html .= '<option value="' . $key . '" selected>' . $key . "</option>\n";
                } else {
                    $html .= '<option value="' . $key . '">' . $key . "</option>\n";
                }
            }
            $html .= '</select>';
        } else {
            // Time
            $times = ['000000' => '00:00','003000' => '00:30','010000' => '01:00','013000' => '01:30','020000' => '02:00','023000' => '02:30','030000' => '03:00','033000' => '03:30','040000' => '04:00','043000' => '04:30','050000' => '05:00','053000' => '05:30','060000' => '06:00','063000' => '06:30','070000' => '07:00','073000' => '07:30','080000' => '08:00','083000' => '08:30','090000' => '09:00','093000' => '09:30','100000' => '10:00','103000' => '10:30','110000' => '11:00','113000' => '11:30','120000' => '12:00','123000' => '12:30','130000' => '13:00','133000' => '13:30','140000' => '14:00','143000' => '14:30','150000' => '15:00','153000' => '15:30','160000' => '16:00','163000' => '16:30','170000' => '17:00','173000' => '17:30','180000' => '18:00','183000' => '18:30','190000' => '19:00','193000' => '19:30','200000' => '20:00','203000' => '20:30','210000' => '21:00','213000' => '21:30','220000' => '22:00','223000' => '22:30','230000' => '23:00','233000' => '23:30'];
            $html .= '<select name="' . $prefix . 'time" id="' . $prefix . "time\">\n";
            foreach ($times as $key => $value) {
                if ($key == $split_hour . $split_minute . '00') {
                    $html .= '<option value="' . $key . '" selected>' . $value . "</option>\n";
                } else {
                    $html .= '<option value="' . $key . '">' . $value . "</option>\n";
                }
            }
            $html .= '</select>';
        }

        return $html;
    }

    /**
     * Creates HTML dropdown menus to select day, month, year and hour (in half hour increments) with accessibility attributes.
     * This is an accessible version of timedate_select() that includes proper labels for screen readers.
     *
     * @param string $prefix      - Prefix string to make the name of the selector.
     * @param string $input_date  - Default time/date to populate the selector.
     * @param bool $split_time    - False = one dropdown for hours & minutes, True = two separate dropdowns for hours and minutes.
     * @param int $start_year     - Start year for the year dropdown (e.g. 2001).
     * @param int $end_year       - End year for the year dropdown (e.g. 2015).
     * @param array $string       - Language strings array.
     * @param string $label_prefix - Prefix for aria-labels (e.g., "Start date" or "End date").
     *
     * @return string - The HTML of the accessible time/date selector.
     */
    public static function accessible_timedate_select($prefix, $input_date, $split_time, $start_year, $end_year, $string, $label_prefix = '')
    {
        $split_year = mb_substr((string) $input_date, 0, 4);
        $split_month = mb_substr((string) $input_date, 4, 2);
        $split_day = mb_substr((string) $input_date, 6, 2);
        $split_hour = mb_substr((string) $input_date, 8, 2);
        $split_minute = mb_substr((string) $input_date, 10, 2);

        $html = '';

        // Get the appropriate aria labels based on prefix
        $day_label = isset($string[$label_prefix . '_day']) ? $string[$label_prefix . '_day'] : (isset($string['day']) ? $string['day'] : 'Day');
        $month_label = isset($string[$label_prefix . '_month']) ? $string[$label_prefix . '_month'] : (isset($string['month']) ? $string['month'] : 'Month');
        $year_label = isset($string[$label_prefix . '_year']) ? $string[$label_prefix . '_year'] : (isset($string['year']) ? $string['year'] : 'Year');
        $hour_label = isset($string[$label_prefix . '_hour']) ? $string[$label_prefix . '_hour'] : (isset($string['hour']) ? $string['hour'] : 'Hour');
        $minute_label = isset($string[$label_prefix . '_minute']) ? $string[$label_prefix . '_minute'] : (isset($string['minute']) ? $string['minute'] : 'Minute');
        
        // Day
        $html .= '<select name="' . $prefix . 'day" id="' . $prefix . 'day" aria-label="' . $day_label . '">\n';
        for ($i = 1; $i < 32; $i++) {
            if ($i < 10) {
                if ($i == $split_day) {
                    $html .= "<option value=\"0$i\" selected>";
                } else {
                    $html .= "<option value=\"0$i\">";
                }
            } else {
                if ($i == $split_day) {
                    $html .= "<option value=\"$i\" selected>";
                } else {
                    $html .= "<option value=\"$i\">";
                }
            }
            if ($i < 10) {
                $html .= '0';
            }
            $html .= "$i</option>\n";
        }
        $html .= '</select>';

        // Month
        $html .= '<select name="' . $prefix . 'month" id="' . $prefix . 'month" aria-label="' . $month_label . '">\n';
        $months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
        for ($i = 0; $i < 12; $i++) {
            $trans_month = mb_substr((string) $string[$months[$i]], 0, 3, 'UTF-8');
            if (($split_month - 1) == $i) {
                if ($i < 9) {
                    $html .= '<option value="0' . ($i + 1) . "\" selected>$trans_month</option>\n";
                } else {
                    $html .= '<option value="' . ($i + 1) . "\" selected>$trans_month</option>\n";
                }
            } else {
                if ($i < 9) {
                    $html .= '<option value="0' . ($i + 1) . "\">$trans_month</option>\n";
                } else {
                    $html .= '<option value="' . ($i + 1) . "\">$trans_month</option>\n";
                }
            }
        }
        $html .= '</select>';

        // Year
        $html .= '<select name="' . $prefix . 'year" id="' . $prefix . 'year" aria-label="' . $year_label . '">';
        for ($i = $start_year; $i <= $end_year; $i++) {
            if ($i == $split_year) {
                $html .= "<option value=\"$i\" selected>$i</option>\n";
            } else {
                $html .= "<option value=\"$i\">$i</option>\n";
            }
        }
        $html .= '</select>';

        if ($split_time) {
            $html .= '<select name="' . $prefix . 'hour" id="' . $prefix . 'hour" aria-label="' . $hour_label . '">\n';
            for ($key = 0; $key < 24; $key++) {
                if ($key < 10) {
                    $key = '0' . $key;
                }
                if ($key == $split_hour) {
                    $html .= '<option value="' . $key . '" selected>' . $key . "</option>\n";
                } else {
                    $html .= '<option value="' . $key . '">' . $key . "</option>\n";
                }
            }
            $html .= '</select>';

            $html .= '<select name="' . $prefix . 'minute" id="' . $prefix . 'minute" aria-label="' . $minute_label . '">\n';
            for ($key = 0; $key < 60; $key++) {
                if ($key < 10) {
                    $key = '0' . $key;
                }
                if ($key == $split_minute) {
                    $html .= '<option value="' . $key . '" selected>' . $key . "</option>\n";
                } else {
                    $html .= '<option value="' . $key . '">' . $key . "</option>\n";
                }
            }
            $html .= '</select>';
        } else {
            // Time
            $times = ['000000' => '00:00','003000' => '00:30','010000' => '01:00','013000' => '01:30','020000' => '02:00','023000' => '02:30','030000' => '03:00','033000' => '03:30','040000' => '04:00','043000' => '04:30','050000' => '05:00','053000' => '05:30','060000' => '06:00','063000' => '06:30','070000' => '07:00','073000' => '07:30','080000' => '08:00','083000' => '08:30','090000' => '09:00','093000' => '09:30','100000' => '10:00','103000' => '10:30','110000' => '11:00','113000' => '11:30','120000' => '12:00','123000' => '12:30','130000' => '13:00','133000' => '13:30','140000' => '14:00','143000' => '14:30','150000' => '15:00','153000' => '15:30','160000' => '16:00','163000' => '16:30','170000' => '17:00','173000' => '17:30','180000' => '18:00','183000' => '18:30','190000' => '19:00','193000' => '19:30','200000' => '20:00','203000' => '20:30','210000' => '21:00','213000' => '21:30','220000' => '22:00','223000' => '22:30','230000' => '23:00','233000' => '23:30'];
            $time_label = isset($string[$label_prefix . '_time']) ? $string[$label_prefix . '_time'] : (isset($string['time']) ? $string['time'] : 'Time');
            $html .= '<select name="' . $prefix . 'time" id="' . $prefix . 'time" aria-label="' . $time_label . '">\n';
            foreach ($times as $key => $value) {
                if ($key == $split_hour . $split_minute . '00') {
                    $html .= '<option value="' . $key . '" selected>' . $value . "</option>\n";
                } else {
                    $html .= '<option value="' . $key . '">' . $value . "</option>\n";
                }
            }
            $html .= '</select>';
        }

        return $html;
    }

    /**
     * Converts a time/date from 20140301103059 into a localised date.
     *
     * @param string $original - The date that needs to be convered.
     * @return string
     */
    public static function rogoToDisplay(string $original): string
    {
        $day = mb_substr($original, 6, 2);
        $month = mb_substr($original, 4, 2);
        $year = mb_substr($original, 0, 4);
        $hours = mb_substr($original, 8, 2);
        $minutes = mb_substr($original, 10, 2);
        $date = new \DateTime();
        $date->setDate($year, $month, $day);
        $date->setTime($hours, $minutes, 0);
        return $date->format(Config::get_instance()->get('cfg_short_datetime_php'));
    }

    /**
     * Get the timestamp from time.
     *
     * @param integer $hours hours
     * @param integer $minutes minutes
     * @param DateTimeZone $timezone timezone object
     * @throws Exception
     * @return int
     */
    public static function getTimestampFromTime(int $hours, int $minutes, DateTimeZone $timezone): int
    {
        $date = new DateTime('now', $timezone);
        $date->setTime($hours, $minutes);
        return $date->getTimestamp();
    }

    /**
     * Get the UTC date time object
     *
     * @param string $time the time
     * @param string $timezone the timezone
     * @throws Exception
     * @return DateTime
     */
    public static function getUTCDateTime(string $time, string $timezone): DateTime
    {
        $utc_timezone = new DateTimeZone('UTC');
        $target_timezone = new DateTimeZone($timezone);
        $datetime = new DateTime($time, $target_timezone);
        $datetime->setTimezone($utc_timezone);
        return $datetime;
    }

    /**
     * Get the date time object from ExamSys date time selectors
     *
     * @param integer $year year set
     * @param integer $month month set
     * @param integer $day day set
     * @param integer $hour hour set
     * @param integer $minute minute set
     * @param string $timezone
     * @throws Exception
     * @return DateTime
     */
    public static function getDateTimeFromSelection(
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        string $timezone
    ): DateTime {
        $target_timezone = new DateTimeZone($timezone);
        $datetime = new DateTime('now', $target_timezone);
        $datetime->setDate(
            $year,
            $month,
            $day
        );
        $datetime->setTime(
            $hour,
            $minute
        );
        return $datetime;
    }

    /**
     * Converts a time/date from 20140301103059 into a UTC timestamp.
     *
     * @param string $original - The date that needs to be convered.
     * @return int
     */
    public static function rogoToTimestamp(string $original): int
    {
        $day = mb_substr($original, 6, 2);
        $month = mb_substr($original, 4, 2);
        $year = mb_substr($original, 0, 4);
        $hours = mb_substr($original, 8, 2);
        $minutes = mb_substr($original, 10, 2);
        $utc_timezone = new DateTimeZone('UTC');
        $target_timezone = new DateTimeZone(Config::get_instance()->get('cfg_timezone'));
        $date = new \DateTime('now', $target_timezone);
        $date->setDate($year, $month, $day);
        $date->setTime($hours, $minutes, 0);
        $date->setTimezone($utc_timezone);
        return $date->getTimestamp();
    }

    /**
     * Formats a duration in seconds as a human-readable string.
     *
     * @param int $seconds Number of seconds.
     * @return string
     */
    public static function formatDuration(int $seconds): string
    {
        $diff_hour = ($seconds / 60) / 60;
        $tmp_position = mb_strpos($diff_hour, '.');
        if ($tmp_position > 0) {
            $diff_hour = mb_substr($diff_hour, 0, $tmp_position);
        }
        if ($diff_hour > 0) {
            $seconds -= ($diff_hour * 60) * 60;
        }
        $diff_min = $seconds / 60;
        $tmp_position = mb_strpos($diff_min, '.');
        if ($tmp_position > 0) {
            $diff_min = mb_substr($diff_min, 0, $tmp_position);
        }
        if ($diff_min > 0) {
            $seconds -= $diff_min * 60;
        }
        $diff_sec = $seconds;
        $timestring = '';
        if ($diff_hour < 10) {
            $timestring = '0';
        }
        $timestring .= $diff_hour . ':';
        if ($diff_min < 10) {
            $timestring .= '0';
        }
        $timestring .= $diff_min . ':';
        if ($diff_sec < 10) {
            $timestring .= '0';
        }
        $timestring .= $diff_sec;

        return $timestring;
    }
}
