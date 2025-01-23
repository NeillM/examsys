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
 * Data class that handles the creation of menu items for the paper_options.php page.
 * This class generates structured data for all interactive elements in the paper sidebar,
 * including paper tasks, question tasks, and summative assessment checklist items.
 *
 * @author Iyud Dissanayake
 * @copyright Copyright (c) 2024 The University of Nottingham
 * @package 
 */
class PaperMenuItemData
{
    /** @var array Language strings used for menu item text */
    private $string;

    /** @var string The root path of the ExamSys installation */
    private $rootPath;

    /**
     * Constructor for PaperMenuItemData
     * 
     * @param array $string Array of language strings for menu items
     */
    public function __construct(array $string)
    {
        $this->rootPath = Config::get_instance()->get('cfg_root_path');
        $this->string = $string;
    }

    // Paper Tasks
    /**
     * Generates menu item data for the test preview option.
     * Returns a disabled menu item if the paper is offline or has no items,
     * otherwise returns an enabled menu item with fullscreen and preview settings.
     *
     * @param object $properties Paper properties object containing paper settings
     * @return array Menu item data structure with UI properties
     */
    public function getTestPreviewItem($properties)
    {
        if ($properties->get_paper_type() == \assessment::TYPE_OFFLINE || $properties->get_item_no() == 0) {
            return [
                'classes' => 'menuitem',
                'disabled' => true,
                'icon' => $this->rootPath . '/artwork/small_play_grey.png',
                'text' => $this->string['testpreview'],
                'href' => '#',
                'hasPopup' => false,
                'popupType' => null
            ];
        } else {
            return [
                'classes' => 'menuitem startpaper',
                'disabled' => false,
                'icon' => $this->rootPath . '/artwork/small_play.png',
                'text' => $this->string['testpreview'],
                'href' => '#',
                'hasPopup' => false,
                'popupType' => null,
                'data_attributes' => [
                    'fullscreen' => $properties->get_fullscreen(),
                    'preview' => '0'
                ]
            ];
        }
    }

    /**
     * Generates menu item data for adding questions to the paper.
     * Returns a disabled menu item if the paper is summatively locked,
     * otherwise returns an enabled menu item 
     *
     * @param object $properties Paper properties object containing paper settings
     * @param int $paperID The paper ID
     * @param string $module The module code
     * @param string $folder The folder name
     * @param int $scrOfY The scroll Y position
     * @return array Menu item data structure with UI properties
     */
    public function getAddQuestionsItem($properties, $paperID, $module, $folder, $scrOfY = 0)
    {
        if ($properties->get_summative_lock() == 1) {
            return [
                'classes' => 'menuitem',
                'disabled' => true,
                'icon' => $this->rootPath . '/artwork/add_questions_grey.gif',
                'text' => $this->string['addquestionspaper']
            ];
        }

        $max_screen = ($properties->get_max_screen() != '') ? $properties->get_max_screen() : 0;
        $display_pos = $properties->get_max_display_pos() + 1;
        
        // Calculate window dimensions and features
        $winW = "screen.width - 80";
        $winH = "screen.height - 100";
        $features = "width=' + $winW + ',height=' + $winH + ',left=40,top=0,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable";
        
        return [
            'classes' => 'menuitem addquestions',
            'disabled' => false,
            'icon' => $this->rootPath . '/artwork/add_questions_16.gif',
            'text' => $this->string['addquestionspaper'],
            'href' => '#',
            'action' => 'openPopup',
            'data_attributes' => [
                'url' => $this->rootPath . "/question/add/add_questions_frame.php"
                      . "?paperID=" . $paperID 
                      . "&module=" . $module
                      . "&folder=" . $folder
                      . "&scrOfY=" . $scrOfY
                      . "&display_pos=" . $display_pos
                      . "&max_screen=" . $max_screen,
                'popuptype' => 'window',
                'name' => 'notice',
                'features' => $features,
                'focus' => true
            ]
        ];
    }

