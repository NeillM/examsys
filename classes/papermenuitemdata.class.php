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
}