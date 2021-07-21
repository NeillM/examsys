requirejs(['tinyMCE', 'rogoconfig', 'jquery'], function (Tinymce, config, $) {
    Tinymce.init({
        selector: ".editorStandard",
        plugins: "visualchars nonbreaking paste lists code table image help",
        a11y_advanced_options: true,
        image_advtab: true,
        image_dimensions: false,
        image_uploadtab: true,
        images_file_types: 'gif,jpg,jpeg,png',
        images_upload_url: config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/upload.php",
        menubar: false,
        statusbar: false,
        toolbar: "cut copy paste | undo | bold italic underline | subscript superscript | alignleft aligncenter alignright | numlist bullist | image | table | code | help",
        help_tabs: ['shortcuts', 'keyboardnav'],
    });
});
