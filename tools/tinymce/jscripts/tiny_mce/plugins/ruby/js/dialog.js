tinyMCEPopup.requireLangPack();

var RubyDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		f.rb.value = tinyMCEPopup.editor.selection.getContent();
        console.log(f.rb.value);
	},

    preview : function() {
        // Insert the contents from the input into the document
        var ruby = '<ruby><rb>' + document.forms[0].rb.value + '</rb><rp>{</rp><rt>' + document.forms[0].rt.value + '</rt><rp>}</rp></ruby>';
        document.getElementById('rubyPreview').innerHTML = ruby;
    },

	insert : function() {
		// Insert the contents from the input into the document
		var ruby = '<ruby><rb>' + document.forms[0].rb.value + '</rb><rp>{</rp><rt>' + document.forms[0].rt.value + '</rt><rp>}</rp></ruby>';
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, ruby);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(RubyDialog.init, RubyDialog);