    /**
     * Generates menu item data for editing paper properties.
     *
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getEditPropertiesItem($paperID, $module, $folder)
    {
        return [
            'classes' => 'menuitem properties',
            'icon' => $this->rootPath . '/artwork/properties_icon.gif',
            'text' => $this->string['editproperties'],
            'href' => $this->rootPath
                    . "/paper/properties.php?paperID=$paperID&caller=details&module=$module&folder=$folder",
            'action' => 'directUrl'
        ];
    }

    /**
     * Generates menu item data for emailing externals.
     *
     * @param object $properties Paper properties object containing paper settings
     * @return array Menu item data structure with UI properties
     */
    public function getEmailExternalsItem($properties)
    {
        // Only proceed if paper type is 2
        if ($properties->get_paper_type() != \assessment::TYPE_SUMMATIVE) {
            return [];
        }

        if (is_null($properties->get_external_review_deadline())) {
            return [
                'classes' => 'menuitem',
                'id' => 'emailexternalsgrey',
                'icon' => $this->rootPath . '/artwork/small_email_grey.png',
                'text' => $this->string['emailexternals'],
                'disabled' => true,
            ];
        }

        return [
            'classes' => 'menuitem cascade showmenu',
            'id' => 'emailexternals',
            'icon' => $this->rootPath . '/artwork/small_email.png',
            'text' => $this->string['emailexternals'],
            'href' => '#',
            'hasPopup' => true,
            'popupType' => 'menu',
            'action' => 'openSubMenu',
            'data_attributes' => [
                'popupid' => '1',
                'popuptype' => 'papertasks',
                'popupname' => 'emailexternals'
            ],
        ];
    }

    /**
     * Generates menu item data for accessing paper reports.
     *
     * @param object $properties Paper properties object
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getReportsItem($properties, $paperID, $module, $folder)
    {
        $paperType = $properties->get_paper_type();

        // Types 0,1,2,5,6 with no items - grey disabled version
        if (in_array($paperType, [\assessment::TYPE_FORMATIVE, \assessment::TYPE_PROGRESS, \assessment::TYPE_SUMMATIVE, \assessment::TYPE_OFFLINE, \assessment::TYPE_PEERREVIEW]) && $properties->get_item_no() == 0) {
            return [
                'classes' => 'menuitem greycascade',
                'icon' => $this->rootPath . '/artwork/statistics_icon_grey.gif',
                'text' => $this->string['reports']
            ];
        }

        // Return active version for all paper types
        return [
            'classes' => 'menuitem cascade stats',
            'id' => 'reports',
            'icon' => $this->rootPath . '/artwork/statistics_icon.gif',
            'text' => $this->string['reports'],
            'href' => '#'
        ];
    }

    /**
     * Generates menu item data for importing offline marks.
     *
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getImportOfflineMarksItem($paperID, $module, $folder)
    {
        return [
            'classes' => 'menuitem',
            'icon' => $this->rootPath . '/artwork/import_16.gif',
            'text' => $this->string['importmarks'],
            'href' => $this->rootPath . "/import/offline_marks.php?paperID=$paperID&module=$module&folder=$folder",
            'action' => 'directUrl'
        ];
    }

    /**
     * Generates menu item data for importing OSCE marks.
     *
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getImportOsceMarksItem($paperID, $module, $folder)
    {
        return [
            'classes' => 'menuitem',
            'icon' => $this->rootPath . '/artwork/import_16.gif',
            'text' => $this->string['importoscemarks'],
            'href' => $this->rootPath
                    . "/import/osce_marks.php?paperID=$paperID&module=$module&folder=$folder",
            'action' => 'directUrl'
            
        ];
    }

    /**
     * Generates menu item data for viewing mapped objectives.
     * Returns a disabled menu item if calendar year is not set.
     *
     * @param object $properties Paper properties object
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getMappedObjectivesItem($properties, $paperID, $module, $folder)
    {
        if ($properties->get_calendar_year() == '') {
            return [
                'classes' => 'menuitem',
                'disabled' => true,
                'icon' => $this->rootPath . '/artwork/curriculum_map_small_grey.png',
                'text' => $this->string['mappedobjectives']
            ];
        }

        return [
            'classes' => 'menuitem',
            'text' => $this->string['mappedobjectives'],
            'icon' => $this->rootPath . '/artwork/curriculum_map_small.png',
            'href' => $this->rootPath
                    . "/mapping/paper_by_session.php?paperID=$paperID&paper_title="
                    . $properties->get_paper_title()
                    . '&sd=' . $properties->get_start_date()
                    . '&ed=' . $properties->get_end_date()
                    . "&module=$module&folder=$folder",
            'action' => 'directUrl'
        ];
    }

    

    /**
     * Generates menu item data for copying a paper.
     *
     * @return array Menu item data structure with UI properties
     */
    public function getCopyPaperItem()
    {
        return [
            'classes' => 'menuitem cascade',
            'id' => 'copypaper',
            'icon' => $this->rootPath . '/artwork/copy_icon.gif',
            'text' => $this->string['copypaper'],
            'href' => '#',
            'hasPopup' => true,
            'popupType' => 'dialog',
        ];
    }

