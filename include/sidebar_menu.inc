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
 *
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$menuNo = 0;

/**
 * Generate a popup menu using templates for better accessibility.
 *
 * @param array $options Array of menu options where keys are titles and values are URLs
 * @param render|null $render Optional render object. If not provided, one will be created.
 */
function makeMenu($options, $render = null)
{
    global $menuNo, $configObject;

    // Create render object if not provided
    if ($render === null) {
        $render = new render($configObject);
    }

    // Work out the width of the menu
    $longest_string = 0;
    foreach ($options as $title => $url) {
        // Handle [BR] separator in title
        $tmp_parts = explode('[BR]', (string) $title);
        $display_title = count($tmp_parts) > 1 ? $tmp_parts[1] : $tmp_parts[0];
        if (mb_strlen($display_title) > $longest_string) {
            $longest_string = mb_strlen($display_title);
        }
    }
    $width = ($longest_string * 7) + 35;
    if ($width < 60) {
        $width = 60;
    }  // Set a reasonable minimum width

    // Prepare data arrays for backward compatibility with scrolling functionality
    $line = 0;
    $myoptions = [];
    $myurls = [];
    foreach ($options as $title => $url) {
        $tmp_parts = explode('[BR]', (string) $title);
        if (count($tmp_parts) > 1) {
            $title = $tmp_parts[1];
        } else {
            $title = $tmp_parts[0];
        }
        $myoptions[] = $title;
        $line++;
    }

    $line = 0;
    foreach ($options as $title => $url) {
        $myurls[] = $url;
        $line++;
    }

    // Output the data container for scrolling functionality
    echo "<div id='popupmenu$menuNo' data-myOptions='" . json_encode($myoptions) . "' data-myURLs='" . json_encode($myurls) . "'></div>";

    // Start popup container
    echo "<div id=\"popup$menuNo\" class=\"popup\" style=\"width:" . $width . "px; z-index:30\">\n";

    // Add scroll up button if menu has more than 20 items
    if (count($options) > 20) {
        echo '<div data-menuno="' . $menuNo . '" id="' . $menuNo . "_up\" class=\"popupitem scrollup\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/submenu_up_off.png\" width=\"9\" height=\"5\" alt=\"down\" border=\"0\" />&nbsp;</div>\n";
    }

    // Render menu items using template
    $line = 0;
    foreach ($options as $title => $url) {
        $itemID = $menuNo . '_' . $line;

        // Process title to handle [BR] separator
        $tmp_parts = explode('[BR]', (string) $title);
        $display_title = count($tmp_parts) > 1 ? $tmp_parts[1] : $tmp_parts[0];

        // Determine item type and prepare template data
        $itemData = [
            'id' => $itemID
        ];

        if (mb_substr((string) $url, 0, 1) == '-') {
            // Separator
            $itemData['type'] = 'separator';
        } elseif (mb_substr((string) $url, 0, 1) == '#') {
            // Heading
            $itemData['type'] = 'heading';
            $itemData['text'] = mb_substr((string) $url, 1);
        } else {
            // Link or JavaScript action
            $itemData['text'] = $display_title;
            if (mb_stripos((string) $url, 'JavaScript:') !== false) {
                $itemData['type'] = 'javascript';
                // For JavaScript actions, store the full JavaScript code in onclick
                $itemData['href'] = '#';
                $itemData['onclick'] = htmlspecialchars((string) $url, ENT_QUOTES, 'UTF-8');
            } else {
                $itemData['type'] = 'link';
                $itemData['href'] = $url;
            }
        }

        // Render using template
        echo $render->renderString(['data' => $itemData], 'sidebar/popupmenuitem.html');

        $line++;
        if ($line > 19) {
            break;
        }
    }

    // Add scroll down button if menu has more than 20 items
    if (count($options) > 20) {
        echo '<div data-menuno="' . $menuNo . '" id="' . $menuNo . "_down\" class=\"popupitem scrolldown\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/submenu_down_on.png\" width=\"9\" height=\"5\" alt=\"down\" border=\"0\" />&nbsp;</div>\n";
    }

    echo "</div>\n";
    $menuNo++;
}
