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
 * Formats logger fields for the paper changes page.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */
class PaperChangesFormatter
{
    /** @var Config The ExamSys config object. */
    protected Config $config;

    /** @var array List of folders that have been associated with a paper, the key is the id of the folder. */
    protected array $changed_folders = [];

    /** @var array List of labs involved in a paper, the key is the id of the lab. */
    protected array $changed_labs = [];

    /** @var array List of reference material that has been attached and detached from a paper, the key is the id of the reference material. */
    protected array $changed_reference_material = [];

    /** @var array List of users who have reviewers who have reviewed a paper, the keys is the id of the user. */
    protected array $changed_reviewers = [];

    /** @var string[] Cached list of folders. */
    protected array $folders;

    /** @var array Cached list of named labs. */
    protected array $labs;

    /** @var string[] Cached list of user details (based on the changed_reviewers) */
    protected array $user_list;

    /** @var string[] Cached list of reference material. */
    protected array $reference_material;

    /**
     * The constructor.
     *
     * @param string[] $string Language strings for the page.
     */
    public function __construct(
        protected array $string,
    ) {
        $this->config = Config::get_instance();
    }

    /**
     * A list of callbacks to use in {@see Logger::get_changes()}
     *
     * @return array[]
     */
    public function getLoggerCallbacks(): array
    {
        return [
            'externals' => [$this, 'callbackReviewer'],
            'folder' => [$this, 'callbackFolder'],
            'internals' => [$this, 'callbackReviewer'],
            'labs' => [$this, 'callbackLab'],
            'referencematerial' => [$this, 'callbackReferenceMaterial'],
        ];
    }

    /**
     * Adds folders to a list for later use.
     *
     * @param string $old
     * @param string $new
     * @return void
     */
    public function callbackFolder($old, $new): void
    {
        if ($old != '') {
            $this->changed_folders[$old] = false;
        }
        if ($new != '') {
            $this->changed_folders[$new] = false;
        }
    }

    /**
     * Adds reference material to a list for later use.
     *
     * @param string $old
     * @param string $new
     * @return void
     */
    public function callbackReferenceMaterial($old, $new): void
    {
        if ($old != '') {
            $this->changed_reference_material[$old] = false;
        }
        if ($new != '') {
            $this->changed_reference_material[$new] = false;
        }
    }

    /**
     * Adds reviewers to a list for later use.
     *
     * @param string $old The old value
     * @param string $new The new value
     * @return void
     */
    public function callbackReviewer($old, $new): void
    {
        $old_reviewers = explode(',', $old);
        $new_reviewers = explode(',', $new);

        // Add any reviewers in the current change to the $changed_reviewers array
        foreach ($old_reviewers as $reviewer) {
            if ($reviewer != '') {
                $this->changed_reviewers[$reviewer] = false;
            }
        }
        foreach ($new_reviewers as $reviewer) {
            if ($reviewer != '') {
                $this->changed_reviewers[$reviewer] = false;
            }
        }
    }

    /**
     * Adds labs to a list for later use.
     *
     * @param string $old The old value
     * @param string $new The new value.
     * @return void
     */
    public function callbackLab($old, $new): void
    {
        $old_labs = explode(',', $old);
        $new_labs = explode(',', $new);

        // Add any labs in the current change to the $changed_labs array
        foreach ($old_labs as $lab) {
            if ($lab != '') {
                $this->changed_labs[$lab] = false;
            }
        }
        foreach ($new_labs as $lab) {
            if ($lab != '') {
                $this->changed_labs[$lab] = false;
            }
        }
    }

    /**
     * Caches folder names.
     *
     * @return void
     */
    protected function getFolders(): void
    {
        if (isset($this->folders)) {
            // We already have the list of folders.
            return;
        }

        if (empty($this->changed_folders)) {
            // No details to fetch.
            return;
        }

        $this->folders = folder_utils::getFolderNames(array_keys($this->changed_folders));
    }

    /**
     * Caches the names of labs that are referenced in changes.
     *
     * @return void
     */
    protected function getLabs(): void
    {
        if (isset($this->labs)) {
            return;
        }
        if (empty($this->changed_labs)) {
            return;
        }

        $in = implode(',', $this->changed_labs);
        $sql = "SELECT id, name FROM labs WHERE id IN($in)";
        $result = $this->config->db->prepare($sql);
        $result->execute();
        $result->bind_result($lab_id, $lab_name);
        while ($result->fetch()) {
            $this->labs[$lab_id] = $lab_name;
        }
    }