    /**
     * Generates menu item data for copying from another paper.
     * Returns a disabled menu item if the paper is summatively locked.
     *
     * @param object $properties Paper properties object
     * @return array Menu item data structure with UI properties
     */
    public function getCopyFromPaperItem($properties)
    {
        if ($properties->get_summative_lock() == 1) {
            return [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/copy_icon_grey.gif',
                'text' => $this->string['copyfrompaper'],
                'disabled' => true,
            ];
        }

        return [
            'classes' => 'menuitem cascade',
            'id' => 'copyfrompaper',
            'icon' => $this->rootPath . '/artwork/copy_icon.gif',
            'text' => $this->string['copyfrompaper'],
            'href' => '#',
            'hasPopup' => true,
            'popupType' => 'dialog',
        ];
    }

    /**
     * Generates menu item data for deleting a paper.
     * Returns a disabled menu item if the paper is summatively locked
     * or if user lacks required permissions for summative papers.
     *
     * @param object $properties Paper properties object
     * @param object $userObject User object containing role information
     * @param int $paperID The paper ID
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getDeletePaperItem($properties, $userObject, $paperID, $module, $folder)
    {
        $configObject = config::get_instance();
        if (
            $properties->get_summative_lock() == 1 ||
            ($configObject->get_setting('core', 'cfg_summative_mgmt') &&
            $properties->get_paper_type() == \assessment::TYPE_SUMMATIVE &&
            !$userObject->has_role(array('Admin', 'SysAdmin')))
        ) {
            return [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/delete_paper_grey_16.gif',
                'text' => $this->string['deletepaper'],
                'disabled' => true,
            ];
        }

        // Calculate window dimensions and position
        $width = 500;
        $height = 210;
        $left = "' + (screen.width / 2 - " . ($width/2) . ") + '";
        $top = "' + (screen.height / 2 - " . ($height/2) . ") + '";
        $features = "width=$width,height=$height,left=$left,top=$top,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable";

        return [
            'classes' => 'menuitem deletepaper',
            'icon' => $this->rootPath . '/artwork/delete_paper_16.gif',
            'text' => $this->string['deletepaper'],
            'href' => '#',
            'action' => 'openPopup',
            'data_attributes' => [
                'url' => $this->rootPath . "/delete/check_delete_paper.php"
                      . "?paperID=" . $paperID 
                      . "&module=" . $module
                      . "&folder=" . $folder,
                'popuptype' => 'window',
                'name' => 'notice',
                'features' => $features,
                'focus' => true
            ]
        ];
    }

    /**
     * Generates menu item data for retiring a paper.
     *
     * @return array Menu item data structure with UI properties
     */
    public function getRetirePaperItem()
    {
        return [
            'classes' => 'menuitem retirepaper',
            'icon' => $this->rootPath . '/artwork/retire_16.png',
            'text' => $this->string['retirepaper'],
            'href' => '#',
        ];
    }

    /**
     * Generates menu item data for printing a hardcopy of the paper.
     * Returns a disabled menu item if the paper has no items.
     *
     * @param object $properties Paper properties object
     * @param int $paperID The ID of the paper
     * @return array Menu item data structure with UI properties
     */
    public function getPrintHardcopyItem($properties, $paperID)
    {
        if ($properties->get_item_no() == 0) {
            return [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/print_icon_16_disabled.png',
                'text' => $this->string['printhardcopy'],
                'disabled' => true,
            ];
        }

        if ($properties->get_paper_type() == \assessment::TYPE_OSCE) {
            return [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/print_icon_16.png',
                'text' => $this->string['printhardcopy'],
                'href' => $this->rootPath . "/osce/print.php?paperID=$paperID"
            ];
        }

        return [
            'classes' => 'menuitem cascade showmenu',
            'id' => 'hardcopy',
            'icon' => $this->rootPath . '/artwork/print_icon_16.png',
            'text' => $this->string['printhardcopy'],
            'href' => '#',
            'hasPopup' => true,
            'popupType' => 'menu',
            'action' => 'openSubMenu',
            'data_attributes' => [
                'popupid' => '3',
                'popuptype' => 'papertasks',
                'popupname' => 'hardcopy'
            ],
        ];
    }

