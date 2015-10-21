<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* Render package
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

/**
 * Render helper class.
 * Interfaces with /vender/twig.
 */
class render {
    
    // Twig object.
    private $twig;
    
    /**
     * @brief Constructor
     * @param object $configObject - rogo configuration object
     * @return void 
     */
    function __construct($configObject) {
        $loader = new \Twig_Loader_Filesystem($configObject->get('twig_templates'));
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => $configObject->get('twig_cache')
        ));
    }
    
    /**
     * @brief Render page.
     * @param string $template - template location
     * @param array $data - data  to subsitutue
     * @return  
     */
    public function render($template, $data) {
        echo $this->twig->render($template, $data);
    }
    
    /**
     * @brief Render admin list page.
     * @param string $template - template location
     * @param array $data - data  to subsitutue
     * @return  
     */
    public function render_admin_list($data) {
        echo $this->twig->render('admin/list.html', $data);
    }
    
    /**
     * @brief Render admin page header.
     * @param string $template - template location
     * @param array $data - data  to subsitutue
     * @return  
     */
    public function render_admin_header($data) {
        echo $this->twig->render('admin/header.html', $data);
    }
    
    /**
     * @brief Render admin page footer.
     * @param string $template - template location
     * @param array $data - data  to subsitutue
     * @return  
     */
    public function render_admin_footer($data) {
        echo $this->twig->render('admin/footer.html', $data);
    }
}