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
 * Allows the properties of a paper to be edited.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

use component\form\Checkbox;
use component\form\CheckboxGroup;
use component\form\CheckboxOptGroup;
use component\form\Color;
use component\form\Date;
use component\form\Fieldset;
use component\form\Form;
use component\form\GeneralGroup;
use component\form\Hidden;
use component\form\Number;
use component\form\RadioGroup;
use component\form\Select;
use component\form\StaticComponent;
use component\form\StaticTemplate;
use component\form\Text;
use component\form\TextArea;
use component\form\Time;
use component\form\Url;
use component\notification\Notification;
use component\tabs\Tab;
use component\tabs\TabList;

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/add_edit.inc';  // to clear MS Office tags
require_once '../include/load_config.php';
require_once '../include/timezones.php';

// Marking options
define('MARK_NO_ADJUSTMENT', '0');
define('MARK_RANDOM', '1');
define('MARK_STD_SET', '2');

$paperID = check_var('paperID', 'REQUEST', true, false, true);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);
$noadd = param::optional('noadd', '', param::ALPHA, param::FETCH_GET);
$caller = param::optional('caller', '', param::ALPHA, param::FETCH_GET);
$render = new render($configObject);
$texteditorplugin = \plugins\plugins_texteditor::get_editor();

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
if ($properties->get_paper_type() == '2') {
    $minavailability = $properties->getMinAvailability();
} else {
    $minavailability = 0;
}
$modules_array = $properties->get_modules();

$q_feedback_enabled = Paper_utils::q_feedback_enabled(array_keys($modules_array), $mysqli);  // See if question-based feedback is enabled on all modules.

if ($properties->get_summative_lock() and !$userObject->has_role('SysAdmin')) {
    $locked = true;
} else {
    $locked = false;
}

if (!isset($staff_modules)) {
    $staff_modules = $userObject->get_staff_modules();
}

$option_no = 1;

// Work out if any negative marking is used
$neg_marking = $properties->isNegativelyMarked();

// Load textual feedback
$textual_feedback = Paper_utils::get_textual_feedback($paperID, $mysqli);

$local_time = new DateTimeZone($configObject->get('cfg_timezone'));
$target_timezone = new DateTimeZone($properties->get_timezone());

if (!is_null($properties->get_start_date())) {
    $start_date = DateTime::createFromFormat('U', $properties->get_start_date(), $local_time);
    $start_date->setTimezone($target_timezone);
} else {
    $start_date = '';
}

if (!is_null($properties->get_end_date())) {
    $end_date = DateTime::createFromFormat('U', $properties->get_end_date(), $local_time);
    $end_date->setTimezone($target_timezone);
} else {
    $end_date = '';
}

$sum_disabled = !$properties->canEditSecurity();

// Output the html header.
$render->render(
    [
        'css' => [
                '/css/header.css',
                '/css/properties.css',
                '/css/warnings.css',
                \component\Helper::getCSSPath(),
        ],
        'js' => [],
        'texteditor' => $texteditorplugin->get_header_file(),
    ],
    [
        'title' => $string['propertiestitle']
    ],
    'header.html'
);

require '../include/toprightmenu.inc';
echo draw_toprightmenu();
?>
<div id="content">
<?php
// Output the breadcrumbs.
$breadcrumbData = new BreadcrumbData($string);
$breadcrumb = $breadcrumbData->preparePaperBreadcrumb(
    $paperID,
    $properties,
    $module,
    $folder,
    $string['propertiestitle'],
);
echo $render->render_admin_navigation($breadcrumb->getData($render));

// Get the paper settings.
$papersettings = new PaperSettings($paperID, $properties->get_paper_type());

$secure_browser_enabled = ($configObject->get_setting('core', 'paper_seb_enabled') and $papersettings->settingsCategoryEnabled('seb'));
$reviews_enabled = !in_array($properties->get_paper_type(), [assessment::TYPE_SURVEY, assessment::TYPE_PEERREVIEW]);

$rubric_enabled = !in_array($properties->get_paper_type(), [
    assessment::TYPE_SURVEY,
    assessment::TYPE_OSCE,
    assessment::TYPE_OFFLINE,
    assessment::TYPE_PEERREVIEW,
]);

$prologue_postscript_enabled = !in_array($properties->get_paper_type(), [assessment::TYPE_OSCE, assessment::TYPE_OFFLINE]);

$reference_material_enabled = !in_array($properties->get_paper_type(), [
    assessment::TYPE_OSCE,
    assessment::TYPE_OFFLINE,
    assessment::TYPE_PEERREVIEW,
]);

$is_sysadmin = $userObject->has_role('SysAdmin');

$is_admin = $userObject->has_role(['SysAdmin', 'Admin']);

$is_graded = $properties->isGraded();

$general_tab = new Tab(
    id: 'general-tab',
    name: $string['generaltab'],
);
$security_tab = new Tab(
    id: 'security-tab',
    name: $string['securitytab'],
);

if ($noadd === 'y') {
    $security_tab->setSelected();
}

$tabs = [
    $general_tab,
    $security_tab,
];

if ($secure_browser_enabled) {
    $secure_browser_tab = new Tab(
        id: 'seb-tab',
        name: $string['sebtab'],
    );
    $tabs[] = $secure_browser_tab;
}

if ($reviews_enabled) {
    $feedback_tab = new Tab(
        id: 'feedback-tab',
        name: $string['feedback'],
    );
    $tabs[] = $feedback_tab;

    $reviewers_tab = new Tab(
        id: 'reviewers-tab',
        name: $string['reviewerstab'],
    );
    $tabs[] = $reviewers_tab;
}

