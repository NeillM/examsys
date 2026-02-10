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
 * Page that displays components available in ExamSys
 *
 * @author Simon Wilkinson
 * @copyright Copyright (c) 2026 The University of Nottingham
 */

require '../include/sysadmin_auth.inc';

use component\Helper;
use component\Register;

$collection = param::required('collection', param::ALPHA);
$component = param::required('component', param::ALPHA);

$renderer = new render($configObject);

$class = Register::getComponentClassName($collection, $component);
$example = $class::getExample();

$headerjs = $example->getJavascriptForHead();
$footerjs = $example->getJavascriptForFooter();
$css = [
    Helper::getCSSPath(true),
];

$renderer->render([], $string, 'component/preview_header.html', $headerjs, $css);
$renderer->renderComponent($example);
$renderer->render([], $string, 'component/preview_footer.html', $footerjs);
