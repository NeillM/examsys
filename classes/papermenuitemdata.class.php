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
class MenuItemData
{
    private $string;

    public function __construct($configObject, $string)
    {
        $this->configObject = $configObject;
        $this->string = $string;
    }

    // Paper Tasks
    public function getTestPreviewItem($properties)
    {
        if ($properties->get_paper_type() == '5' || $properties->get_item_no() == 0) {
            return [
                'classes' => 'grey menuitem',
                'disabled' => true,
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/small_play_grey.png',
                'text' => $this->string['testpreview'],
                'href' => '#',
                'hasPopup' => false,
                'tabindex' => 0
            ];
        } else {
            return [
                'classes' => 'menuitem startpaper',
                'disabled' => false,
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/small_play.png',
                'text' => $this->string['testpreview'],
                'href' => '#',
                'hasPopup' => false,
                'tabindex' => 0,
                'data_attributes' => [
                    'fullscreen' => $properties->get_fullscreen(),
                    'preview' => '0'
                ]
            ];
        }
    }

    public function getAddQuestionsItem($properties)
    {
        if ($properties->get_summative_lock() == 1) {
            return [
                'classes' => 'grey menuitem',
                'disabled' => true,
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/add_questions_grey.gif',
                'text' => $this->string['addquestionspaper']
            ];
        }

        $max_screen = ($properties->get_max_screen() != '') ? $properties->get_max_screen() : 0;
        return [
            'classes' => 'menuitem addquestions',
            'disabled' => false,
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/add_questions_16.gif',
            'text' => $this->string['addquestionspaper'],
            'href' => '#',
            'tabindex' => 0,
            'data_attributes' => [
                'dispno' => ($properties->get_max_display_pos() + 1),
                'screen' => $max_screen
            ]
        ];
    }

    public function getEditPropertiesItem($paperID, $module, $folder)
    {
        return [
            'classes' => 'menuitem properties',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/properties_icon.gif',
            'text' => $this->string['editproperties'],
            'href' => Config::get_instance()->get('cfg_root_path')
                    . "/paper/properties.php?paperID=$paperID&caller=details&module=$module&folder=$folder",
            'tabindex' => 0
        ];
    }
    public function getEmailExternalsItem($properties)
    {
        // Only proceed if paper type is 2
        if ($properties->get_paper_type() != '2') {
            return null;
        }

        if (is_null($properties->get_external_review_deadline())) {
            return [
                'classes' => 'grey menuitem',
                'id' => 'emailexternalsgrey',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/small_email_grey.png',
                'text' => $this->string['emailexternals'],
                'disabled' => true,
                'role' => 'menuitem'
            ];
        }

        return [
            'classes' => 'menuitem cascade showmenu',
            'id' => 'emailexternals',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/small_email.png',
            'text' => $this->string['emailexternals'],
            'href' => '#',
            'tabindex' => -1,
            'hasPopup' => true,
            'data_attributes' => [
                'popupid' => '1',
                'popuptype' => 'papertasks',
                'popupname' => 'emailexternals'
            ],
            'role' => 'menuitem'
        ];
    }

    public function getReportsItem($properties, $paperID, $module, $folder, $checklist, $graded = false)
    {
        $paperType = $properties->get_paper_type();

        // Types 0,1,2,5,6 with no items - grey disabled version
        if (in_array($paperType, ['0', '1', '2', '5', '6']) && $properties->get_item_no() == 0) {
            return [
                'classes' => 'grey menuitem greycascade',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/statistics_icon_grey.gif',
                'text' => $this->string['reports']
            ];
        }

        // All other cases (including types 3 and 4) - active version
        if (in_array($paperType, ['0', '1', '2', '3', '4', '5', '6'])) {
            return [
                'classes' => 'menuitem cascade stats',
                'id' => 'reports',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/statistics_icon.gif',
                'text' => $this->string['reports'],
                'href' => '#'
            ];
        }

        return null;
    }

    public function getImportOsceMarksItem($paperID, $module, $folder)
    {
        return [
            'classes' => 'menuitem',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/import_16.gif',
            'text' => $this->string['importoscemarks'],
            'href' => Config::get_instance()->get('cfg_root_path')
                    . "/import/osce_marks.php?paperID=$paperID&module=$module&folder=$folder",
            'tabindex' => 0
        ];
    }

    public function getMappedObjectivesItem($properties, $paperID, $module, $folder)
    {
        if ($properties->get_calendar_year() == '') {
            return [
                'classes' => 'greymenuitem',
                'disabled' => true,
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/curriculum_map_small_grey.png',
                'text' => $this->string['mappedobjectives']
            ];
        }

        return [
            'classes' => 'menuitem',
            'text' => $this->string['mappedobjectives'],
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/curriculum_map_small.png',
            'href' => Config::get_instance()->get('cfg_root_path')
                    . "/mapping/paper_by_session.php?paperID=$paperID&paper_title="
                    . $properties->get_paper_title()
                    . '&sd=' . $properties->get_start_date()
                    . '&ed=' . $properties->get_end_date()
                    . "&module=$module&folder=$folder",
        ];
    }