if ($rubric_enabled) {
    $rubric_tab = new Tab(
        id: 'rubric-tab',
        name: $string['rubrictab'],
    );
    $tabs[] = $rubric_tab;
}

if ($prologue_postscript_enabled) {
    $prologue_tab = new Tab(
        id: 'prologue-tab',
        name: $string['prologuetab'],
    );
    $tabs[] = $prologue_tab;

    $postscript_tab = new Tab(
        id: 'postscript-tab',
        name: $string['postscripttab'],
    );
    $tabs[] = $postscript_tab;
}

if ($reference_material_enabled) {
    $reference_material_tab = new Tab(
        id: 'reference-material-tab',
        name: $string['referencematerial'],
    );
    $tabs[] = $reference_material_tab;
}

$tablist = new TabList(
    id: 'properties-tabs',
    name: '',
    tabs: $tabs,
    orientation: TabList::ORIENTATION_VERTICAL,
);

$form = new Form(
    action: $_SERVER['PHP_SELF'],
    method: Form::METHOD_POST,
    autocomplete: false,
    id: 'theform',
);

$form->addElement(new Hidden('paperID', 'paperID', $paperID));
$form->addElement(new Hidden('noadd', 'noadd', $noadd));
$form->addElement(new Hidden('caller', 'caller', $caller));

$form->addElement(new StaticComponent($tablist, '@tabs/tab_list_start.html'));

// Add the general tab.
$form->addElement(new StaticComponent($general_tab, '@tabs/tab_panel_start.html'));
$form->addElement(new StaticTemplate(
    data: [
        'heading' => $string['generalheading'],
        'class' => 'general',
    ],
    template: 'paper/properties_heading.html',
));

$paper_details = new GeneralGroup(
    id: 'paper-details',
    name: 'paper-details',
    label: $string['paperdetails'],
);

// Add the url that students use to view the paper.
$exam_day_only = in_array($properties->get_paper_type(), [assessment::TYPE_SUMMATIVE, assessment::TYPE_OSCE]);
if ($exam_day_only) {
    $url_desc = $string['onlyonexamday'];
} else {
    $url_desc = '';
}
$paper_details->addOption(new Url(
    id: 'paper-url',
    name: 'paper-url',
    label: $string['url'],
    value: $properties->getPaperUrl() ?: $string['na'],
    description: $url_desc,
    readonly: true,
    pattern: '',
    size: 75,
));

// Add the paper title field.
$paper_details->addOption(new Text(
    id: 'papertitle',
    name: 'paper_title',
    label: $string['name'],
    value: $properties->get_paper_title(),
    readonly: $locked,
    required: true,
    maxLength: 200,
    size: 75,
));

// Add the paper type selector.
if (in_array($properties->get_paper_type(), [assessment::TYPE_FORMATIVE, assessment::TYPE_PROGRESS])) {
    $options = [
        '0' => $string['formative self-assessment'],
        '1' => $string['progress test'],
    ];
} else {
    $tmp_types = ['formative self-assessment', 'progress test', 'summative exam', 'survey', 'osce station', 'offline paper', 'peer review'];
    $options = [
        $properties->get_paper_type() => $string[$tmp_types[$properties->get_paper_type()]],
    ];
}

$paper_details->addOption(new Select(
    id: 'paper_type',
    name: 'paper_type',
    label: $string['type'],
    options: $options,
    default: $properties->get_paper_type(),
));

// Add the folder selector.
$paper_details->addOption(new Select(
    id: 'folderID',
    name: 'folderID',
    label: $string['folder'],
    options: array_merge(
        [''],
        folder_utils::getUsersFolderNames(
            $userObject->get_user_ID(),
            array_keys($staff_modules ?? []),
            (int) $properties->get_folder(),
        ),
    ),
    default: (int) $properties->get_folder(),
));

// External system details.
$external = new \external_systems();
$external_systems = $external->get_all_externalsystems();

$external_options = [];
foreach ($external_systems as $system) {
    $external_options[$system] = $system;
}

$paper_details->addOption(new Select(
    id: 'externalsys',
    name: 'externalsys',
    label: $string['externalsys'],
    options: array_merge(
        [''],
        $external_options,
    ),
    default: $properties->get_externalsys() ?? '',
    disabled: !$is_sysadmin || $is_graded,
));

$paper_details->addOption(new Text(
    id: 'externalid',
    name: 'externalid',
    label: $string['externalid'],
    value: (string) $properties->get_externalid(),
    readonly: !$is_sysadmin || $is_graded,
    maxLength: 255,
    size: 30,
));

$form->addElement($paper_details);

$can_edit_display_options = $properties->get_paper_type() == assessment::TYPE_OSCE;

if ($properties->get_paper_type() == assessment::TYPE_OSCE) {
    // In OSCEs the display settings are present in the form, but not displayed.
    $display_options_classes = ['hidden'];
} else {
    $display_options_classes = [];
}

$display_options = new GeneralGroup(
    id: 'display-options',
    name: 'display-options',
    label: $string['displayoptions'],
    classes: $display_options_classes,
);

$display_options->addOption(new Select(
    id: 'fullscreen',
    name: 'fullscreen',
    label: $string['display'],
    options: [
        $string['windowed'],
        $string['fullscreen'],
    ],
    default: $properties->get_fullscreen(),
    disabled: $locked,
));

$display_options->addOption(new Select(
    id: 'bidirectional',
    name: 'bidirectional',
    label: $string['navigation'],
    options: [
        $string['unidirectional'],
        $string['bidirectional'],
    ],
    default: $properties->get_bidirectional(),
    disabled: $locked,
));