    /**
     * Generates menu item data for importing/exporting paper data.
     *
     * @param object $properties Paper properties object
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @return array Menu item data structure with UI properties
     */
    public function getImportExportItem($properties, $paperID, $module)
    {
        return [
            'text' => $this->string['importexport'],
            'icon' => $this->rootPath . '/artwork/ims_16.png',
            'href' => '#',
            'classes' => 'menuitem cascade showmenu',
            'id' => 'qti',
            'disabled' => false,
            'action' => 'openSubMenu',
            'hasPopup' => true,
            'popupType' => 'menu',
            'data_attributes' => [
                'popupid' => '2',
                'popuptype' => 'papertasks',
                'popupname' => 'qti'
            ]
        ];
    }

    /**
     * Generates menu item data for accessing the student cohort.
     * Returns an empty array if calendar year is not set.
     *
     * @param object $properties Paper properties object
     * @return array Menu item data structure with UI properties
     */
    public function getStudentCohortItem($properties)
    {
    // Only return if calendar year exists
        if (!$properties->get_calendar_year()) {
            return [];
        }

        return [
            'classes' => 'menuitem studentcohort',
            'icon' => $this->rootPath . '/artwork/small_user_icon.gif',
            'text' => $this->string['studentcohort'],
            'href' => '#',
        ];
    }

    /**
     * Generates menu item data for standard settings.
     * 
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getStandardSettingsItem($paperID, $module, $folder)
    {
        return [
            'classes' => 'menuitem',
            'icon' => $this->rootPath . '/artwork/std_set_icon_16.gif',
            'text' => $this->string['standardssetting'],
            'href' => $this->rootPath . "/std_setting/index.php?paperID=$paperID&module=$module&folder=$folder",
            'action' => 'directUrl'
        ];
    }

    // Question Tasks
    /**
     * Generates menu items data for question actions in disabled state.
     *
     * @param bool $exam_clarifications Whether to include exam clarification options
     * @param string|null $paperType The type of paper
     * @return array Array of menu item data structures
     */
    public function getCurrentQuestionItemsGrey($exam_clarifications = false, $paperType = null)
    {
        $items = [
            [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/edit_grey.png',
                'text' => $this->string['editquestion'],
                'disabled' => true
            ],
            [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/information_icon_grey.gif',
                'text' => $this->string['information'],
                'disabled' => true
            ],
            [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/copy_icon_grey.gif',
                'text' => $this->string['copyontopaperx'],
                'disabled' => true
            ],
            [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/link_grey.png',
                'text' => $this->string['linktopaper'],
                'disabled' => true
            ],
            [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/red_cross_grey.png',
                'text' => $this->string['removefrompaper'],
                'disabled' => true
            ],
            [
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/small_play_grey.png',
                'text' => $this->string['previewquestion'],
                'disabled' => true
            ]
        ];

        // Add exam clarifications if enabled
        if ($exam_clarifications) {
            array_splice($items, 2, 0, [[
                'classes' => 'menuitem',
                'icon' => $this->rootPath . '/artwork/comment_16_grey.png',
                'text' => $this->string['midexamclarification'],
                'disabled' => true
            ]]);
        }

        return $items;
    }

