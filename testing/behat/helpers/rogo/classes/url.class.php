<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

namespace testing\behat\helpers\rogo;

/**
 * Helpers for generating URLs to Rogo pages for use in Behat.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @package testing
 * @subpackage behat
 */
class Url
{
    /**
     * Generates the URL to a user profile page.
     *
     * @param int $userid The id of the user the page is for.
     * @param string $tab Optional tab the page should be on.
     * @return string
     */
    public static function userProfile(int $userid, string $tab = ''): string
    {
        switch ($tab) {
            case 'Accessibility':
                $sectionparam = '&tab=accessibility';
                break;
            case 'Admin':
                $sectionparam = '&tab=admin';
                break;
            case 'Metadata':
                $sectionparam = '&tab=metadata';
                break;
            case 'Modules':
                $sectionparam = '&tab=moduless';
                break;
            case 'Notes':
                $sectionparam = '&tab=notes';
                break;
            case 'Roles':
                $sectionparam = '&tab=roles';
                break;
            case 'Teams':
                $sectionparam = '&tab=teams';
                break;
            default:
                $sectionparam = '';
                break;
        }
        return "/users/details.php?userID={$userid}{$sectionparam}";
    }
}