// Colour selectors.
$display_options->addOption(new Color(
    id: 'background',
    name: 'background',
    label: $string['background'],
    value: $properties->get_bgcolor(),
    readonly: $locked,
));
$display_options->addOption(new Color(
    id: 'foreground',
    name: 'foreground',
    label: $string['foreground'],
    value: $properties->get_fgcolor(),
    readonly: $locked,
));
$display_options->addOption(new Color(
    id: 'themecolor',
    name: 'themecolor',
    label: $string['theme'],
    value: $properties->get_themecolor(),
    readonly: $locked,
));
$display_options->addOption(new Color(
    id: 'labelcolor',
    name: 'labelcolor',
    label: $string['labelsnotes'],
    value: $properties->get_labelcolor(),
    readonly: $locked,
));

if ($properties->get_paper_type() == assessment::TYPE_PEERREVIEW) {
    $audio = new CheckboxGroup(
        id: 'display_photos',
        name: 'display_photos',
        label: $string['photos'],
    );
    $audio->addOption(
        value: '1',
        label: $string['ifavailable'],
        checked: $properties->get_display_correct_answer()
    );
    $display_options->addOption($audio);
} else {
    $calculator = new CheckboxGroup(
        id: 'calculator',
        name: 'calculator',
        label: $string['calculator'],
    );
    $calculator->addOption(
        value: '1',
        label: $string['displaycalculator'],
        description: $string['tooltip_calculator'],
        disabled: $locked,
        checked: $properties->get_calculator(),
    );
    $display_options->addOption($calculator);

    $audio = new CheckboxGroup(
        id: 'sound_demo',
        name: 'sound_demo',
        label: $string['audio'],
    );
    $audio->addOption(
        value: '1',
        label: $string['demosoundclip'],
        description: $string['tooltip_audio'],
        disabled: $locked,
        checked: $properties->get_sound_demo(),
    );
    $display_options->addOption($audio);
}

$form->addElement($display_options);

$marking_options = new GeneralGroup(
    id: 'marking-options',
    name: 'marking-options',
    label: $string['marking'],
);

if ($is_graded) {
    // Output a warning.
    $marking_options->addOption(new StaticComponent(new Notification(
        message: $string['paperpublishedwarning'],
        classes: ['locked-image'],
        image: true,
        type: Notification::TYPE_WARNING,
    )));
}

$percentage_options = [];
for ($i = 0; $i <= 100; $i++) {
    $percentage_options["$i"] = "$i%";
}

if ($properties->get_paper_type() == assessment::TYPE_OSCE) {
    $osce_started = $properties->get_osce_started_status($paperID, $mysqli);

    $marking_values = [
        '102' => $string['na'],
        '101' => $string['borderlinemethod'],
    ] + $percentage_options;

    $marking_options->addOption(new Select(
        id: 'pass_mark',
        name: 'pass_mark',
        label: $string['passmark'],
        options: $marking_values,
        default: $properties->get_pass_mark(),
        disabled: $is_graded,
    ));

    $classification_options = [
        '5' => $string['na'],
        '7' => $string['overallclass5'],
        '3' => $string['overallclass2'],
        '4' => $string['overallclass3'],
        '6' => $string['overallclass4'],
    ];
    $marking_options->addOption(new Select(
        id: 'marking',
        name: 'marking',
        label: $string['overallclassification'],
        options: $classification_options,
        default: $properties->get_marking(),
        description: $string['tooltip_osceclassification'],
        disabled: $osce_started || $is_graded,
    ));

    // Add the postscript
    $postscript_components = $texteditorplugin->getTextareaComponent(
        id: 'osce_marking_guidance',
        label: $string['markingguidance'],
        content: $texteditorplugin->get_text_for_display(htmlspecialchars((string) $properties->get_paper_postscript()), ENT_NOQUOTES),
        type: plugins\plugins_texteditor::TYPE_STANDARD,
        classes: ['properties-textbox'],
    );
    foreach ($postscript_components as $component) {
        $marking_options->addOption($component);
    }
} elseif ($properties->get_paper_type() == assessment::TYPE_PEERREVIEW) {
    $marking_options->addOption(new Select(
        id: 'type',
        name: 'type',
        label: $string['groupdetails'],
        options: array_merge(
            ['' => ''],
            $properties->getUserMetadataTypes(),
        ),
        default: (string) $properties->get_rubric(),
    ));

    $marking_options->addOption(new Select(
        id: 'marking',
        name: 'marking',
        label: $string['numberfrom'],
        options: [0, 1],
        default: $properties->get_marking(),
    ));

    $review = new RadioGroup(
        id: 'review',
        name: 'review',
        label: $string['review'],
        default: $properties->get_display_question_mark(),
    );
    $review->addOption(
        value: '1',
        label: $string['allpeerspergroup'],
    );
    $review->addOption(
        value: '0',
        label: $string['singlereview'],
    );
    $marking_options->addOption($review);
} elseif ($properties->get_paper_type() != assessment::TYPE_SURVEY) {
    $marking_options->addOption(new Number(
        id: 'pass_mark',
        name: 'pass_mark',
        label: $string['passmark'],
        value: $properties->get_pass_mark(),
        description: $string['percentage'],
        readonly: $is_graded,
        max: 100,
        min: 1,
        step: 1,
    ));

    $has_disticntion = in_array($properties->get_paper_type(), [
        assessment::TYPE_FORMATIVE,
        assessment::TYPE_PROGRESS,
        assessment::TYPE_SUMMATIVE,
    ]);

    if ($has_disticntion) {
        $marking_options->addOption(new Number(
            id: 'distinction_mark',
            name: 'distinction_mark',
            label: $string['distinction'],
            value: $properties->get_distinction_mark(),
            description: $string['percentage'],
            readonly: $is_graded,
            max: 100,
            min: 1,
            step: 1,
        ));
    }

    $marking = new RadioGroup(
        id: 'marking',
        name: 'marking',
        label: $string['method'],
        default: mb_substr((string) $properties->get_marking(), 0, 1),
    );
    $marking->addOption(
        value: MARK_NO_ADJUSTMENT,
        label: $string['noadjustment'],
        disabled: $is_graded,
    );
    $marking->addOption(
        value: MARK_RANDOM,
        label: $string['calculatrrandommark'],
        description: $string['tooltip_random'],
        disabled: $is_graded || $neg_marking,
    );
    $standardsetting = new StandardSetting($mysqli);
    $std_setting_options = $standardsetting->getStdSettingWithName($paperID);
    $has_standard_setting = !empty($std_setting_options);
    $marking->addOption(
        value: MARK_STD_SET,
        label: $string['stdset'],
        disabled: $is_graded || !$has_standard_setting,
    );
    $marking_options->addOption($marking);

    if ($has_standard_setting) {
        $options = [];
        foreach ($std_setting_options as $option) {
            $key = MARK_STD_SET . ',' . $option['std_setID'];
            $label = "{$option['title']} {$option['surname']}, {$option['initials']} - {$option['display_date']}";
            $options[$key] = $label;
        }

        $marking_options->addOption(new Select(
            id: 'std_set',
            name: 'std_set',
            label: $string['selectedstdset'],
            options: $options,
            default: $properties->get_marking(),
            disabled: $is_graded,
        ));
    }
}