    /**
     * Generates menu items data for question actions in active state.
     *
     * @param bool $exam_clarifications Whether to include exam clarification options
     * @param string|null $paperType The type of paper
     * @return array Array of menu item data structures
     */
    public function getCurrentQuestionItemsActive($exam_clarifications = false, $paperType = null)
    {
        $items = [
            [
                'classes' => 'menuitem edit',
                'id' => 'edit',
                'icon' => $this->rootPath . '/artwork/edit.png',
                'text' => $this->string['editquestion'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem information',
                'id' => 'information',
                'icon' => $this->rootPath . '/artwork/information_icon.gif',
                'text' => $this->string['information'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem copy',
                'id' => 'copy',
                'icon' => $this->rootPath . '/artwork/copy_icon.gif',
                'text' => $this->string['copyontopaperx'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem link',
                'id' => 'link',
                'icon' => $this->rootPath . '/artwork/link.png',
                'text' => $this->string['linktopaper'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem',
                'id' => 'delete',
                'icon' => $this->rootPath . '/artwork/red_cross.png',
                'text' => $this->string['removefrompaper'],
                'href' => '#'
            ],
            [
                'classes' => 'menuitem startpaper',
                'id' => 'preview',
                'icon' => $this->rootPath . '/artwork/small_play.png',
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
                'icon' => $this->rootPath . '/artwork/comment_16.png',
                'text' => $this->string['midexamclarification'],
                'href' => '#'
            ]]);
        }

        return $items;
    }

    /**
     * Generates menu item data for killer question option.
     *
     * @param bool $isGrey Whether to return the item in disabled state
     * @return array Menu item data structure with UI properties
     */
    public function getKillerQuestionItem($isGrey = false)
    {
        if ($isGrey) {
            return [
                'classes' => 'menuitem killer',
                'icon' => $this->rootPath . '/artwork/skull_16.png',
                'text' => $this->string['unsetkillerquestion'],
                'disabled' => true,
            ];
        }

        return [
            'classes' => 'menuitem killerq killer',
            'id' => 'killerq',
            'icon' => $this->rootPath . '/artwork/skull_16.png',
            'text' => $this->string['setkillerquestion'],
            'href' => '#',
            'disabled' => false,
        ];
    }

    /**
     * Generates menu item data for creating a new question.
     *
     * @return array Menu item data structure with UI properties
     */
    public function getNewQuestionItem()
    {
        return [
            'classes' => 'menuitem cascade showmenu',
            'id' => 'newquestion',
            'icon' => $this->rootPath . '/artwork/new_question_menu_icon.gif',
            'text' => $this->string['createnewquestion'],
            'href' => '#',
            'hasPopup' => true,
            'popupType' => 'menu',
            'action' => 'openSubMenu',
            'data_attributes' => [
                'popupid' => '0',
                'popuptype' => 'banktasks',
                'popupname' => 'newquestion'
            ]
        ];
    }

    // Summative Checklist
    /**
     * Generates menu item data for session check in summative checklist.
     * Returns a warning item if academic year from title doesn't match calendar year.
     *
     * @param object $properties Paper properties object
     * @return array Menu item data structure with UI properties
     */
    public function getSessionCheckItem($properties)
    {
        $tmp_match = Paper_utils::academic_year_from_title($properties->get_paper_title());
        if ($tmp_match !== false && $tmp_match != $properties->get_calendar_year()) {
            return [
                'icon' => $this->rootPath . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['session'],
                'href' => '',
                'status' => $this->string['mismatch']
            ];
        }
        return [];
    }

    /**
     * Generates menu item data for time check in summative checklist.
     *
     * @param object $properties Paper properties object
     * @return array Menu item data structure with UI properties
     */
    public function getTimesCheckItem($properties)
    {
        if (
            $properties->get_start_date() == $properties->get_end_date() ||
            is_null($properties->get_start_date()) ||
            is_null($properties->get_end_date())
        ) {
            return [
                'icon' => $this->rootPath . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['examtime'],
                'href' => '',
                'status' => $this->string['incorrect']
            ];
        }
        return [];
    }

    /**
     * Generates menu item data for duration check in summative checklist.
     *
     * @param object $properties Paper properties object
     * @return array Menu item data structure with UI properties
     */
    public function getDurationCheckItem($properties)
    {
        if ($properties->get_exam_duration() == '') {
            return [
                'icon' => $this->rootPath . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['duration'],
                'href' => '',
                'status' => $this->string['unset']
            ];
        }
        return [];
    }

    /**
     * Generates menu item data for labs check in summative checklist.
     *
     * @param object $properties Paper properties object
     * @return array Menu item data structure with UI properties
     */
    public function getLabsCheckItem($properties)
    {
        if ($properties->get_labs() == '') {
            return [
                'icon' => $this->rootPath . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['computerlabs'],
                'href' => '',
                'status' => $this->string['unset']
            ];
        }
        return [];
    }

    /**
     * Generates menu item data for unsetting internal review status.
     *
     * @param string $text Display text for the menu item
     * @param string $status Current review status
     * @return array Menu item data structure with UI properties
     */
    public function getUnsetInternalReviewItem($text, $status)
    {
        return [
            'icon' => $this->rootPath . '/artwork/checklist_exclamation.png',
            'alt' => $this->string['warning'],
            'text' => $text,
            'href' => '',
            'link_class' => 'properties',
            'status' => $status
        ];
    }

    /**
     * Generates menu item data for internal review check in summative checklist.
     *
     * @param string $text Display text for the menu item
     * @param string $status Current review status
     * @param bool $isComplete Whether the review is complete
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getInternalReviewCheckItem($text, $status, $isComplete, $paperID, $module, $folder)
    {
        $href = $this->rootPath
            . '/reports/review_comments.php?type=internal&paperID=' . $paperID
            . '&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=' . $module
            . '&folder=' . $folder . '&percent=100&absent=0&direction=asc';

        return [
            'height' => 16,
            'width' => 18,
            'icon' => $this->rootPath . '/artwork/'
               . ($isComplete ? 'checklist_tick.png' : 'checklist_exclamation.png'),
            'alt' => $isComplete ? $this->string['complete'] : $this->string['warning'],
            'text' => $text,
            'href' => $href,
            'link_class' => 'checklist',
            'status' => $status
        ];
    }

    /**
     * Generates menu item data for unsetting external review status.
     *
     * @param string $text Display text for the menu item
     * @param string $status Current review status
     * @return array Menu item data structure with UI properties
     */
    public function getUnsetExternalReviewItem($text, $status)
    {
        return [
            'height' => 16,
            'icon' => $this->rootPath . '/artwork/checklist_exclamation.png',
            'alt' => $this->string['warning'],
            'text' => $text,
            'href' => '',
            'link_class' => 'properties',
            'status' => $status
        ];
    }

    /**
     * Generates menu item data for external review check in summative checklist.
     *
     * @param string $text Display text for the menu item
     * @param string $status Current review status
     * @param bool $isComplete Whether the review is complete
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getExternalReviewCheckItem($text, $status, $isComplete, $paperID, $module, $folder)
    {
        $href = $this->rootPath
            . '/reports/review_comments.php?type=external&paperID=' . $paperID
            . '&startdate=&enddate=&repcourse=%&repyear=%&sortby=name&module=' . $module
            . '&folder=' . $folder . '&percent=100&absent=0&direction=asc';

        return [
            'height' => 16,
            'icon' => $this->rootPath . '/artwork/'
                . ($isComplete ? 'checklist_tick.png' : 'checklist_exclamation.png'),
            'alt' => $isComplete ? $this->string['complete'] : $this->string['warning'],
            'text' => $text,
            'href' => $href,
            'link_class' => 'checklist',
            'status' => $status
        ];
    }

    /**
     * Generates menu item data for standards set check in summative checklist.
     *
     * @param float $standards_set Standards set value
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getStandardsSetCheckItem($standards_set, $paperID, $module, $folder)
    {
        $href = "{$this->rootPath}/std_setting/index.php?paperID={$paperID}&module={$module}&folder={$folder}";

        $status = match($standards_set) {
            1 => $this->string['ok'],
            0.5 => $this->string['incomplete'],
            default => $this->string['unset']
        };
        
        return [
            'icon' => $this->rootPath . '/artwork/' . ($standards_set == 1 ? 'checklist_tick.png' : 'checklist_exclamation.png'),
            'alt' => $standards_set == 1 ? $this->string['complete'] : $this->string['warning'],
            'text' => $this->string['standardsset'],
            'href' => $href,
            'link_class' => 'checklist',
            'status' => $status
        ];
    }

    /**
     * Generates menu item data for mapping check in summative checklist.
     *
     * @param int $mappings_complete Number of completed mappings
     * @param int $total_questions Total number of questions
     * @param string $objsBySession Objectives by session
     * @param int $paperID The ID of the paper
     * @param string $module The module code
     * @param string $folder The folder name
     * @return array Menu item data structure with UI properties
     */
    public function getMappingCheckItem($mappings_complete, $total_questions, $objsBySession, $paperID, $module, $folder)
    {
        $href = "{$this->rootPath}/mapping/paper_by_question.php?paperID={$paperID}&folder={$folder}&module={$module}";
        
        if ($objsBySession == 'error') {
            return [
                'icon' => $this->rootPath . '/artwork/checklist_exclamation.png',
                'alt' => $this->string['warning'],
                'text' => $this->string['mapping'],
                'href' => $href,
                'link_class' => 'checklist',
                'status' => 'Error'
            ];
        }

        $isComplete = $mappings_complete >= $total_questions;
        return [
            'icon' => $this->rootPath . '/artwork/' . ($isComplete ? 'checklist_tick.png' : 'checklist_exclamation.png'),
            'alt' => $isComplete ? $this->string['complete'] : $this->string['warning'],
            'text' => $this->string['mapping'],
            'href' => $href,
            'link_class' => 'checklist',
            'status' => $isComplete ? $this->string['ok'] : "$mappings_complete/$total_questions"
        ];
    }
}
