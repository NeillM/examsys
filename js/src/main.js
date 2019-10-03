// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Requirejs configuration for the backend i.e. admin pages.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs.config({
    // By default load any module IDs from js.
    baseUrl: '/js/modules',
    // paths to modules.
    paths: {
        jquery: "/js/jquery-1.11.1.min",
        jqueryvalidate: "/js/jquery.validate.min",
        jqueryui: "/js/jquery-ui-1.10.4.min",
        jquerytablesorter: "/js/jquery.tablesorter.min",
        qunit: "/node_modules/qunit/qunit/qunit",
        mathjax: "/node_modules/mathjax/MathJax.js?config=TeX-MML-AM_HTMLorMML&amp;delayStartupUntil=configured",
        editor: "editor.min",
        tinyMCE: "/plugins/texteditor/plugin_tinymce3_texteditor/tinymce/jscripts/tiny_mce/tiny_mce",
        three: "/node_modules/three/build/three.min",
        colourpicker: "/tools/colour_picker/js/colour_picker.min",
        campuses: "/admin/campus/js/campuses.min",
        campusesvalidate: "/admin/campus/js/campuses_validate.min",
        extsys: "/admin/external/js/extsys.min",
        extsysvalidate: "/admin/external/js/extsys_validate.min",
        oauth: "/admin/oauth/js/oauth.min",
        oauthclients: "/admin/oauth/js/oauthclients.min",
        oauthvalidate: "/admin/oauth/js/oauthclients_validate.min",
        plugins: "/admin/plugins/js/plugins.min",
        pluginsvalidate: "/admin/plugins/js/plugins_validate.min",
        ltisearchusers: "/LTI/js/search_users.min",
        rogoconfig: "requireconfig.min",
        jsxls: "jsxls.min",
        lang: "lang.min",
        textboxmarking: "textboxmarking.min",
        start: "start.min",
        systemtooltips:"system_tooltips.min",
        helplauncher: "helplauncher.min",
        classtotals: "class_totals.min",
        toprightmenu: "toprightmenu.min",
        help: "help.min",
        hofstee: "hofstee.min",
        questionedit: "questionedit.min",
        questioneditcalc: "/plugins/questions/enhancedcalc/js/modules/questionedit.min",
        questioneditextmatch: "/plugins/questions/extmatch/js/modules/questionedit.min",
        questioneditsct: "/plugins/questions/sct/js/modules/questionedit.min",
        questioneditlikert: "/plugins/questions/likert/js/modules/questionedit.min",
        questioneditdich: "/plugins/questions/dichotomous/js/modules/questionedit.min",
        questioneditblank: "/plugins/questions/blank/js/modules/questionedit.min",
        questioneditrandom: "/plugins/questions/random/js/modules/questionedit.min",
        questionstartmrq: "/plugins/questions/mrq/js/modules/start.min",
        questionstartrank: "/plugins/questions/rank/js/modules/start.min",
        questionstartextmatch: "/plugins/questions/extmatch/js/modules/start.min",
        datecopy: "datecopy.min",
        questionmapping: "questionmapping.min",
        paperdetails: "paperdetails.min",
        leadinpopup: "questionleadinpopup.min",
        questionstatus: "questionstatus.min",
        qualitative: "qualitative.min",
        typecoursefilter: "typecoursefilter.min",
        list: "list.min",
        errorlist: "errorlist.min",
        facultieslist: "facultieslist.min",
        summativedetails: "summativedetails.min",
        modulessidebar: "modulessidebar.min",
        courseslist: "courseslist.min",
        ebellist: "ebellist.min",
        sessionslist: "sessionslist.min",
        sessions: "sessions.min",
        schoolslist: "schoolslist.min",
        keywordlist: "keywordlist.min",
        modules: "modules.min",
        popupmenu: "popup_menu.min",
        peerreview: "peerreview.min",
        performance: "performance.min",
        osce: "osce.min",
        user: "user.min",
        usersearch: "usersearch.min",
        usermodules: "usermodules.min",
        state: "state.min",
        alert: "alert.min",
        recyclelist: "recyclelist.min",
        questionsearch: "questionsearch.min",
        questionlist: "questionlist.min",
        sidebar: "sidebar.min",
        stdset: "stdset.min",
        admin: "admin.min",
        papertype: "papertype.min",
        moduleoptions: "moduleoptions.min",
        folder: "folder.min",
        folderproperties: "folderproperties.min",
        papersidebar: "papersidebar.min",
        menu: "menu.min",
        stdsetreview: "stdsetreview.min",
        paperproperties: "paperproperties.min",
        freqdisc: "freqdisc.min",
        review: "review.min",
        reviewcomment: "reviewcomment.min",
        reference: "reference.min",
        userindex: "userindex.min",
        qti: "qti.min",
        register: "register.min",
        calcremark: "/plugins/questions/enhancedcalc/js/modules/remark.min",
        student: "student.min",
        reassignuser: "reassignuser.min",
        addquestionsbuttons: "addquestionsbuttons.min",
        addquestionscommon: "addquestionscommon.min",
        keywordsquestionlist: "keywordsquestionlist.min",
        lablist: "lablist.min",
        form: "form.min",
        moduleform: "moduleform.min",
        newpaperform: "newpaperform.min",
        studentnote: "studentnote.min",
        keyword: "keyword.min",
        calendar: "calendar.min",
        referancelist: "referancelist.min",
        mapping: "mapping.min",
        mappingsessions: "mappingsessions.min",
        mappingsessionlist: "mappingsessionlist.min",
        mappingsidebar: "mappingsidebar.min",
        textboxfinalise: "textbox_finalise.min",
        ui: "ui.min",
        questionlink: "questionlink.min",
        invigilator: "invigilator.min",
        announcement: "announcement.min",
        lti: "lti.min",
        answer_hotspot: "html_questions/html5.answer.hotspot.min",
        html5helper: "html_questions/html5.helper.min",
        html5images: "html_questions/html5.image.min",
        html5: "html_questions/html5.min",
        hotspot_listener: "html_questions/html5.listener.hotspot.min",
        html5listener: "html_questions/html5.listener.min",
        html5_button: "html_questions/html5.menu.button.min",
        html5_chk: "html_questions/html5.menu.checkbox.min",
        html5_filler: "html_questions/html5.menu.filler.min",
        html5_group: "html_questions/html5.menu.group.min",
        hotspot_layerzone: "html_questions/html5.menu.hotspot.layerzone.min",
        html5_menuitem: "html_questions/html5.menu.item.min",
        html5_menu: "html_questions/html5.menu.min",
        hotspot_analysis: "html_questions/html5.question.hotspot.analysis.min",
        hotspot_answer: "html_questions/html5.question.hotspot.answer.min",
        hotspot_colourselector: "html_questions/html5.question.hotspot.colourselector.min",
        hotspot_correction: "html_questions/html5.question.hotspot.correction.min",
        hotspot_edit: "html_questions/html5.question.hotspot.edit.min",
        hotspot: "html_questions/html5.question.hotspot.min",
        hotspot_layer: "html_questions/html5.question.hotspot.layer.min",
        hotspot_script: "html_questions/html5.question.hotspot.script.min",
        hotspot_shape: "html_questions/html5.question.hotspot.shape.min",
        hotspot_standardset: "html_questions/html5.question.hotspot.standardset.min",
        html5_question: "html_questions/html5.question.min",
        qsharedf: "html5/qsharedf.min",
        qarea: "html5/qarea.min",
        qlabelling: "html5/qlabelling.min",
        mathjaxpreview: "mathjax/preview.min",
        log: "log.min",
        threeshared: "media/three/threeshared.min",
        TrackballControls: "media/three/controlers/TrackballControls.min",
        CSS2DRenderer: "media/three/renderers/CSS2DRenderer.min",
        CSS2DObject: "media/three/objects/CSS2DObject.min",
        PDBLoader: "media/three/loaders/PDBLoader.min",
        PLYLoader: "media/three/loaders/PLYLoader.min",
        DDSLoader: "media/three/loaders/DDSLoader.min",
        OBJLoader: "media/three/loaders/OBJLoader.min",
        MTLLoader: "media/three/loaders/MTLLoader.min",
        MaterialCreator: "media/three/objects/MaterialCreator.min",
        ParserState: "media/three/objects/ParserState.min",
        obj: "media/three/obj.min",
        ply: "media/three/ply.min",
        pdb: "media/three/pdb.min",
        jqueryvalidatecalc: "/plugins/questions/enhancedcalc/js/modules/validateuser.min",
        jquerycalc: "/plugins/questions/enhancedcalc/js/modules/validatequestion.min",
        jqueryleadinonly: "validation/jquery.leadin-only.min",
        jqueryarea: "/plugins/questions/area/js/modules/validatequestion.min",
        jqueryhotspot: "/plugins/questions/hotspot/js/modules/validatequestion.min",
        jqueryblank: "/plugins/questions/blank/js/modules/validatequestion.min",
        jquerydich: "/plugins/questions/dichotomous/js/modules/validatequestion.min",
        jqueryextmatch: "/plugins/questions/extmatch/js/modules/validatequestion.min",
        jquerykeyword: "/plugins/questions/keyword_based/js/modules/validatequestion.min",
        jquerymatrix: "/plugins/questions/matrix/js/modules/validatequestion.min",
        jquerymcq: "/plugins/questions/mcq/js/modules/validatequestion.min",
        jquerymrq: "/plugins/questions/mrq/js/modules/validatequestion.min",
        jqueryrandom: "/plugins/questions/random/js/modules/validatequestion.min",
        jqueryrank: "/plugins/questions/rank/js/modules/validatequestion.min",
        jquerysct: "/plugins/questions/sct/js/modules/validatequestion.min"
    },
    shim: {
        // Mathjax configration.
        mathjax: {
            exports: "MathJax",
            init: function () {
                MathJax.Hub.Config({
                    messageStyle: "none",
                    showMathMenu: false,
                    showMathMenuMSIE: false,
                    tex2jax: {inlineMath: [['$$','$$'], ['[tex]', '[/tex]'], ['[texi]', '[/texi]']], displayMath: [['$$$','$$$']]}
                });
                MathJax.Hub.Startup.onload();
                return MathJax;
            }
        },
        tinyMCE: {
            exports: 'tinyMCE',
            init: function () {
                return this.tinyMCE;
            }
        },
        // Non AMD libraries that need jquery.
        jqueryvalidate: ["jquery"],
        jqueryui: ["jquery"],
        jquerytablesorter: ["jquery"],
    },
});

// Read Rogo configuration and enable/disable functionality accordingly
requirejs(['rogoconfig'], function(config) {
    if (config.mathjax) {
        requirejs(['mathjax']);
    }
});

requirejs(['systemtooltips'], function (TOOLTIPS) {
    var tooltips = new TOOLTIPS();
    tooltips.init();
});

requirejs(['helplauncher', 'jquery'], function (HELPLAUNCHER, $) {
    $(function() {
        $('#helplink').click(function () {
            HELPLAUNCHER.launchHelp($(this).attr('data-id'), $(this).attr('data-role'));
        });
    });
});

requirejs(['toprightmenu'], function (MENU) {
    var menu = new MENU();
    menu.init();
});

requirejs(['ui'], function (UI) {
    var ui = new UI();
    ui.init();
});