$form->addElement($marking_options);

$general_settings = $properties->getSettingsComponents('general');
foreach ($general_settings as $setting) {
    $form->addElement($setting);
}

$form->addElement(new StaticComponent($general_tab, '@tabs/tab_panel_end.html'));

// Add the security tab.
$form->addElement(new StaticComponent($security_tab, '@tabs/tab_panel_start.html'));
$form->addElement(new StaticTemplate(
    data: [
        'heading' => $string['securityheading'],
        'class' => 'security',
    ],
    template: 'paper/properties_heading.html',
));

if ($properties->get_summative_lock() and $userObject->has_role(['SysAdmin'])) {
    // Output a warning.
    $marking_options->addOption(new StaticComponent(new Notification(
        message: $string['donotchangewarning'],
        classes: ['locked-image'],
        image: true,
        type: Notification::TYPE_WARNING,
    )));
}

// Academic year selector.
$yearutils = new yearutils($mysqli);
$form->addElement($yearutils->getCalendarYearOptionsComponent(
    id: 'session',
    name: 'calendar_year',
    label: $string['session'],
    paper_type: $properties->get_paper_type(),
    calendar_year: (string) $properties->get_calendar_year(),
    na: $string['na'],
    classes: ['meta'],
    disabled: $sum_disabled || $is_graded,
));

$form->addElement(new Text(
    id: 'password',
    name: 'password',
    label: $string['password'],
    value: (string) $properties->get_decrypted_password(),
    description: $string['tooltip_password'],
    readonly: $locked,
    size: 20,
    disabled: $properties->get_paper_type() == assessment::TYPE_OSCE,
));

$duration = new GeneralGroup(
    id: 'duration',
    name: 'duration',
    label: $string['duration'],
    orientation: GeneralGroup::ORIENTATION_HORIZONTAL,
);

$exam_duration = $properties->get_exam_duration();
if ($exam_duration == null) {
    $duration_hours = '';
    $duration_mins = '';
} else {
    $duration_hours = (int)floor($exam_duration / 60);
    $duration_mins = (int)$exam_duration - ($duration_hours * 60);
}

$hours = ['NULL' => $string['na']];
for ($i = 0; $i <= 12; $i++) {
    $hours[$i] = $i;
}
$duration->addOption(new Number(
    id: 'exam_duration_hours',
    name: 'exam_duration_hours',
    label: $string['hrs'],
    value: $duration_hours,
    description: $string['hrs_info'],
    readonly: $sum_disabled || $is_graded || $locked,
    max: 12,
    min: 0,
    step: 1,
));

$minutes = ['NULL' => $string['na']];
for ($i = 0; $i < 60; $i++) {
    $minutes[$i] = $i;
}
$duration->addOption(new Number(
    id: 'exam_duration_mins',
    name: 'exam_duration_mins',
    label: $string['mins'],
    value: $duration_mins,
    description: $string['mins_info'],
    readonly: $sum_disabled || $is_graded || $locked,
    max: 59,
    min: 0,
    step: 1,
));

$form->addElement($duration);

$form->addElement(new Select(
    id: 'timezone',
    name: 'timezone',
    label: $string['timezone'],
    options: $timezone_array,
    default: $properties->get_timezone(),
    disabled: $sum_disabled || $is_graded || $locked,
));

$mindate = '2002-01-01';
$maxdate = new DateTime('last day of December +20 years');

$paper_start = new GeneralGroup(
    id: 'paper-start',
    name: 'paper-start',
    label: $string['availablefrom'],
    orientation: GeneralGroup::ORIENTATION_HORIZONTAL,
);