    public function getCopyPaperItem()
    {
        return [
            'classes' => 'menuitem cascade',
            'id' => 'copypaper',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/copy_icon.gif',
            'text' => $this->string['copypaper'],
            'href' => '#',
            'tabindex' => 0,
            'hasPopup' => true,
            'role' => 'menuitem'
        ];
    }

    public function getCopyFromPaperItem($properties)
    {
        if ($properties->get_summative_lock() == 1) {
            return [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/copy_icon_grey.gif',
                'text' => $this->string['copyfrompaper'],
                'disabled' => true,
                'role' => 'menuitem'
            ];
        }

        return [
            'classes' => 'menuitem cascade',
            'id' => 'copyfrompaper',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/copy_icon.gif',
            'text' => $this->string['copyfrompaper'],
            'href' => '#',
            'tabindex' => 0,
            'hasPopup' => true,
            'role' => 'menuitem'
        ];
    }

    public function getDeletePaperItem($properties, $userObject, $configObject)
    {
        if (
            $properties->get_summative_lock() == 1 ||
            ($configObject->get_setting('core', 'cfg_summative_mgmt') &&
            $properties->get_paper_type() == '2' &&
            !$userObject->has_role(array('Admin', 'SysAdmin')))
        ) {
            return [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/delete_paper_grey_16.gif',
                'text' => $this->string['deletepaper'],
                'disabled' => true,
                'role' => 'menuitem'
            ];
        }

        return [
            'classes' => 'menuitem deletepaper',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/delete_paper_16.gif',
            'text' => $this->string['deletepaper'],
            'href' => '#',
            'tabindex' => 0,
            'role' => 'menuitem'
        ];
    }

    public function getRetirePaperItem()
    {
        return [
            'classes' => 'menuitem retirepaper',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/retire_16.png',
            'text' => $this->string['retirepaper'],
            'href' => '#',
            'tabindex' => 0,
            'role' => 'menuitem'
        ];
    }

    public function getPrintHardcopyItem($properties, $paperID)
    {
        if ($properties->get_item_no() == 0) {
            return [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/print_icon_16_disabled.png',
                'text' => $this->string['printhardcopy'],
                'disabled' => true,
                'role' => 'menuitem'
            ];
        }

        if ($properties->get_paper_type() == '4') {
            return [
                'classes' => 'menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/print_icon_16.png',
                'text' => $this->string['printhardcopy'],
                'href' => Config::get_instance()->get('cfg_root_path') . "/osce/print.php?paperID=$paperID",
                'tabindex' => 0,
                'role' => 'menuitem'
            ];
        }

        return [
            'classes' => 'menuitem cascade showmenu',
            'id' => 'hardcopy',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/print_icon_16.png',
            'text' => $this->string['printhardcopy'],
            'href' => '#',
            'tabindex' => 0,
            'hasPopup' => true,
            'data_attributes' => [
                'popupid' => '3',
                'popuptype' => 'papertasks',
                'popupname' => 'hardcopy'
            ],
            'role' => 'menuitem'
        ];
    }

    public function getImportExportItem($properties, $paperID, $module)
    {
        return [
            'text' => $this->string['importexport'],
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/ims_16.png',
            'href' => '#',
            'classes' => 'menuitem cascade showmenu',
            'id' => 'qti',
            'disabled' => false,
            'hasPopup' => true,
            'tabindex' => 0,
            'data_attributes' => [
                'popupid' => '2',
                'popuptype' => 'papertasks',
                'popupname' => 'qti'
            ]
        ];
    }

    public function getStudentCohortItem($properties)
    {
    // Only return if calendar year exists
        if (!$properties->get_calendar_year()) {
            return null;
        }

        return [
            'classes' => 'menuitem studentcohort',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/small_user_icon.gif',
            'text' => $this->string['studentcohort'],
            'href' => '#',
            'tabindex' => 0,
            'role' => 'menuitem'
        ];
    }

    // Question Tasks
    public function getCurrentQuestionItemsGrey($exam_clarifications = false, $paperType = null)
    {
        $items = [
            [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/edit_grey.png',
                'text' => $this->string['editquestion'],
                'disabled' => true
            ],
            [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/information_icon_grey.gif',
                'text' => $this->string['information'],
                'disabled' => true
            ],
            [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/copy_icon_grey.gif',
                'text' => $this->string['copyontopaperx'],
                'disabled' => true
            ],
            [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/link_grey.png',
                'text' => $this->string['linktopaper'],
                'disabled' => true
            ],
            [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/red_cross_grey.png',
                'text' => $this->string['removefrompaper'],
                'disabled' => true
            ],
            [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/small_play_grey.png',
                'text' => $this->string['previewquestion'],
                'disabled' => true
            ]
        ];

        // Add exam clarifications if enabled
        if ($exam_clarifications) {
            array_splice($items, 2, 0, [[
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/comment_16_grey.png',
                'text' => $this->string['midexamclarification'],
                'disabled' => true
            ]]);
        }

        return $items;
    }

