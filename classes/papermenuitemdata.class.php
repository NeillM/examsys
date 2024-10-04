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
}