$paper_start->addOption(new Date(
    id: 'fdate',
    name: 'fdate',
    label: $string['date'],
    value: ($start_date == '') ? $start_date : $start_date->format('Y-m-d'),
    classes: ['datecopy'],
    max: $maxdate->format('Y-m-d'),
    min: $mindate,
    readonly: $sum_disabled || $is_graded,
));

$paper_start->addOption(new Time(
    id: 'ftime',
    name: 'ftime',
    label: $string['time'],
    value: ($start_date == '') ? $start_date : $start_date->format('H:i'),
    classes: ['datecopy'],
    readonly: $sum_disabled || $is_graded,
));

$form->addElement($paper_start);

if ($properties->get_paper_type() == assessment::TYPE_SUMMATIVE and !is_null($exam_duration)) {
    $enddesc = sprintf($string['minavailability'], $minavailability);
} else {
    $enddesc = '';
}

$paper_end = new GeneralGroup(
    id: 'paper-end',
    name: 'paper-end',
    label: $string['availableuntil'],
    description: $enddesc,
    orientation: GeneralGroup::ORIENTATION_HORIZONTAL,
);

$paper_end->addOption(new Date(
    id: 'tdate',
    name: 'tdate',
    label: $string['date'],
    value: ($end_date == '') ? $end_date : $end_date->format('Y-m-d'),
    classes: ['datecopy'],
    max: $maxdate->format('Y-m-d'),
    min: $mindate,
    readonly: $sum_disabled || $is_graded,
));

$paper_end->addOption(new Time(
    id: 'ttime',
    name: 'ttime',
    label: $string['time'],
    value: ($end_date == '') ? $end_date : $end_date->format('H:i'),
    classes: ['datecopy'],
    readonly: $sum_disabled || $is_graded,
));

$form->addElement($paper_end);

$security_settings = $properties->getSettingsComponents('security');
foreach ($security_settings as $setting) {
    $form->addElement($setting);
}

// Module selection list.
$modules = new CheckboxOptGroup(
    id: 'modules_list',
    name: 'mod[]',
    label: $string['modules'],
    classes: ['modules', 'highlight-checked'],
);

// Gets the list of modules attached to the paper.
$modules_array = $properties->get_modules();

// Get a list of modules the user can access.
$module_list = $userObject->get_staff_accessable_modules();

$old_schoolcode = '';
foreach ($module_list as $module) {
    if ($old_schoolcode !== $module['schoolcode']) {
        // Update the old code.
        $old_schoolcode = $module['schoolcode'];
        $code = $module['schoolcode'] ?? '';
        $name = $code ? "{$code} {$module['school']}" : $module['school'];
        $modules->addGroup($code, $name);
    }
    $selected = isset($modules_array[$module['idMod']]);
    $modules->addOption(
        group: $code,
        value: $module['idMod'],
        label: $module['id'] . ': ' . mb_substr((string) $module['fullname'], 0, 60),
        disabled: $locked && !$is_sysadmin,
        checked: $selected,
    );
}

$form->addElement($modules);

// Lab list.

// Labs are not relevant to OSCE papers.
if ($properties->get_paper_type() != assessment::TYPE_OSCE) {
    // SysAdmins can usually edit labs.
    $is_sysadmin = $userObject->has_role(['Admin', 'SysAdmin']);

    // When summative management is enabled only SysAdmins can modify the labs on summative papers.
    $is_summative = $properties->get_paper_type() == assessment::TYPE_SUMMATIVE;
    $summative_management = $configObject->get_setting('core', 'cfg_summative_mgmt');

    $can_modify =  $is_sysadmin || ($is_summative && !$summative_management);

    $labs = new CheckboxOptGroup(
        id: 'labs_list',
        name: 'lab[]',
        label: $string['restricttolabs'],
        classes: ['labs', 'highlight-checked'],
        groupclasses: ['campus'],
    );

    $lab_factory = new LabFactory($mysqli);
    $lab_list = $lab_factory->getAllLabs();

    $current_labs = explode(',', (string) $properties->get_labs());

    $campus_count = 0;
    $old_campus = '';
    foreach ($lab_list as $lab) {
        $campus = $lab->get_campus();

        if ($old_campus != $campus) {
            $campus_count++;
            $old_campus = $campus;
            $labid = "lab-{$campus_count}";
            $labs->addGroup(
                id: $labid,
                label: $lab->get_campus(),
            );
        }

        $labs->addOption(
            group: $labid,
            value: $lab->get_id(),
            label: $lab->get_name() . ' (' . $lab->getNumberOfPC() . ')',
            disabled: $sum_disabled,
            checked: in_array($lab->get_id(), $current_labs),
        );
    }

    $form->addElement($labs);
}

// Metadata restrictions.
$metadata = new GeneralGroup(
    id: 'metadata_security',
    name: 'metadata_security',
    label: $string['restricttometadata'],
    classes: ['metadata'],
);
$form->addElement($metadata);

$form->addElement(new StaticComponent($security_tab, '@tabs/tab_panel_end.html'));

if ($secure_browser_enabled) {
    // Add the secure exam browser tab.
    $form->addElement(new StaticComponent($secure_browser_tab, '@tabs/tab_panel_start.html'));
    $form->addElement(new StaticTemplate(
        data: [
            'heading' => $string['seb_keys_heading'],
            'class' => 'seb',
        ],
        template: 'paper/properties_heading.html',
    ));

    $seb_settings = $properties->getSettingsComponents('seb');
    foreach ($seb_settings as $setting) {
        $form->addElement($setting);
    }

    $seb_metadata = Paper_utils::get_metadata($mysqli, $paperID, 'seb_hash');
    $seb_keys = $seb_metadata['seb_hash'] ?? [];

    $form->addElement(new TextArea(
        id: 'seb_keys_text',
        name: 'seb_keys_text',
        label: $string['seb_keys'],
        classes: ['sebkeys'],
        value: implode("\n", $seb_keys),
        description: $string['seb_keys_title'],
    ));

    $form->addElement(new StaticComponent($secure_browser_tab, '@tabs/tab_panel_end.html'));
}

