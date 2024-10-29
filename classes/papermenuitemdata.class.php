<?php

class MenuItemData {

    private $string;

    public function __construct($configObject, $string) {
        $this->configObject = $configObject;
        $this->string = $string;
    }

    public function createMenuItem($text, $icon, $href = '#', $classes = '', $disabled = false, $id = '', $dataAttributes = [], $hasPopup = false, $tabindex = 0) {
        return [
            'text' => $text,
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/' . $icon,
            'href' => $href,
            'classes' => $classes,
            'disabled' => $disabled,
            'id' => $id,
            'data_attributes' => $dataAttributes,
            'hasPopup' => $hasPopup,
            'tabindex' => $tabindex
        ];
    }

    public function createSubmenuItem($text, $icon, $id, $dataAttributes, $submenuItems) {
        return [
            'text' => $text,
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/' . $icon,
            'href' => '#',
            'classes' => 'menuitem cascade showmenu',
            'disabled' => false,
            'id' => $id,
            'data_attributes' => $dataAttributes,
            'hasPopup' => true,
            'tabindex' => 0,
            'submenu' => $submenuItems,
            'template' => 'sidebar/submenuitem.html'
        ];
    }

    public function createPopupMenuItem($id, $text, $icon, $popupType, $popupId, $template = 'sidebar/popupmenuitem.html') {
        return [
            'id' => $id,
            'text' => $text,
            'icon' => Config::get_instance()->get('cfg_root_path') . '/artwork/' . $icon,
            'classes' => 'menuitem cascade showmenu',
            'disabled' => false,
            'hasPopup' => true,
            'href' => '#',
            'tabindex' => -1,
            'data_attributes' => [
                'popupid' => $popupId,
                'popuptype' => 'papertasks',
                'popupname' => $popupType
            ],
            'template' => $template
        ];
    }

    public function getTestPreviewItem($properties) {
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

    public function getImportExportItem($properties, $paperID, $module) {
        $submenuItems = [];
        
        if (!$properties->get_summative_lock()) {
            $submenuItems[] = [
                'text' => $this->string['import'],
                'href' => Config::get_instance()->get('cfg_root_path') . "/qti/import.php?paperID=$paperID&module=$module"
            ];
            $submenuItems[] = [
                'text' => $this->string['importraf'],
                'href' => Config::get_instance()->get('cfg_root_path') . "/import/rogo_assessment_format.php?paperID=$paperID&module=$module"
            ];
            if ($properties->get_question_no() > 0) {
                $submenuItems[] = ['type' => 'separator'];
            }
        }
        
        if ($properties->get_question_no() > 0) {
            $submenuItems[] = [
                'text' => $this->string['export12'],
                'href' => Config::get_instance()->get('cfg_root_path') . "/qti/export.php?dest=qti12&paperID=$paperID&module=$module"
            ];
            $submenuItems[] = [
                'text' => $this->string['exportraf'],
                'href' => Config::get_instance()->get('cfg_root_path') . "/export/rogo_assessment_format.php?paperID=$paperID"
            ];
        }

        return $this->createSubmenuItem(
            $this->string['importexport'],
            'ims_16.png',
            'qti',
            ['popupid' => '2', 'popuptype' => 'papertasks', 'popupname' => 'qti'],
            $submenuItems
        );
    }

    public function getReportsItem($properties, $paperID, $module, $folder, $checklist, $graded = false) {
        $paperType = $properties->get_paper_type();
        $iconRoot = Config::get_instance()->get('cfg_root_path') . '/artwork/';
        
        // Handling type 0,1,2,5,6 papers
        if (in_array($paperType, ['0', '1', '2', '5', '6'])) {
            if ($properties->get_item_no() == 0) {
                return [
                    'classes' => 'grey menuitem greycascade',
                    'disabled' => true,
                    'icon' => $iconRoot . 'statistics_icon_grey.gif',
                    'text' => $this->string['reports'],
                    'hasPopup' => false
                ];
            } else {
                $items = [[
                    'classes' => 'menuitem cascade stats',
                    'disabled' => false,
                    'icon' => $iconRoot . 'statistics_icon.gif',
                    'text' => $this->string['reports'],
                    'hasPopup' => true,
                    'href' => '#',
                    'tabindex' => 0,
                    'id' => 'reports'
                ]];

                // Add mapping objectives if applicable
                if (mb_strpos($checklist, 'mapping') !== false && $paperType != '6') {
                    $items[] = $this->getMappedObjectivesItem($properties, $paperID, $module, $folder);
                }

                // Add paper type specific items
                if ($paperType == '5') {
                    $items[] = [
                        'classes' => 'menuitem',
                        'icon' => $iconRoot . 'import_16.gif',
                        'text' => $this->string['importmarks'],
                        'href' => $iconRoot . "/import/offline_marks.php?paperID=$paperID&module=$module&folder=$folder",
                        'tabindex' => 0
                    ];
                } elseif ($paperType != '6') {
                    if (mb_strpos($checklist, 'stdset') !== false) {
                        $items[] = [
                            'classes' => 'menuitem',
                            'icon' => $iconRoot . 'std_set_icon_16.gif',
                            'text' => $this->string['standardssetting'],
                            'href' => $iconRoot . "/std_setting/index.php?paperID=$paperID&module=$module&folder=$folder",
                            'tabindex' => 0
                        ];
                    }
                }

                return $items[0]; // Return main reports item, other items handled by submenu
            }
        }
        
        // Handling type 3 paper
        if ($paperType == '3') {
            return [
                'classes' => 'menuitem cascade stats',
                'disabled' => false,
                'icon' => $iconRoot . 'statistics_icon.gif',
                'text' => $this->string['reports'],
                'hasPopup' => true,
                'href' => '#',
                'tabindex' => 0,
                'id' => 'reports'
            ];
        }
        
        // Handling type 4 paper
        if ($paperType == '4') {
            $items = [[
                'classes' => 'menuitem cascade stats',
                'disabled' => false,
                'icon' => $iconRoot . 'statistics_icon.gif',
                'text' => $this->string['reports'],
                'hasPopup' => true,
                'href' => '#',
                'tabindex' => 0,
                'id' => 'reports'
            ]];

            if (!$graded) {
                $items[] = [
                    'classes' => 'menuitem',
                    'icon' => $iconRoot . 'import_16.gif',
                    'text' => $this->string['importoscemarks'],
                    'href' => $iconRoot . "/import/osce_marks.php?paperID=$paperID&module=$module&folder=$folder",
                    'tabindex' => 0
                ];
            }

            if (mb_strpos($checklist, 'mapping') !== false) {
                $items[] = $this->getMappedObjectivesItem($properties, $paperID, $module, $folder);
            }

            return $items[0]; // Return main reports item, other items handled by submenu
        }
        
        return null;
    }

    private function getMappedObjectivesItem($properties, $paperID, $module, $folder) {
        if ($properties->get_calendar_year() == '') {
            return [
                'classes' => 'greymenuitem',
                'disabled' => true,
                'icon' => 'curriculum_map_small_grey.png',
                'text' => $this->string['mappedobjectives']
            ];
        }
        
        return [
            'classes' => 'menuitem',
            'text' => $this->string['mappedobjectives'],
            'icon' => 'curriculum_map_small.png',
            'href' => Config::get_instance()->get('cfg_root_path') . 
                    "/mapping/paper_by_session.php?paperID=$paperID&paper_title=" . 
                    $properties->get_paper_title() . 
                    "&sd=" . $properties->get_start_date() . 
                    "&ed=" . $properties->get_end_date() . 
                    "&module=$module&folder=$folder",
            'tabindex' => 0
        ];
    }
}