    /**
     * Caches the names of users.
     *
     * @return void
     */
    protected function getUserNames(): void
    {
        if (isset($this->user_list)) {
            // We have already retried the user list.
            return;
        }

        $this->user_list = [];
        if (count($this->changed_reviewers) > 0) {
            $reviewer_in = implode(',', array_keys($this->changed_reviewers));

            $sql = "SELECT id, title, surname FROM users WHERE id IN ($reviewer_in)";
            $results = $this->config->db->prepare($sql);
            $results->execute();
            $results->bind_result($id, $title, $surname);

            while ($results->fetch()) {
                $this->user_list[$id] = $title . ' ' . $surname;
            }

            $results->close();
        }
    }

    /**
     * Caches the names of reference material.
     *
     * @return void
     */
    protected function getReferenceMaterial(): void
    {
        if (isset($this->reference_material)) {
            // The reference material has already been cached.
            return;
        }

        if (empty($this->changed_reference_material)) {
            // There is no reference material to get details for.
            return;
        }

        $this->reference_material = [];
        $in = implode(',', array_keys($this->changed_reference_material));
        $sql = "SELECT id, title FROM reference_material WHERE id IN ($in)";
        $results = $this->config->db->prepare($sql);
        $results->execute();
        $results->bind_result($id, $title);
        while ($results->fetch()) {
            $this->reference_material[$id] = $title;
        }
        $results->close();
    }

    /**
     * Formats the old and new values.
     *
     * @param string $type The type of value we are formatting
     * @param mixed $old The old value
     * @param mixed $new The new value
     * @return string[]
     */
    public function format(string $type, mixed $old, mixed $new): array
    {
        switch ($type) {
            case 'startdate':
            case 'enddate':
                $dateformat = $this->config->get('cfg_short_datetime_php');
                $old = date($dateformat, $old);
                $new = date($dateformat, $new);
                break;
            case 'folder':
                $old = $this->formatFolders($old);
                $new = $this->formatFolders($new);
                break;
            case 'method':
                $old = $this->formatMethod($old);
                $new = $this->formatMethod($new);
                break;
            case 'displaycalculator':
            case 'demosoundclip':
            case 'photos':
            case 'ticks_crosses':
            case 'hideallfeedback':
            case 'textfeedback':
            case 'correctanswerhighlight':
            case 'question_marks':
                $old = $this->formatOnOff($old);
                $new = $this->formatOnOff($new);
                break;
            case 'externals':
            case 'internals':
                $old = $this->formatUser($old);
                $new = $this->formatUser($new);
                break;
            case 'background':
            case 'foreground':
            case 'theme':
            case 'labelsnotes':
                $old = $this->formatColor($old);
                $new = $this->formatColor($new);
                break;
            case 'referencematerial':
                $old = $this->formatReferenceMaterial($old);
                $new = $this->formatReferenceMaterial($new);
                break;
            case 'display':
                $old = $this->formatDisplay($old);
                $new = $this->formatDisplay($new);
                break;
            case 'navigation':
                $old = $this->formatNavigation($old);
                $new = $this->formatNavigation($new);
                break;
            case 'review':
                $old = $this->formatReview($old);
                $new = $this->formatReview($new);
                break;
            case 'passmark':
            case 'distinction':
                $old = $this->formatPassmark($old);
                $new = $this->formatPassmark($new);
                break;
            case 'labs':
                $old = $this->formatLab($old);
                $new = $this->formatLab($new);
                break;
            case 'marking':
                $old = $this->formatMarking($old);
                $new = $this->formatMarking($new);
                break;
        }

        return [
            $old,
            $new,
        ];
    }

    /**
     * Formats the value of a colour change.
     *
     * @param string $color
     * @return string
     */
    protected function formatColor(string $color)
    {
        return '<div class="color-change" style="background-color:' . $color . ';" role="figure" aria-label="' . $color . '"></div>';
    }

    /**
     * Format the display method.
     *
     * @param int $data
     * @return string
     */
    protected function formatDisplay($data): string
    {
        if ($data == 0) {
            $string = $this->string['windowed'];
        } else {
            $string = $this->string['fullscreen'];
        }

        return htmlentities(
            string: $string,
            double_encode: false,
        );
    }

    /**
     * Format the value of a folder change.
     *
     * @param string $id
     * @return string
     */
    protected function formatFolders(string $id): string
    {
        if ($id == '') {
            return '';
        }

        $this->getFolders();

        if (isset($this->folders[$id])) {
            $formatted_string = str_replace(';', '/', $this->folders[$id]);
        } else {
            $formatted_string = $id;
        }

        return htmlentities(
            string: $formatted_string,
            double_encode: false,
        );
    }