if ($reviews_enabled) {
    // Add the feedback tab.
    $form->addElement(new StaticComponent($feedback_tab, '@tabs/tab_panel_start.html'));
    $form->addElement(new StaticTemplate(
        data: [
            'heading' => $string['feedbackheading'],
            'class' => 'feedback',
        ],
        template: 'paper/properties_heading.html',
    ));

    $feedback = new Feedback($properties);

    if ($feedback->objectiveFeedbackPossible()) {
        $objective_group = new GeneralGroup(
            id: 'objectives-group',
            name: 'objectives-group',
            label: $string['objectivesreport'],
            classes: ['feedback', 'objectives-report'],
        );

        $objective_feedback = new RadioGroup(
            id: 'objectives_report',
            name: 'objectives_report',
            label: $string['feedbackreport'],
            default: (int) $feedback->hasObjectiveFeedback(),
            orientation: Fieldset::ORIENTATION_HORIZONTAL,
        );
        $objective_feedback->addOption('1', $string['on']);
        $objective_feedback->addOption('0', $string['off']);
        $objective_group->addOption($objective_feedback);

        $objective_group->addOption(new Url(
            id: 'objectives_report_url',
            name: 'objectives_report_url',
            label: $string['feedbackurl'],
            value: $feedback->getObjectiveFeedbackUrl(),
            readonly: true,
            size: 75,
        ));

        $form->addElement($objective_group);
    }

    if ($feedback->questionFeedbackPossible()) {
        if ($properties->get_paper_type() == assessment::TYPE_SUMMATIVE) {
            $description = $string['feedbackwarning'];
        } else {
            $description = '';
        }

        $question_group = new GeneralGroup(
            id: 'questions-group',
            name: 'questions-group',
            label: $string['questionfeedback'],
            classes: ['feedback', 'questions-report'],
            description: $description,
        );

        $question_feedback = new RadioGroup(
            id: 'questions_report',
            name: 'questions_report',
            label: $string['feedbackreport'],
            default: (int) $feedback->hasQuestionFeedback(),
            orientation: Fieldset::ORIENTATION_HORIZONTAL,
        );
        $question_feedback->addOption('1', $string['on']);
        $question_feedback->addOption('0', $string['off']);
        $question_group->addOption($question_feedback);

        $question_group->addOption(new Url(
            id: 'questions_report_url',
            name: 'questions_report_url',
            label: $string['feedbackurl'],
            value: $feedback->getQuestionFeedbackUrl(),
            readonly: true,
            size: 75,
        ));

        $form->addElement($question_group);
    }

    if ($feedback->cohortPerformanceFeedbackPossible()) {
        $cohort_group = new GeneralGroup(
            id: 'cohort-group',
            name: 'cohort-group',
            label: $string['cohortperformancefeedback'],
            classes: ['feedback', 'cohort-performance'],
        );

        $cohort_feedback = new RadioGroup(
            id: 'cohort_performance',
            name: 'cohort_performance',
            label: $string['feedbackreport'],
            default: (int) $feedback->hasCohortPerformanceFeedback(),
            orientation: Fieldset::ORIENTATION_HORIZONTAL,
        );
        $cohort_feedback->addOption('1', $string['on']);
        $cohort_feedback->addOption('0', $string['off']);
        $cohort_group->addOption($cohort_feedback);

        $cohort_group->addOption(new Url(
            id: 'cohort_report_url',
            name: 'cohort_report_url',
            label: $string['feedbackurl'],
            value: $feedback->getCohortPerformanceFeedbackUrl(),
            readonly: true,
            size: 75,
        ));

        $form->addElement($cohort_group);
    }

    if ($feedback->externalExaminerFeedbackPossible()) {
        $external_group = new GeneralGroup(
            id: 'external-group',
            name: 'external-group',
            label: $string['externalexaminerfeedback'],
            classes: ['feedback', 'external-examiner'],
            description: $string['externalwarning'],
        );

        $external_feedback = new RadioGroup(
            id: 'external_examiner',
            name: 'external_examiner',
            label: $string['feedbackreport'],
            default: (int) $feedback->hasExternalExaminerFeedback(),
            orientation: Fieldset::ORIENTATION_HORIZONTAL,
        );
        $external_feedback->addOption('1', $string['on']);
        $external_feedback->addOption('0', $string['off']);
        $external_group->addOption($external_feedback);

        $external_group->addOption(new Url(
            id: 'external_report_url',
            name: 'external_report_url',
            label: $string['feedbackurl'],
            value: $feedback->getExternalExaminerFeedbackUrl(),
            readonly: true,
            size: 75,
        ));

        $form->addElement($external_group);
    }

    if ($properties->get_paper_type() != assessment::TYPE_OSCE) {
        if ($properties->get_paper_type() != assessment::TYPE_FORMATIVE) {
            // The settings are present byt not displayed.
            $answer_screen_classes = ['hidden'];
        } else {
            $answer_screen_classes = [];
        }

        $answer_screen = new GeneralGroup(
            id: 'answer-screen',
            name: 'answer-screen',
            label: $string['answerscreensettings'],
            classes: $answer_screen_classes,
            orientation: Fieldset::ORIENTATION_HORIZONTAL,
        );
        $answer_screen->addOption(new Checkbox(
            id: 'display_students_response',
            name: 'display_students_response',
            label: $string['ticks_crosses'],
            value: '1',
            checked: $properties->get_display_students_response(),
        ));
        $answer_screen->addOption(new Checkbox(
            id: 'display_question_mark',
            name: 'display_question_mark',
            label: $string['question_marks'],
            value: '1',
            checked: $properties->get_display_question_mark(),
        ));
        $answer_screen->addOption(new Checkbox(
            id: 'hide_if_unanswered',
            name: 'hide_if_unanswered',
            label: $string['hideallfeedback'],
            value: '1',
            checked: $properties->get_hide_if_unanswered(),
        ));
        $answer_screen->addOption(new Checkbox(
            id: 'display_correct_answer',
            name: 'display_correct_answer',
            label: $string['correctanswerhighlight'],
            value: '1',
            checked: $properties->get_display_correct_answer(),
        ));
        $answer_screen->addOption(new Checkbox(
            id: 'display_feedback',
            name: 'display_feedback',
            label: $string['textfeedback'],
            value: '1',
            checked: $properties->get_display_feedback(),
        ));
        $form->addElement($answer_screen);
    }

    // Paper level text feedback.
    $has_textfeedback = !in_array($properties->get_paper_type(), [
        assessment::TYPE_SUMMATIVE,
        assessment::TYPE_OSCE
    ]);

    if ($has_textfeedback) {
        $text_feedback = new GeneralGroup(
            id: 'textual-feedback',
            name: 'textual-feedback',
            label: $string['textualfeedback'],
        );

        for ($i = 1; $i <= 10; $i++) {
            $boundary = new GeneralGroup(
                id: 'textual-feedback-boundary-' . $i,
                name: 'textual-feedback-boundary-' . $i,
                label: sprintf($string['textualfeedbackboundary'], $i),
                classes: ['feedback-boundary'],
                orientation: Fieldset::ORIENTATION_HORIZONTAL,
            );

            $boundary->addOption(new Number(
                id: 'feedback_value' . $i,
                name: 'feedback_value' . $i,
                label: $string['above'],
                value: $textual_feedback[$i]['boundary'] ?? '',
                description: $string['percentage'],
                max: 100,
                min: 0,
                step: 1,
            ));
            $boundary->addOption(new TextArea(
                id: 'feedback_msg' . $i,
                name: 'feedback_msg' . $i,
                label: $string['message'],
                cols: 60,
                value: $textual_feedback[$i]['msg'] ?? '',
                rows: 1,
            ));

            $text_feedback->addOption($boundary);
        }

        $form->addElement($text_feedback);
    }

    $feedback_settings = $properties->getSettingsComponents('feedback');
    foreach ($feedback_settings as $setting) {
        $form->addElement($setting);
    }

    $form->addElement(new StaticComponent($feedback_tab, '@tabs/tab_panel_end.html'));

    // Add the reviewers tab.
    $form->addElement(new StaticComponent($reviewers_tab, '@tabs/tab_panel_start.html'));
    $form->addElement(new StaticTemplate(
        data: [
            'heading' => $string['reviewersheading'],
            'class' => 'reviewers',
        ],
        template: 'paper/properties_heading.html',
    ));

    if ($properties->hasQuestionsOfType('sct')) {
        // Display a link to the SCT review page for the paper.
        // Standard users are not able to view it.
        $sct_url = NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path')
            . '/reviews/sct_review.php?id=' . urlencode((string) $properties->get_crypt_name());
        $form->addElement(new Url(
            id: 'sct-review-url',
            name: 'sct-review-url',
            label: $string['sct_review'],
            value: $sct_url,
            readonly: true,
            size: 75,
        ));
    }

    // The internal review settings.
    $internal_review = new GeneralGroup(
        id: 'internalreview',
        name: 'internalreview',
        label: $string['internalreview'],
    );

    $internal_review->addOption(new Date(
        id: 'internaldeadline',
        name: 'internaldeadline',
        label: $string['deadline'],
        value: (string) $properties->get_internal_review_deadline(),
    ));

    $internal = new CheckboxGroup(
        id: 'internal',
        name: 'internal[]',
        label: $string['internalreviewers'],
        classes: ['reviewers', 'highlight-checked'],
    );

    $internal_reviewers = $properties->getInternalReviewerList();
    foreach ($internal_reviewers as $reviewer) {
        $internal->addOption(
            value: $reviewer['id'],
            label: $reviewer['name'],
            disabled: $locked && !$is_admin,
            checked: $reviewer['selected'],
        );
    }

    $internal_review->addOption($internal);

    $form->addElement($internal_review);

    // The External review settings.
    $external_review = new GeneralGroup(
        id: 'externalreview',
        name: 'externalreview',
        label: $string['externalreview'],
    );

    $external_review->addOption(new Date(
        id: 'externaldeadline',
        name: 'externaldeadline',
        label: $string['deadline'],
        value: (string) $properties->get_external_review_deadline(),
    ));

    $external = new CheckboxGroup(
        id: 'examiner',
        name: 'examiner[]',
        label: $string['externalexaminers'],
        classes: ['reviewers', 'highlight-checked'],
    );

    $externals_examiners = $properties->getExternalExaminerList();
    foreach ($externals_examiners as $reviewer) {
        $external->addOption(
            value: $reviewer['id'],
            label: $reviewer['name'],
            disabled: $locked && !$is_admin,
            checked: $reviewer['selected'],
        );
    }

    $external_review->addOption($external);

    $form->addElement($external_review);

    $reviewers_settings = $properties->getSettingsComponents('reviewers');
    foreach ($reviewers_settings as $setting) {
        $form->addElement($setting);
    }

    $form->addElement(new StaticComponent($reviewers_tab, '@tabs/tab_panel_end.html'));
}

