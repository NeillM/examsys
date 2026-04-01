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

namespace testing\datagenerator;

/**
 * Generates standard setting records for papers.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 * @package testing
 * @subpackage datagenerator
 */
class standard_setting extends generator
{
    /**
     * Creates a standard setting entry.
     *
     * Parameters:
     * - std_set: The date and time the standard setting was created (interpreted by \DateTime)
     * - method: The type of standard setting (Modified Angoff, Angoff (Yes/No), Ebel, Hofstee)
     * - group_review
     * - pass_score: The calculated pass score for the standard setting method.
     * - distinction_score: The calculated distinction score for the standard setting method.
     *
     * @param int $paper
     * @param int $user
     * @param array $parameters
     * @return array
     * @throws \DateMalformedStringException
     */
    public function createStandatdSetting(
        int $paper,
        int $user,
        array $parameters,
    ): array {
        $defaults = [
            'std_set' => 'now',
            'method' => 'Ebel',
            'group_review' => '',
            'pass_score' => 40,
            'distinction_score' => 70,
        ];
        $values = $this->set_defaults_and_clean($defaults, $parameters);

        // We want to ensure that the standard setting date is in the correct form.
        $date = new \DateTime($values['std_set']);
        $values['std_set'] = $date->format('Y-m-d H:i:s');

        $values['id'] = \StandardSetting::new_std_set(
            $paper,
            $user,
            $values['std_set'],
            $values['method'],
            $values['group_review'],
            $values['pass_score'],
            $values['distinction_score']
        );

        return $values;
    }
}