    /**
     * Format the values of a lab change.
     *
     * @param string $lab_id
     * @return string
     */
    protected function formatLab(string $lab_id): string
    {
        if ($lab_id == '') {
            // No labs were set.
            return '';
        }

        $this->getLabs();

        $labs = [];

        $parts = explode(',', $lab_id);
        foreach ($parts as $part) {
            if (isset($this->labs[$part])) {
                $lab_name = $this->labs[$part];
            } else {
                $lab_name = $this->string['unknown'];
            }
            $labs[] = $lab_name;
        }

        return htmlentities(
            string: implode(', ', $labs),
            double_encode: false,
        );
    }

    /**
     * Format a change to the marking method.
     *
     * @param string $marking
     * @return string
     */
    protected function formatMarking(string $marking): string
    {
        $marking_string = $marking;

        $marking_type = $marking[0];

        $marking_string = match ((string) $marking_type) {
            MARK_NO_ADJUSTMENT => $this->string['noadjustment'],
            MARK_RANDOM => $this->string['calculatrrandommark'],
            MARK_STD_SET => $this->string['stdset'],
            '3' => $this->string['overallclass2'],
            '4' => $this->string['overallclass3'],
            '6' => $this->string['overallclass4'],
            '7' => $this->string['overallclass5'],
            default => $marking_string,
        };

        return htmlentities(
            string: $marking_string,
            double_encode: false,
        );
    }
    /**
     * Format the change to a
     * @param string $method
     * @return string
     */
    protected function formatMethod($method): string
    {
        $string = '';

        if ($method == '0') {
            $string = $this->string['noadjustment'];
        } elseif ($method == '1') {
            $string = $this->string['calculatrrandommark'];
        } elseif ($method[0] == '2') {
            $string = $this->string['stdset'];
        } elseif ($method == '3') {
            $string = $this->string['overallclass2'];
        } elseif ($method == '4') {
            $string = $this->string['overallclass3'];
        } elseif ($method == '5') {
            $string = $this->string['overallclass1'];
        } elseif ($method == '6') {
            $string = $this->string['overallclass4'];
        }

        return htmlentities(
            string: $string,
            double_encode: false,
        );
    }

    /**
     * format the navigation method.
     *
     * @param int $data
     * @return string
     */
    protected function formatNavigation($data): string
    {
        if ($data == 0) {
            $string = $this->string['unidirectional'];
        } else {
            $string = $this->string['bidirectional'];
        }

        return htmlentities(
            string: $string,
            double_encode: false,
        );
    }

    /**
     * Format a boolean value.
     *
     * @param int $data
     * @return string
     */
    protected function formatOnOff($data): string
    {
        if ($data == 0) {
            $string = $this->string['off'];
        } else {
            $string = $this->string['on'];
        }

        return htmlentities(
            string: $string,
            double_encode: false,
        );
    }

    /**
     * Format a change to the passmark setting.
     *
     * @param int $method
     * @return string
     */
    protected function formatPassmark($method): string
    {
        if ($method == 101) {
            $string = $this->string['borderline'];
        } elseif ($method == 102 or $method == 127) {
            $string = $this->string['na'];
        } else {
            $string = $method . '%';
        }

        return htmlentities(
            string: $string,
            double_encode: false,
        );
    }

    /**
     * Formats the value of a reference material change.
     *
     * @param string $ID
     * @return string
     */
    protected function formatReferenceMaterial($ID): string
    {
        if ($ID == '') {
            return '';
        }

        $this->getReferenceMaterial();

        return htmlentities(
            string: $this->reference_material[$ID],
            double_encode: false,
        );
    }

    /**
     * format a change to the review method.
     *
     * @param string $method
     * @return string
     */
    protected function formatReview($method): string
    {
        if ($method == '0') {
            $string = $this->string['singlereview'];
        } else {
            $string = $this->string['allpeerspergroup'];
        }

        return htmlentities(
            string: $string,
            double_encode: false,
        );
    }

    /**
     * Format the values of a change to users associated with the paper.
     *
     * @param string $text
     * @return string
     */
    protected function formatUser($text): string
    {
        if ($text == '') {
            return '';
        }

        $this->getUserNames();

        $users = [];
        $parts = explode(',', (string) $text);
        foreach ($parts as $part) {
            $users[] = $this->user_list[$part];
        }

        return htmlentities(
            string: implode(', ', $users),
            double_encode: false,
        );
    }
}
