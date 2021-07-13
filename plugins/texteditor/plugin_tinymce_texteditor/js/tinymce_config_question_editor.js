requirejs(['tinyMCE', 'rogoconfig', 'jquery'], function (Tinymce, config, $) {
    Tinymce.init({
        selector: ".editorStandard",
        plugins: "visualchars nonbreaking paste lists code table image preview",
        external_plugins: {
            'ruby-annotation': config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/js/plugins/ruby-annotation/plugin.min.js",
            'maths-equation-editor': config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/js/plugins/maths-equation-editor/plugin.min.js",
        },
        language : 'en',
        language_load : true,
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
        toolbar: "cut copy paste | undo | bold italic underline | subscript superscript | maths-equation-editor | ruby-annotation | alignleft aligncenter alignright | numlist bullist | image | table | code | preview |",
    });

    Tinymce.init({
        selector: ".editorSimple",
        plugins: "visualchars nonbreaking paste code preview",external_plugins: {
            'maths-equation-editor': config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/js/plugins/maths-equation-editor/plugin.min.js",
        },
        language : 'en',
        language_load : true,
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
        toolbar: "cut copy paste | undo | bold italic underline | subscript superscript | maths-equation-editor | code | preview |",
    });

    Tinymce.init({
        selector: ".editorBasic",
        plugins: "visualchars nonbreaking paste",
        a11y_advanced_options: true,
        image_advtab: true,
        image_dimensions: false,
        image_uploadtab: true,
        images_file_types: 'gif,jpg,jpeg,png',
        images_upload_url: config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/upload.php",
        menubar: false,
        statusbar: false,
        toolbar: "cut copy paste pastetext| undo | removeformat |",
    });
});