if ($rubric_enabled) {
    // Add the rubric tab.
    $form->addElement(new StaticComponent($rubric_tab, '@tabs/tab_panel_start.html'));
    $form->addElement(new StaticTemplate(
        data: [
            'heading' => $string['rubricheading'],
            'class' => 'rubric',
        ],
        template: 'paper/properties_heading.html',
    ));

    // Add the rubric text editor.
    $postscript_components = $texteditorplugin->getTextareaComponent(
        id: 'rubric_text',
        label: $string['rubricheading'],
        content: $texteditorplugin->get_text_for_display(htmlspecialchars((string) $properties->get_rubric()), ENT_NOQUOTES),
        type: plugins\plugins_texteditor::TYPE_STANDARD,
        classes: ['properties-prologue-textbox'],
        disabled: $locked,
    );
    foreach ($postscript_components as $component) {
        $form->addElement($component);
    }

    $rubric_settings = $properties->getSettingsComponents('rubric');
    foreach ($rubric_settings as $setting) {
        $form->addElement($setting);
    }

    $form->addElement(new StaticComponent($rubric_tab, '@tabs/tab_panel_end.html'));
}

if ($prologue_postscript_enabled) {
    // Add the prologue tab.
    $form->addElement(new StaticComponent($prologue_tab, '@tabs/tab_panel_start.html'));
    $form->addElement(new StaticTemplate(
        data: [
            'heading' => $string['prologueheading'],
            'class' => 'prologue',
        ],
        template: 'paper/properties_heading.html',
    ));

    // Add the prologue text editor.
    $postscript_components = $texteditorplugin->getTextareaComponent(
        id: 'paper_prologue',
        label: $string['prologueheading'],
        content: $texteditorplugin->get_text_for_display(htmlspecialchars((string) $properties->get_paper_prologue()), ENT_NOQUOTES),
        type: plugins\plugins_texteditor::TYPE_STANDARD,
        classes: ['properties-prologue-textbox'],
        disabled: $locked,
    );
    foreach ($postscript_components as $component) {
        $form->addElement($component);
    }

    $prologue_settings = $properties->getSettingsComponents('prologue');
    foreach ($prologue_settings as $setting) {
        $form->addElement($setting);
    }

    $form->addElement(new StaticComponent($prologue_tab, '@tabs/tab_panel_end.html'));

    // Add the postscript tab.
    $form->addElement(new StaticComponent($postscript_tab, '@tabs/tab_panel_start.html'));
    $form->addElement(new StaticTemplate(
        data: [
            'heading' => $string['postscriptheading'],
            'class' => 'postscript',
        ],
        template: 'paper/properties_heading.html',
    ));

    // Add the postscript text editor.
    $postscript_components = $texteditorplugin->getTextareaComponent(
        id: 'paper_postscript',
        label: $string['postscriptheading'],
        content: $texteditorplugin->get_text_for_display(htmlspecialchars((string) $properties->get_paper_postscript()), ENT_NOQUOTES),
        type: plugins\plugins_texteditor::TYPE_STANDARD,
        classes: ['properties-prologue-textbox'],
        disabled: $locked,
    );
    foreach ($postscript_components as $component) {
        $form->addElement($component);
    }

    $postscript_settings = $properties->getSettingsComponents('postscript');
    foreach ($postscript_settings as $setting) {
        $form->addElement($setting);
    }

    $form->addElement(new StaticComponent($postscript_tab, '@tabs/tab_panel_end.html'));
}

