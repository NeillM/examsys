requirejs(['tinyMCE', 'rogoconfig', 'jquery'], function (Tinymce, config, $) {
    Tinymce.init({
        selector: ".editorStandard",
        plugins: "visualchars nonbreaking paste lists code table help",
        external_plugins: {
            'maths-equation-editor': config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/js/plugins/maths-equation-editor/plugin.min.js",
        },
        language: config.lang,
        language_url: '/plugins/texteditor/plugin_tinymce_texteditor/js/langs/' + config.lang + '.js',
        icons_url: config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/icons/icons.js",
        icons: 'rogo',
        a11y_advanced_options: true,
        image_advtab: true,
        image_dimensions: false,
        image_uploadtab: true,
        images_file_types: 'gif,jpg,jpeg,png',
        images_upload_url: config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/upload.php",
        menubar: false,
        statusbar: false,
        toolbar: "cut copy paste | undo | bold italic underline | subscript superscript | maths-equation-editor | alignleft aligncenter alignright | numlist bullist | table | code | help",
        help_tabs: ['shortcuts', 'keyboardnav'],
    });
});
