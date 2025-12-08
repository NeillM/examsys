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
 * Data class that handles the creation of menu items for general sidebar menus
 * (homepage, folder pages, module pages, etc.)
 * This class generates structured data for interactive elements in the sidebar,
 * following the same pattern as PaperMenuItemData.
 *
 * @author Nisha Sarala
 * @copyright Copyright (c) 2025 The University of Nottingham
 * @package
 */
class GeneralMenuItemData
{
    /** @var string The root path of the ExamSys installation */
    private $rootPath;

    /**
     * Constructor for GeneralMenuItemData
     *
     * @param array $string Array of language strings for menu items
     */
    public function __construct(private array $string)
    {
        $this->rootPath = Config::get_instance()->get('cfg_root_path');
    }

    /**
     * Generates menu item data for the Search menu with popup submenu.
     *
     * @param string|null $module Optional module code for module-specific search
     * @return array Menu item data structure with UI properties
     */
    public function getSearchMenuItem(?string $module = null): array
    {
        return [
            'classes' => 'cascade showmenu',
            'id' => 'search',
            'icon' => $this->rootPath . '/artwork/xmag.png',
            'text' => $this->string['search'],
            'href' => '#',
            'hasPopup' => true,
            'popupType' => 'menu',
            'action' => 'openSubMenu',
            'type' => 'link',
            'data_attributes' => [
                'popupid' => '0',
                'popuptype' => 'general',
                'popupname' => 'search'
            ],
        ];
    }

    /**
     * Generates menu item data for creating a new folder.
     *
     * @return array Menu item data structure with UI properties
     */
    public function getCreateFolderItem(): array
    {
        return [
            'classes' => '',
            'icon' => $this->rootPath . '/artwork/folder_16.png',
            'text' => $this->string['createfolder'],
            'href' => $this->rootPath . '/index.php?newfolder=y',
            'action' => 'directUrl'
        ];
    }

    /**
     * Generates menu item data for personal keywords.
     *
     * @param string|null $module Optional module code for module-specific keywords
     * @return array Menu item data structure with UI properties
     */
    public function getPersonalKeywordsItem(?string $module = null): array
    {
        $url = $this->rootPath . '/folder/list_keywords.php';
        if ($module) {
            $url .= '?module=' . $module;
        }
        return [
            'classes' => '',
            'icon' => $this->rootPath . '/artwork/key.png',
            'text' => $this->string['mypersonalkeywords'],
            'href' => $url,
            'action' => 'directUrl'
        ];
    }

    /**
     * Generates menu item data for admin tools (SysAdmin only).
     *
     * @return array Menu item data structure with UI properties
     */
    public function getAdminToolsItem(): array
    {
        return [
            'classes' => '',
            'icon' => $this->rootPath . '/artwork/admin_icon_16.gif',
            'text' => $this->string['admintools'],
            'href' => $this->rootPath . '/admin/index.php',
            'action' => 'directUrl'
        ];
    }

    /**
     * Generates menu item data for calendar (Admin only).
     *
     * @return array Menu item data structure with UI properties
     */
    public function getCalendarItem(): array
    {
        $year = date('Y');
        $month = date('n');
        return [
            'classes' => '',
            'icon' => $this->rootPath . '/artwork/shortcut_calendar_icon.png',
            'text' => $this->string['calendar'],
            'href' => $this->rootPath . "/admin/calendar.php?calyear=$year#$month",
            'action' => 'directUrl'
        ];
    }

    /**
     * Generates menu item data for folder properties.
     *
     * @param bool $isOwner Whether the current user owns the folder
     * @return array Menu item data structure with UI properties
     */
    public function getFolderPropertiesItem(bool $isOwner): array
    {
        if ($isOwner) {
            return [
                'classes' => 'folderprop',
                'icon' => $this->rootPath . '/artwork/properties_icon.gif',
                'text' => $this->string['folderproperties'],
                'href' => '#',
                'action' => 'directUrl'
            ];
        } else {
            return [
                'classes' => 'greymenuitem',
                'disabled' => true,
                'icon' => $this->rootPath . '/artwork/properties_icon_grey.gif',
                'text' => $this->string['folderproperties']
            ];
        }
    }

    /**
     * Generates menu item data for making a subfolder.
     *
     * @param bool $isOwner Whether the current user owns the folder
     * @param string $module The module code
     * @param string $folder The folder ID
     * @return array Menu item data structure with UI properties
     */
    public function getMakeSubfolderItem(bool $isOwner, string $module, string $folder): array
    {
        if ($isOwner) {
            return [
                'classes' => '',
                'icon' => $this->rootPath . '/artwork/folder_16.png',
                'text' => $this->string['makesubfolder'],
                'href' => $this->rootPath . "/folder/index.php?module=$module&folder=$folder&newfolder=y",
                'action' => 'directUrl'
            ];
        } else {
            return [
                'classes' => 'greymenuitem',
                'disabled' => true,
                'icon' => $this->rootPath . '/artwork/folder_16_grey.png',
                'text' => $this->string['makesubfolder']
            ];
        }
    }

    /**
     * Generates menu item data for deleting a folder.
     *
     * @param bool $isOwner Whether the current user owns the folder
     * @return array Menu item data structure with UI properties
     */
    public function getDeleteFolderItem(bool $isOwner): array
    {
        if ($isOwner) {
            return [
                'classes' => 'deletefolder',
                'icon' => $this->rootPath . '/artwork/red_cross.png',
                'text' => $this->string['deletefolder'],
                'href' => '#',
                'action' => 'directUrl'
            ];
        } else {
            return [
                'classes' => 'greymenuitem',
                'disabled' => true,
                'icon' => $this->rootPath . '/artwork/red_cross_grey.png',
                'text' => $this->string['deletefolder']
            ];
        }
    }

    /**
     * Generates menu item data for managing objectives (module page).
     *
     * @param string $module The module code
     * @return array Menu item data structure with UI properties
     */
    public function getManageObjectivesItem(string $module): array
    {
        return [
            'classes' => '',
            'icon' => $this->rootPath . '/artwork/module_icon_16.png',
            'text' => $this->string['manageobjectives'],
            'href' => $this->rootPath . "/mapping/sessions_list.php?module=$module",
            'action' => 'directUrl'
        ];
    }

    /**
     * Generates menu item data for managing keywords (module page).
     *
     * @param string $module The module code
     * @return array Menu item data structure with UI properties
     */
    public function getManageKeywordsItem(string $module): array
    {
        return [
            'classes' => '',
            'icon' => $this->rootPath . '/artwork/key.png',
            'text' => $this->string['managekeywords'],
            'href' => $this->rootPath . "/folder/list_keywords.php?module=$module",
            'action' => 'directUrl'
        ];
    }

    /**
     * Generates menu item data for reference material (module page).
     *
     * @param string $module The module code
     * @return array Menu item data structure with UI properties
     */
    public function getReferenceMaterialItem(string $module): array
    {
        return [
            'classes' => '',
            'icon' => $this->rootPath . '/artwork/ref_16.png',
            'text' => $this->string['referencematerial'],
            'href' => $this->rootPath . "/module/list_ref_material.php?module=$module",
            'action' => 'directUrl'
        ];
    }
}
