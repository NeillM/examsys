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
 * Adds in the locale setting to the calculation settings.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2023 The University of Nottingham
 */

if ($updater_utils->check_version('7.6.0')) {
    if (!$updater_utils->has_updated('rogo_3303')) {
        $setting = $configObject->get_setting('core', 'cfg_calc_settings');
        $setting['locale'] = 'en_GB';
        $configObject->set_setting('cfg_calc_settings', $setting, Config::ASSOC);
        $updater_utils->record_update('rogo_3303');
    }
}