if ($reference_material_enabled) {
    // Add the reference material tab.
    $form->addElement(new StaticComponent($reference_material_tab, '@tabs/tab_panel_start.html'));
    $form->addElement(new StaticTemplate(
        data: [
            'heading' => $string['referenceheading'],
            'class' => 'reference-material',
        ],
        template: 'paper/properties_heading.html',
    ));

    // Metadata restrictions.
    $reference = new GeneralGroup(
        id: 'reference_list',
        name: 'reference_list',
        label: $string['referencematerial'],
        classes: ['reference'],
    );
    $form->addElement($reference);

    $reference_settings = $properties->getSettingsComponents('reference');
    foreach ($reference_settings as $setting) {
        $form->addElement($setting);
    }

    $form->addElement(new StaticComponent($reference_material_tab, '@tabs/tab_panel_end.html'));
}

$form->addElement(new StaticComponent($tablist, '@tabs/tab_list_end.html'));

$form->setStandardButtons($string['ok'], $string['cancel']);

$render->renderComponent($form);

$dataset['name'] = 'dataset';
$dataset['attributes']['rootpath'] = $cfg_root_path;
$dataset['attributes']['type'] = $properties->get_paper_type();
$dataset['attributes']['id'] = $paperID;
$dataset['attributes']['minavail'] = $minavailability;
$dataset['attributes']['summativemanagment'] = $sum_disabled;
$render->render($dataset, [], 'dataset.html');
// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, [], 'dataset.html');

// Output the footer.
$scripts = \component\Helper::combineJS(
    [
        '/js/paperpropertiesinit.min.js',
    ],
    $breadcrumb->getJavascriptForFooter(),
    $form->getJavascriptForFooter(),
    $tablist->getJavascriptForFooter(),
);
$render->render(
    [
        'scripts' => $scripts,
    ],
    $string,
    'footer.html'
);

$mysqli->close();
