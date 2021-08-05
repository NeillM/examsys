requirejs(['tinyMCE', 'rogoconfig', 'jquery'], function (Tinymce, config, $) {
  Tinymce.init({
    selector: ".editorStandard",
    plugins: "visualchars nonbreaking paste lists code table template help",
    a11y_advanced_options: true,
    image_advtab: true,
    image_dimensions: false,
    image_uploadtab: true,
    images_file_types: 'gif,jpg,jpeg,png',
    images_upload_url: config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/upload.php",
    menubar: false,
    statusbar: false,
    toolbar: "template | cut copy paste | undo | bold italic underline | subscript superscript | alignleft aligncenter alignright | numlist bullist | table | code | help",
    templates: config.cfgrootpath +  "/plugins/texteditor/plugin_tinymce_texteditor/js/emailtemplates.json",
    help_tabs: ['shortcuts', 'keyboardnav'],
    language: config.lang,
    language_url: '/plugins/texteditor/plugin_tinymce_texteditor/js/langs/' + config.lang + '.js',
  });
});
