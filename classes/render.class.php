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
    
    /**
     * Twig object
     */
    private $twig;
    
    /**
     * Constructor
     * @param object $configObject - rogo configuration object
     * @return void 
     */
    function __construct($configObject) {
        $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates');
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => false
        ));
    }
    
    /**
     * Render xml reponse.
     * @param string $template - template location
     * @param string $reponsename - response name
     * @param array $response - response data
     * @return $string xml response
     */
    public function render_xml($template, $reponsename, $response) {
        $data = array('name' => $reponsename, 'response' => $response);
        return $this->twig->render($template, $data);
    }
    
    /**
     * Render admin list page.
     * @param array $data - data to list
     * @param array $header - headers for data
     * @return void
     */
    public function render_admin_list($data, $header) {
        $data = array('data' => $data, 'header' => $header);
        echo $this->twig->render('admin/list.html', $data);
    }
    
    /**
     * Render admin page header.
     * @param array $lang translations used in header
     * @param array $config configuration items required
     * @param string $additionaljs additional javascript required
     * @param string $additionalcss additional css required
     * @return void
     */
    public function render_admin_header($lang, $config, $additionaljs, $additionalcss) {
        $data = array('lang' => $lang, 'config' => $config, 'additionaljs' => $additionaljs, 'additionalcss' => $additionalcss);
        echo $this->twig->render('admin/header.html', $data);
    }
    
    /**
     * Render admin page content.
     * @param array $breadcrumb breadcrumb navigation
     * @param string $toprightmenu menu link
     * @param array $lang translations used in header
     * @return void
     */
    public function render_admin_content($breadcrumb, $toprightmenu, $lang) {
        $data = array('breadcrumb' => $breadcrumb, 'toprightmenu' => $toprightmenu, 'lang' => $lang);
        echo $this->twig->render('admin/content.html', $data);
    }
    
    /**
     * Render admin page footer.
     * @return void
     */
    public function render_admin_footer() {
        echo $this->twig->render('admin/footer.html');
    }
    
    /**
     * Render admin options pane.
     * @param string $script - action script to add to page
     * @param string $image - icon file to display
     * @param array $lang - array of language strings
     * @param string $template - options template to use
     * @return void
     */
    public function render_admin_options($script, $image, $lang, $template = 'admin/options.html') {
        $data = array('script' => $script, 'image' => $image, 'lang' => $lang);
        echo $this->twig->render($template, $data);
    }
}