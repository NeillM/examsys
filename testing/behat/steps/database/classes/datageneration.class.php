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

namespace testing\behat\steps\database;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use getID3;
use media_handler;
use module_utils;
use PaperUtils;
use questiondata;
use rogo_directory;
use StudentNotes;
use testing\behat\helpers\database\state;
use testing\datagenerator\Anomaly;
use testing\datagenerator\data_error;
use UserUtils;

/**
 * Core steps that add data into the ExamSys database.
 *
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait datageneration
{
    /**
     * Maps the types that can be passed to exist to a data generator.
     *
     * The values should be an array where:
     * the first value is the name of the data generator,
     * the second value should be the data generator's component,
     * the third value is the function that should be called to create the data.
     * the forth value is the pre process function run before the data generation
     *
     * @var array
     */
    protected $datagenerator_map = [
        'users' => ['users', 'core', 'create_user', null],
        'papers' => ['papers', 'core', 'create_paper', 'preProcessPaper'],
        'questions' => ['questions', 'core', 'create_question', 'preProcessQuestion'],
        'modules' => ['modules', 'core', 'create_module', null],
        'academic year' => ['academic_year', 'core', 'create_academic_year', null],
        'module team members' => ['modules', 'core', 'create_module_team', null],
        'config' => ['config', 'core', 'change_setting', null],
        'campuses' => ['labs', 'core', 'create_campus', null],
        'labs' => ['labs', 'core', 'create_lab', null],
        'exam pcs' => ['labs', 'core', 'create_exam_pc', 'preProcessPC'],
        'module keywords' => ['modules', 'core', 'createModuleKeywords', null],
        'module enrolment' => ['modules', 'core', 'create_enrolment', 'preProcessmoduleEnrolment'],
        'paper note' => ['users', 'core', 'addPaperNote', 'preProcessPaperNote'],
        'access audit' => ['audit', 'core', 'create', null],
        'courses' => ['course', 'core', 'create_course', null],
        'reviewers' => ['papers', 'core', 'addReviewer', null],
        'schedule' => ['papers', 'core', 'schedule', null],
        'anomaly' => ['anomaly', 'core', 'createAnomaly', 'preProcessAnomaly'],
    ];

    /**
     * Will return an array that contains only the indicies from $defaults,
     * using the values from $paramterts if they are set.
     *
     * @param array $defaults Contains all the indicies that should be returned along their their default values.
     * @param array $parameters The values that should over write the defaults.
     * @return array
     */
    protected function set_defaults_and_clean(array $defaults, array $parameters)
    {
        $return = array_merge($defaults, $parameters);
        return array_intersect_key($return, $defaults);
    }

    /**
     * Generate a blank option string
     * @param array $row the question data
     * @return string
     */
    private function getBlankOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        $defaults = [
            'blanks' => '',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $defaultoption = [
            'option_text' => $row['blanks'],
            'marks_correct' => $row['marks_correct'],
            'marks_incorrect' => $row['marks_incorrect'],
            'marks_partial' => $row['marks_partial'],
        ];

        $options = (object)array_merge($defaultoption, $values);

        return $this->encodeOptions($options);
    }

    /**
     * Generate a textbox option string
     * @param array $row the question data
     * @return string
     */
    private function getTextBoxOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        $defaults = [
            'correct' => 'placeholder',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $defaultoption = [
            'correct' => $row['correct'],
            'marks_correct' => $row['marks_correct'],
            'marks_incorrect' => $row['marks_incorrect'],
            'marks_partial' => $row['marks_partial'],
        ];

        $options = (object)array_merge($defaultoption, $values);

        return $this->encodeOptions($options);
    }

    /**
     * Generate a true_false option string
     * @param array $row the question data
     * @return string
     */
    private function getTrueFalseOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        if ($row['correct'] == 'false') {
            $row['correct'] = 'f';
        } else {
            $row['correct'] = 't';
        }

        $defaults = [
            'correct' => 't',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $defaultoption = [
            'correct' => $row['correct'],
            'marks_correct' => $row['marks_correct'],
            'marks_incorrect' => $row['marks_incorrect'],
            'marks_partial' => $row['marks_partial'],
        ];

        $options = (object)array_merge($defaultoption, $values);

        return $this->encodeOptions($options);
    }

    /**
     * Generate a mcq option string
     * @param array $row the question data
     * @return string
     */
    private function getMCQOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        $defaults = [
            'num_options' => 2,
            'correct' => 1,
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        for ($i = 0; $i < $row['num_options']; $i++) {
            $needle = $i + 1;
            $defaultoption = [
                'option_text' => "option {$needle}",
                'correct' => $row['correct'],
                'marks_correct' => $row['marks_correct'],
                'marks_incorrect' => $row['marks_incorrect'],
                'marks_partial' => $row['marks_partial'],
            ];
            $values[$i] = (object)array_merge($defaultoption, $values[$i] ?? []);
        }

        return $this->encodeOptions($values);
    }

    /**
     * Generate a mrq / dichotomous option string
     * @param array $row the question data
     * @return string
     */
    private function getMRQOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        $defaults = [
            'num_options' => 2,
            'type' => 'dichotomous',
            'correct_options' => '1',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $dichotomous = ($row['type'] === 'dichotomous');

        for ($i = 0; $i < $row['num_options']; $i++) {
            $needle = $i + 1;

            if (array_search($needle, explode(',', $row['correct_options'])) !== false) {
                $correct = ($dichotomous) ? 't' : 'y';
            } else {
                $correct = ($dichotomous) ? 'f' : 'n';
            }

            $defaultoption = [
                'option_text' => "option {$needle}",
                'correct' => $correct,
                'marks_correct' => $row['marks_correct'],
                'marks_incorrect' => $row['marks_incorrect'],
                'marks_partial' => $row['marks_partial'],
            ];

            $values[$i] = (object)array_merge($defaultoption, $values[$i] ?? []);
        }

        return $this->encodeOptions($values);
    }

    /**
     * Generate a rank option string
     * @param array $row the question data
     * @return string
     */
    private function getRankOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        $defaults = [
            'num_options' => 3,
            'correct_order' => '1,2,3',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $correct = explode(',', $row['correct_order']);

        for ($i = 0; $i < $row['num_options']; $i++) {
            $needle = $i + 1;
            $defaultoption = [
                'option_text' => "option {$needle}",
                'correct' => $correct[$i],
                'marks_correct' => $row['marks_correct'],
                'marks_incorrect' => $row['marks_incorrect'],
                'marks_partial' => $row['marks_partial'],
            ];
            $values[$i] = (object)array_merge($defaultoption, $values[$i] ?? []);
        }

        return $this->encodeOptions($values);
    }

    /**
     * Generate a sct option string
     * @param array $row the question data
     * @return string
     */
    private function getSCTOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        $defaults = [
            'experts' => '1,2,10,3,1',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $correct = explode(',', $row['experts']);

        $text = [
            'very unlikely',
            'unlikely',
            'neither likely nor unlikely',
            'more likely',
            'very likely'
        ];

        for ($i = 0; $i < count($text); $i++) {
            $defaultoption = [
                'option_text' => $text[$i],
                'correct' => $correct[$i],
                'marks_correct' => $row['marks_correct'],
                'marks_incorrect' => $row['marks_incorrect'],
                'marks_partial' => $row['marks_partial'],
            ];
            $values[$i] = (object)array_merge($defaultoption, $values[$i] ?? []);
        }

        return $this->encodeOptions($values);
    }

    /**
     * Generate a extmatch/matrix option string
     * @param array $row the question data
     * @return string
     */
    private function getExtMatchOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        $defaults = [
            'num_options' => 3,
            'correct_options' => '3,1,2',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $correct = str_replace(',', '|', $row['correct_options']) . '|';
        // Pad out pipes to max.
        $count = substr_count($correct, '|');
        if ($count < 9) {
            $append = str_repeat('|', 9 - $count);
            $correct .= $append;
        }

        for ($i = 0; $i < $row['num_options']; $i++) {
            $needle = $i + 1;
            $defaultoption = [
                'option_text' => "option {$needle}",
                'correct' => $correct,
                'marks_correct' => $row['marks_correct'],
                'marks_incorrect' => $row['marks_incorrect'],
                'marks_partial' => $row['marks_partial'],
            ];
            $values[$i] = (object)array_merge($defaultoption, $values[$i] ?? []);
        }

        return $this->encodeOptions($values);
    }

    /**
     * Generates the options for a generic question.
     *
     * @param array $row
     * @return string
     */
    private function getQuestionOptions(array $row): string
    {
        $values = $this->decodeOptions($row);

        $defaults = [
            'correct' => '',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
            'feedback_right' => '',
            'feedback_wrong' => '',
            'option_text' => '',
        ];

        $row = $this->set_defaults_and_clean($defaults, $row);

        foreach ($values as $key => $value) {
            $optiondefaults = [
                'correct' => $row['correct'],
                'marks_correct' => $row['marks_correct'],
                'marks_incorrect' => $row['marks_incorrect'],
                'marks_partial' => $row['marks_partial'],
                'feedback_right' => $row['feedback_right'],
                'feedback_wrong' => $row['feedback_wrong'],
                'option_text' => $row['option_text'],
            ];
            $value = $this->set_defaults_and_clean($optiondefaults, $value);
            $values[$key] = (object)$value;
        }

        if (empty($values)) {
            $values = (object) $row;
        }

        return $this->encodeOptions($values);
    }

    /**
     * Decodes the options passed to a question generator.
     *
     * @param $row
     * @return array
     */
    private function decodeOptions($row): array
    {
        if (!empty($row['options'])) {
            $values = json_decode($row['options'], true);
        } else {
            $values = [];
        }

        return $values;
    }

    /**
     * Encodes the options in a way that will be understood by the data generator.
     *
     * @param mixed $options
     * @return string
     */
    private function encodeOptions($options): string
    {
        return json_encode($options);
    }

    /**
     * Generate a calc settings string
     * @param array $row the question data
     * @return string
     */
    private function getCalcSettings(array $row): string
    {
        if (!empty($row['settings'])) {
            $values = json_decode($row['settings'], true);
        } else {
            $values = [];
        }

        $defaults = [
            'tolerance_full' => '0',
            'tolerance_partial' => '0',
            'variables' => '{}',
            'dp' => '0',
            'strictdisplay' => true,
            'strictzeros' => false,
            'fulltoltyp' => '#',
            'parttoltyp' => '#',
            'marks_unit' => 0,
            'show_units' => true,
            'formula' => '',
            'formula_units' => '',
            'marks_correct' => 1,
            'marks_incorrect' => 0,
            'marks_partial' => 0,
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $defaultanswers = [
            'formula' => $row['formula'],
            'units' => $row['formula_units'],
        ];

        $defaultoption = [
            'tolerance_full' => $row['tolerance_full'],
            'tolerance_partial' => $row['tolerance_partial'],
            // We need to decode any value sent to that it is not double encoded.
            'vars' => json_decode($row['variables']) ?? $row['variables'],
            'marks_correct' => floatval($row['marks_correct']),
            'marks_incorrect' => floatval($row['marks_incorrect']),
            'marks_partial' => floatval($row['marks_partial']),
            'dp' => $row['dp'],
            'strictdisplay' => filter_var($row['strictdisplay'], FILTER_VALIDATE_BOOLEAN),
            'strictzeros' => filter_var($row['strictzeros'], FILTER_VALIDATE_BOOLEAN),
            'fulltoltyp' => $row['fulltoltyp'],
            'parttoltyp' => $row['parttoltyp'],
            'marks_unit' => floatval($row['marks_unit']),
            'show_units' => filter_var($row['show_units'], FILTER_VALIDATE_BOOLEAN),
            'answers' => [(object)$defaultanswers],
        ];

        if (!empty($values['answers']) && is_array($values['answers'])) {
            foreach ($values['answers'] as $key => $answer) {
                if (is_array($answer)) {
                    // Covert the answers back into objects.
                    $values['answers'][$key] = (object)$values['answers'][$key];
                }
            }
        }

        $options = (object)array_merge($defaultoption, $values);

        return json_encode($options);
    }

    /**
     * Generate a textbox settings string
     * @param array $row the question data
     * @return string
     */
    private function getTextBoxSettings(array $row): string
    {
        if (!empty($row['settings'])) {
            $values = json_decode($row['settings'], true);
        } else {
            $values = [];
        }

        $defaults = [
            'columns' => 80,
            'rows' => 4,
            'editor' => 'Plain Text',
            'terms' => [],
        ];
        $row = $this->set_defaults_and_clean($defaults, $row);

        $defaultoption = [
            'columns' => $row['columns'],
            'rows' => $row['rows'],
            'editor' => $row['editor'],
            'terms' => $row['terms'],
        ];

        $options = (object)array_merge($defaultoption, $values);
        // Terms are json encoded.
        $options->terms = json_encode($options->terms);

        return json_encode($options);
    }

    /**
     * Puts assets into the Media directory.
     *
     * @param string $path The path in the assets directory of the resource.
     * @param string $prefix The prefix for the asset information in it's array.
     * @return array Information about the media.
     */
    protected function set_media(string $path, string $prefix = 'q_'): array
    {
        $mediadirectory = rogo_directory::get_directory('media');

        // Get information about the file to be copied.
        $explodedpath = explode('/', $path);
        $filename = array_pop($explodedpath);
        $ext = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filelocation = $this->getAssetPath() . $path;

        // Calculate where we should save it.
        $uniquename = media_handler::unique_filename($filename);
        $fullpath = $mediadirectory->fullpath($uniquename);

        // Create the file in the media directory.
        copy($filelocation, $fullpath);
        chmod($fullpath, 0664);

        $filetype = media_handler::SUPPORTED[$ext];
        [$width, $height, $problem] = media_handler::getFileInfo($fullpath, $filetype);

        return [
            $prefix . 'media' => $uniquename,
            $prefix . 'media_width' => $width,
            $prefix . 'media_height' => $height,
        ];
    }

    /**
     * Adds media to an option if needed.
     *
     * @param array $option
     * @return array
     */
    protected function processOptionMedia(array $option): array
    {
        if (!empty($option['media'])) {
            $option = array_merge($option, $this->set_media($option['media'], 'o_'));
        }
        return $option;
    }

    /**
     * Preprocess question data
     * @param array $row question data row
     * @return array
     */
    private function preProcessQuestion(array $row): array
    {
        // Defaults.
        if (empty($row['marks_correct'])) {
            $row['marks_correct'] = 1;
        }
        if (empty($row['marks_incorrect'])) {
            $row['marks_incorrect'] = 0;
        }
        if (empty($row['marks_partial'])) {
            $row['marks_partial'] = 0;
        }
        // Upload any media.
        if (!empty($row['media'])) {
            $row = array_merge($row, $this->set_media($row['media']));
        }
        // Force display method for sct.
        if ($row['type'] === 'sct') {
            $row['display_method'] = 1;

            // Optional new information needs to be added to the leadin.
            if (isset($row['newinfo'])) {
                $row['leadin'] .= '~' . $row['newinfo'];
            }

            // The SCT leadin is special. and contains multiple pices of information separated by a ~
            // The second part is for new information.
            if (!str_contains($row['leadin'], '~')) {
                $row['leadin'] .= '~';
            }
        }
        // Generate option json.
        switch ($row['type']) {
            case 'blank':
                $row['options'] = $this->getBlankOptions($row);
                break;
            case 'textbox':
                $row['options'] = $this->getTextBoxOptions($row);
                break;
            case 'true_false':
                $row['options'] = $this->getTrueFalseOptions($row);
                break;
            case 'mcq':
                $row['options'] = $this->getMCQOptions($row);
                break;
            case 'dichotomous':
            case 'mrq':
                $row['options'] = $this->getMRQOptions($row);
                break;
            case 'rank':
                $row['options'] = $this->getRankOptions($row);
                break;
            case 'sct':
                $row['options'] = $this->getSCTOptions($row);
                break;
            case 'matrix':
            case 'extmatch':
                $row['options'] = $this->getExtMatchOptions($row);
                break;
            case 'likert':
                $row['display_method'] = $this->getLikertDisplayMethod($row);
                $row['options'] = $this->getQuestionOptions($row);
                break;
            default:
                $row['options'] = $this->getQuestionOptions($row);
                break;
        }
        // Generate scenario.
        if ($row['type'] === 'extmatch' or $row['type'] === 'matrix') {
            $row['scenario'] = '';
            for ($i = 0; $i < $row['num_stems']; $i++) {
                $needle = $i + 1;
                $row['scenario'] .= 'stem ' . $needle . '|';
            }
        }
        // Generate settings json.
        switch ($row['type']) {
            case 'textbox':
                $row['settings'] = $this->getTextBoxSettings($row);
                break;
            case 'enhancedcalc':
                $row['settings'] = $this->getCalcSettings($row);
                break;
            default:
                break;
        }

        if (!empty($row['options'])) {
            // Options should be json encoded.
            $options = json_decode($row['options'], false);
            if (is_array($options)) {
                foreach ($options as $key => $option) {
                    $options[$key] = (object)$this->processOptionMedia((array)$option);
                }
            } else {
                $options = (object)$this->processOptionMedia((array)$options);
            }
            // Save the options again.
            $row['options'] = json_encode($options);
        }

        // Generate paper json if assigned to a paper.
        if (isset($row['paper'])) {
            $row['paper'] = '{"paper":"' . $row['paper'] . '","screen":"' . $row['screen'] . '","displaypos":"' . $row['position'] . '"}';
        }
        return $row;
    }

    /**
     * Generates the display method string for Likert questions.
     *
     * @param array $row
     * @return string
     * @throws data_error
     */
    protected function getLikertDisplayMethod(array $row): string
    {
        // Default to a 4 Point scale if none is specified.
        $scale_type = $row['scale type'] ?? '4 Point Scales';

        switch ($scale_type) {
            case 'OSCE Station Scales':
                $display_options = $this->getScaleStringOSCE($row);
                break;
            case '3 Point Scales':
                $display_options = $this->getScaleString3Point($row);
                break;
            case '4 Point Scales':
                $display_options = $this->getScaleString4Point($row);
                break;
            case '5 Point Scales':
                $display_options = $this->getScaleString5Point($row);
                break;
            case 'Custom':
                // A valid scale was used.
                $display_options = $this->getScaleStringCustom($row);
                break;
            default:
                // An invalid scale was specified.
                $message = "scale type of '$scale_type' is not supported, use OSCE Station Scales, "
                    . '3 Point Scales, 4 Point Scales, 5 Point Scales, or Custom';
                throw new data_error($message);
        }

        // Then add the data for N/A being displayed.
        $na = $row['not applicable'] ?? 'false';
        if ($na === 'true') {
            $display_options .= '|true';
        } else {
            $display_options .= '|false';
        }
        return $display_options;
    }

    /**
     * Gets the display method for supported OSCE scales.
     *
     * @param array $row
     * @return string
     * @throws data_error
     */
    protected function getScaleStringOSCE(array $row): string
    {
        $scale = $row['scale'] ?? '0, 1, 2, 3';
        return match ($scale) {
            '0, 1' => '0|1',
            '0, 1, 2' => '0|1|2',
            '0, 1, 2, 3' => '0|1|2|3',
            '0, 1, 2, 3, 4' => '0|1|2|3|4',
            '0, 1, 2, 3, 4, 5' => '0|1|2|3|4|5',
            default => throw new data_error("An unsupported OSCE scale was specified: $scale"),
        };
    }

    /**
     * Gets the display method for supported 3 point scales.
     *
     * @param array $row
     * @return string
     * @throws data_error
     */
    protected function getScaleString3Point(array $row): string
    {
        $scale = $row['scale'] ?? 'Low to High';
        return match ($scale) {
            'Low to High' => 'Low|Medium|High',
            'Never to Always' => 'Never|Sometimes|Always',
            'Disagree, Neutral, Agree' => 'Disagree|Neutral|Agree',
            default => throw new data_error("An unsupported 3 Point scale was specified: $scale"),
        };
    }


    /**
     * Gets the display method for supported 4 point scales.
     *
     * @param array $row
     * @return string
     * @throws data_error
     */
    protected function getScaleString4Point(array $row): string
    {
        $scale = $row['scale'] ?? 'Low to High';
        return match ($scale) {
            'Low to High' => 'Low|Tending Low|Tending High|High',
            'Never to Always' => 'Never|Hardly|Sometimes|Always',
            'Strongly Disagree, Disagree, Agree, Strongly Agree' => 'Strongly Disagree|Disagree|Agree|Strongly Agree',
            default => throw new data_error("An unsupported 4 Point scale was specified: $scale"),
        };
    }

    /**
     * Gets the display method for supported 5 point scales.
     *
     * @param array $row
     * @return string
     * @throws data_error
     */
    protected function getScaleString5Point(array $row): string
    {
        $scale = $row['scale'] ?? 'Low to High';
        return match ($scale) {
            'Low to High' => 'Low|Tending Low|Medium|Tending High|High',
            'Never to Always' => 'Never|Hardly|Occasionally|Sometimes|Always',
            'Strongly Disagree, Disagree, Neither Disagree nor Agree, Agree, Strongly Agree' => 'Strongly Disagree|Disagree|Neither Disagree nor Agree|Agree|Strongly Agree',
            'Strongly Disagree, Disagree, Uncertain, Agree, Strongly Agree' => 'Strongly Disagree|Disagree|Uncertain|Agree|Strongly Agree',
            'Strongly Disagree, Disagree, Neutral, Agree, Strongly Agree' => 'Strongly Disagree|Disagree|Neutral|Agree|Strongly Agree',
            default => throw new data_error("An unsupported 5 Point scale was specified: $scale"),
        };
    }

    /**
     * Gets the display method for custom scales.
     *
     * If the scale contains $$ we will assume that the scales entries are split by those character,
     * if no $$ is present we will split on space.
     *
     * Scale entries must contain text other than spaces.
     *
     * @param array $row
     * @return string
     * @throws data_error
     */
    protected function getScaleStringCustom(array $row): string
    {
        $scale = $row['scale'] ?? 'My custom scale';

        if (str_contains($scale, '$$')) {
            $scales = explode('$$', $scale);
        } else {
            $scales = explode(' ', $scale);
        }

        // Discard any prt that does not contain characters.
        $validscales = [];
        foreach ($scales as $item) {
            $trimmeditem = trim($item);
            if ($trimmeditem !== '') {
                $validscales[] = $trimmeditem;
            }
        }

        return implode('|', $validscales);
    }

    /**
     * Preprocess paper data
     * @param array $row paper data row
     * @return array
     */
    private function preProcessPaper(array $row): array
    {
        // Get paper type.
        $types = \PaperUtils::getTypeList();
        $row['papertype'] = $types[$row['type']];
        $row['settings'] = '';
        if ($row['type'] === 'osce') {
            // Generate a default marking value for OSCE papers.
            $row['marking'] ??= 'N/A';

            // Generate settings json.
            $marking = match ($row['marking']) {
                'Pass | Fail' => 7,
                'Clear FAIL | BORDERLINE | Clear PASS | Honours PASS' => 6,
                'N/A' => 5,
                'Fail | Borderline fail | Borderline pass | Pass | Good pass' => 4,
                'Clear Fail | Borderline | Clear Pass' => 3,
                default => 5,
            };
            $row['settings'] = '{"marking":"' . $marking . '"}';
        } else {
            $settings = [];
            if (!empty($row['external_review_deadline'])) {
                $settings['external_review_deadline'] = $row['external_review_deadline'];
            }
            if (!empty($row['password'])) {
                $settings['password'] = $row['password'];
            }
            $row['settings']  = json_encode($settings);
        }

        // Convert Labs into database ids.
        $helper = new \LabFactory(state::get_db());
        if (isset($row['labs'])) {
            $labs = explode(',', $row['labs']);
            $labids = [];
            foreach ($labs as $lab) {
                $labid = $helper->get_lab_id($lab);
                if ($labid !== false) {
                    $labids[] = $labid;
                }
            }
            $row['labs'] = implode(',', $labids);
        }

        return $row;
    }

    /**
     * Enrol a student onto a module
     *
     * @param array $row module enrolment data row
     * @return array
     * @throws data_error
     */
    private function preProcessmoduleEnrolment(array $row): array
    {
        if (empty($row['modulecode'])) {
            throw new data_error('modulecode must be provided');
        }
        if (empty($row['sid'])) {
            throw new data_error('sid must be provided');
        }
        if (empty($row['attempt'])) {
            $row['attempt'] = 1;
        }
        if (empty($row['calendar_year'])) {
            $row['calendar_year'] = null;
        }
        if (empty($row['auto_update'])) {
            $row['auto_update'] = 0;
        }
        $row['userid'] = UserUtils::studentid_exists($row['sid'], state::get_db());
        $row['moduleid'] = module_utils::get_idMod($row['modulecode'], state::get_db());
        unset($row['sid']);
        unset($row['modulecode']);
        return $row;
    }

    /**
     * Add a student note to a paper
     *
     * @param array $row paper note data row
     * @return array
     * @throws data_error
     */
    private function preProcessPaperNote(array $row): array
    {
        if (empty($row['user'])) {
            throw new data_error('user must be provided');
        }
        if (empty($row['paper'])) {
            throw new data_error('paper must be provided');
        }
        if (empty($row['author'])) {
            throw new data_error('author must be provided');
        }

        if (empty($row['note'])) {
            $row['note'] = '';
        }
        $row['userID'] = UserUtils::username_exists($row['user'], state::get_db());
        $row['paperID'] = PaperUtils::getPaperId($row['paper']);
        $row['authorID'] = UserUtils::username_exists($row['author'], state::get_db());
        $existingnote = StudentNotes::get_note($row['paperID'], $row['userID'], state::get_db());
        if ($existingnote === false) {
            $row['noteID'] = 0;
        } else {
            $row['noteID'] = $existingnote['note_id'];
        }
        unset($row['user']);
        unset($row['paper']);
        unset($row['author']);
        return $row;
    }

    /**
     * Add an anomaly to a paper
     *
     * @param array $row paper anomaly data row
     * @return array
     * @throws data_error
     */
    private function preProcessAnomaly(array $row): array
    {
        if (empty($row['user'])) {
            throw new data_error('user must be provided');
        }
        if (empty($row['paper'])) {
            throw new data_error('paper must be provided');
        }
        if (empty($row['type'])) {
            throw new data_error('paper must be provided');
        }
        if (!empty($row['time'])) {
            $timestamp = new \DateTime($row['time']);
            $row['time'] = $timestamp->getTimestamp();
        }
        $row['userid'] = UserUtils::username_exists($row['user'], state::get_db());
        $row['paperid'] = PaperUtils::getPaperId($row['paper']);
        if ($row['type'] === 'clock') {
            $row['type'] = \Anomaly::CLOCK;
        } else {
            throw new data_error('anomaly type unknown');
        }
        unset($row['user']);
        unset($row['paper']);
        return $row;
    }

    /**
     * Ensure that we allow can allow behat to be treated as though it is in a lab.
     *
     * If a lab is defined as behat we should translate it into the address for behat.
     *
     * @param array $row
     * @return array
     */
    protected function preProcessPC(array $row): array
    {
        if (!isset($row['address']) || $row['address'] !== 'behat') {
            // We should do nothing when we are not trying to add a behat PC entry.
            return $row;
        }

        // We will assume that there are two setups for behat:
        // 1. Running on a dev PC directly, i.e. using localhost for access.
        // 2. Using examsys-docker

        $hostname = 'localhost';
        $address = '127.0.0.1';

        $docker_hostname = 'selenium';
        $docker_ip = gethostbyname($docker_hostname);

        if ($docker_ip !== $docker_hostname) {
            // We seem to be running on examsys-docker.
            $address = $docker_ip;
            // We want the fully qualified hostname.
            $hostname = gethostbyaddr($docker_ip);
        }

        // We need to handle ExamSys being configured for both IP address labs and hostname labs.
        $config = \Config::get_instance();

        if ($config->get_setting('core', 'system_hostname_lookup')) {
            $address = $hostname;
        }

        $row['address'] = $address;
        $row['hostname'] = $hostname;

        return $row;
    }

    /**
     * Adds records to the database using an appropriate data generator.
     *
     * @Given /^the following "([^"]*)" exist:$/
     * @param string $type The type of data to be generated. It must appear in self::$datagenerator_map
     * @param TableNode $data The data to be loaded
     * @throws PendingException if the type is not mapped, or the generator function does not exist
     */
    public function the_following_exist($type, TableNode $data)
    {
        if (!isset($this->datagenerator_map[$type])) {
            // The type has not yet been mapped.
            // We should let the user know that it needs to be implemented.
            throw new PendingException("Implement a data generator for: $type");
        }
        $generatorname = $this->datagenerator_map[$type][0];
        $generatorcomponent = $this->datagenerator_map[$type][1];
        $createmethod = $this->datagenerator_map[$type][2];
        $datagenerator = $this->get_datagenerator($generatorname, $generatorcomponent);
        // Check the creation method exists.
        if (!method_exists($datagenerator, $createmethod)) {
            $message = "Implement the {$createmethod} method in the "
                . "{$generatorcomponent}_{$generatorname} data generator";
            throw new PendingException($message);
        }
        // Get the preprocess function.
        $preprocess = $this->datagenerator_map[$type][3];
        // Convert the data into a form that the data generator can use.
        foreach ($data->getHash() as $row) {
            // Preprocess each row.
            if (!is_null($preprocess)) {
                $row = $this->$preprocess($row);
            }
            // Pass each row into the generator.
            $datagenerator->$createmethod($row);
        }
    }
}
