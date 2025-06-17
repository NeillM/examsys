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
 * Utility class for generating breadcrumb navigation data.
 * This class provides methods to create breadcrumb data for different types of pages.
 *
 * @author Iyud Dissanayake
 * @copyright Copyright (c) 2025 The University of Nottingham
 * @package
 */
class BreadcrumbData
{
    /** @var array Language strings */
    protected $string;
    
    /** @var mysqli Database connection */
    protected $db;
    
    /**
     * Constructor
     * 
     * @param array $string Language strings
     */
    public function __construct(array $string)
    {
        $this->string = $string;
        $this->db = Config::get_instance()->db;
    }
    
    /**
     * Add folder navigation links to the breadcrumb
     *
     * @param array $breadcrumb The breadcrumb array to modify
     * @param string $folder Folder ID
     * @return array Modified breadcrumb array
     */
    protected function addFolderLinks(array $breadcrumb, string $folder): array
    {
        // Get folder name
        $folderName = folder_utils::get_folder_name($folder, $this->db);
        
        // Add parent folder links
        foreach (folder_utils::get_parent_list($folderName, UserObject::get_instance(), $this->db) as $parentId => $parentName) {
            $href = '/folder/index.php?folder=' . $parentId;
            $breadcrumb[$parentName] = $href;
        }

        // Add current folder link
        $href = '/folder/index.php?folder=' . $folder;
        $currentFolderName = false === mb_strpos($folderName, ';') ? 
            $folderName : mb_substr($folderName, mb_strrpos($folderName, ';') + 1);
        $breadcrumb[$currentFolderName] = $href;
        
        return $breadcrumb;
    }
    
    /**
     * Add module and paper type links to the breadcrumb
     *
     * @param array $breadcrumb The breadcrumb array to modify
     * @param string $module Module code
     * @param string $paperType Paper type
     * @return array Modified breadcrumb array
     */
    protected function addModuleAndPaperTypeLinks(array $breadcrumb, string $module, string $paperType): array
    {
        // Add module link
        $moduleId = module_utils::get_moduleid_from_id($module, $this->db);
        $moduleHref = '/module/index.php?module=' . $module;
        $breadcrumb[$moduleId] = $moduleHref;

        // Add paper type link
        $paperTypeName = Paper_utils::type_to_name($paperType, $this->string);
        $paperTypeHref = '/paper/type.php?module=' . $module . '&type=' . $paperType;
        $breadcrumb[$paperTypeName] = $paperTypeHref;
        
        return $breadcrumb;
    }
    
    /**
     * Prepare paper breadcrumb data
     *
     * @param int $paperID Paper ID
     * @param PaperProperties $properties Paper properties
     * @param string|null $module Module code
     * @param string|null $folder Folder name
     * @param string|null $currentPage Current page name (from language strings)
     * @return array Breadcrumb data
     */
    public function preparePaperBreadcrumb(
        int $paperID,
        PaperProperties $properties,
        ?string $module = null,
        ?string $folder = null,
        ?string $currentPage = null
    ): array {
        $breadcrumb = [];
        
        // Add home link
        $breadcrumb[$this->string['home']] = '/';
        
        // Add navigation based on context (folder or module)
        if ($folder) {
            $breadcrumb = $this->addFolderLinks($breadcrumb, $folder);
            
            // Add paper type link for folder navigation
            $paperType = $properties->get_paper_type();
            $paperTypeName = Paper_utils::type_to_name($paperType, $this->string);
            $paperTypeHref = '/paper/type.php?type=' . $paperType . '&folder=' . $folder;
            $breadcrumb[$paperTypeName] = $paperTypeHref;
        } else {
            // Determine module if not provided
            if (is_null($module)) {
                $modules = Paper_utils::get_modules($paperID, $this->db);
                $module = key($modules);
            }
            
            // Add module and paper type links if module is available
            if ($module) {
                $breadcrumb = $this->addModuleAndPaperTypeLinks($breadcrumb, $module, $properties->get_paper_type());
            }
        }

        // Add paper details link
        $paperTitle = $properties->get_paper_title();
        $paperHref = '/paper/details.php?paperID=' . $paperID;
        
        // Only include necessary parameters in URLs as per application standards
        if ($module) {
            $paperHref .= '&module=' . $module;
        }
        if ($folder) {
            $paperHref .= '&folder=' . $folder;
        }
        
        $breadcrumb[$paperTitle] = $paperHref;
        
        // Add current page if provided
        if ($currentPage) {
            $breadcrumb[$currentPage] = '';
        }
        
        return $breadcrumb;
    }
}