    public function getCurrentQuestionItemsActive($exam_clarifications = false, $paperType = null)
    {
        $items = [
            [
                'classes' => 'menuitem edit',
                'id' => 'edit',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/edit.png',
                'text' => $this->string['editquestion'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem information',
                'id' => 'information',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/information_icon.gif',
                'text' => $this->string['information'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem copy',
                'id' => 'copy',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/copy_icon.gif',
                'text' => $this->string['copyontopaperx'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem link',
                'id' => 'link',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/link.png',
                'text' => $this->string['linktopaper'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem',
                'id' => 'delete',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/red_cross.png',
                'text' => $this->string['removefrompaper'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem startpaper',
                'id' => 'preview',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/small_play.png',
                'text' => $this->string['previewquestion'],
                'href' => '#',
                'data_attributes' => [
                    'fullscreen' => '0',
                    'preview' => '1'
                ]
            ]
        ];

        // Add exam clarifications if enabled
        if ($exam_clarifications) {
            array_splice($items, 2, 0, [[
                'classes' => 'menuitem clarification',
                'id' => 'clarification',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/comment_16.png',
                'text' => $this->string['midexamclarification'],
                'href' => '#'
            ]]);
        }

        return $items;
    }
    public function getKillerQuestionItem($isGrey = false)
    {
        if ($isGrey) {
            return [
                'classes' => 'grey menuitem',
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/skull_16.png',
                'text' => $this->string['unsetkillerquestion'],
                'disabled' => true,
            ];
        }

        return [
            'classes' => 'menuitem killerq',
            'id' => 'killerq',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/skull_16.png',
            'text' => $this->string['unsetkillerquestion'],
            'href' => '#',
            'disabled' => false,
        ];
    }

    public function getNewQuestionItem()
    {
        return [
            'classes' => 'menuitem cascade showmenu',
            'id' => 'newquestion',
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/new_question_menu_icon.gif',
            'text' => $this->string['createnewquestion'],
            'href' => '#',
            'data_attributes' => [
                'popupid' => '0',
                'popuptype' => 'banktasks',
                'popupname' => 'newquestion'
            ]
        ];
    }

    // Summative Checklist
    public function getSessionCheckItem($properties)
    {
        $tmp_match = Paper_utils::academic_year_from_title($properties->get_paper_title());
        if ($tmp_match !== false && $tmp_match != $properties->get_calendar_year()) {
            return [
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['session'],
                'href' => '',
                'status' => $this->string['mismatch']
            ];
        }
        return null;
    }

    public function getTimesCheckItem($properties)
    {
        if (
            $properties->get_start_date() == $properties->get_end_date() ||
            is_null($properties->get_start_date()) ||
            is_null($properties->get_end_date())
        ) {
            return [
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['examtime'],
                'href' => '',
                'status' => $this->string['incorrect']
            ];
        }
        return null;
    }

    public function getDurationCheckItem($properties)
    {
        if ($properties->get_exam_duration() == '') {
            return [
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['duration'],
                'href' => '',
                'status' => $this->string['unset']
            ];
        }
        return null;
    }

    public function getLabsCheckItem($properties)
    {
        if ($properties->get_labs() == '') {
            return [
                'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['computerlabs'],
                'href' => '',
                'status' => $this->string['unset']
            ];
        }
        return null;
    }

    public function getUnsetInternalReviewItem($text, $status)
    {
        return [
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/checklist_exclamation.png',
            'alt' => $this->string['warning'],
            'text' => $text,
            'href' => '',
            'link_class' => 'properties',
            'status' => $status
        ];
    }
    public function getInternalReviewCheckItem($text, $status, $isComplete, $paperID, $module, $folder)
    {
        $href = Config::get_instance()->get('cfg_root_path')
            . '/reports/review_comments.php?type=internal&paperID=' . $paperID
            . '&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=' . $module
            . '&folder=' . $folder . '&percent=100&absent=0&direction=asc';

        return [
            'height' => 16,
            'width' => 18,
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/'
               . ($isComplete ? 'checklist_tick.png' : 'checklist_exclamation.png'),
            'alt' => $isComplete ? '.' : $this->string['warning'],
            'text' => $text,
            'href' => $href,
            'link_class' => 'checklist',
            'status' => $status
        ];
    }

    public function getUnsetExternalReviewItem($text, $status)
    {
        return [
            'height' => 16,
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/checklist_exclamation.png',
            'alt' => $this->string['warning'],
            'text' => $text,
            'href' => '',
            'link_class' => 'properties',
            'status' => $status
        ];
    }

    public function getExternalReviewCheckItem($text, $status, $isComplete, $paperID, $module, $folder)
    {
        $href = Config::get_instance()->get('cfg_root_path')
            . '/reports/review_comments.php?type=external&paperID=' . $paperID
            . '&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=' . $module
            . '&folder=' . $folder . '&percent=100&absent=0&direction=asc';

        return [
            'height' => 16,
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/'
                . ($isComplete ? 'checklist_tick.png' : 'checklist_exclamation.png'),
            'alt' => $isComplete ? '.' : $this->string['warning'],
            'text' => $text,
            'href' => $href,
            'link_class' => 'checklist',
            'status' => $status
        ];
    }
}
