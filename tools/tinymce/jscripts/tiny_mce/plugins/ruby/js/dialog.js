tinyMCEPopup.requireLangPack();

var RubyDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		var rubyValue = tinyMCEPopup.editor.selection.getNode();
        f.rt.value = rubyValue.getElementsByTagName('rt')[0].innerHTML;
        f.rb.value = rubyValue.textContent.split("{")[0];

        console.log(rubyValue);
	},

	edit : function(){
        var rubyValue = tinyMCEPopup.editor.selection.getNode();

        var ruby = '<ruby><rb>' + document.forms[0].rb.value + '</rb><rp>{</rp><rt>' + document.forms[0].rt.value + '</rt><rp>}</rp></ruby>';
        rubyValue.innerHTML = ruby;
        //tinyMCEPopup.close();
	},

    preview : function() {
        // Insert the contents from the input into the document
		var rbValue = document.forms[0].rb.value;
		var ruby = '<ruby><rb>'+ rbValue.charAt(0) +'</rb>';
		for(var l=1; l<rbValue.length; l++) {

			if(rbValue.charAt(l) == "[") {
				l++;
            	ruby += '<rp>{</rp><rt>' + rbValue.charAt(l) ;
			}else if(rbValue.charAt(l) == ",") {
                l++;
                ruby +=  rbValue.charAt(l) ;
            }else if(rbValue.charAt(l) == "]") {
				l++;
                ruby += '</rt><rp>}</rp></ruby><ruby><rb>' + rbValue.charAt(l) + '</rb>';
			}else {
                ruby += '<ruby><rb>' + rbValue.charAt(l) + '</rb>';
			}
		}
        document.getElementById('rubyPreview').innerHTML = ruby;
    },

	insert : function() {
		// Insert the contents from the input into the document
        var rbValue = document.forms[0].rb.value;
        var ruby = '<ruby><rb>'+ rbValue.charAt(0) +'</rb>';
        for(var l=1; l<rbValue.length; l++) {

            if(rbValue.charAt(l) == "[") {
                l++;
                ruby += '<rp>{</rp><rt>' + rbValue.charAt(l) ;
            }else if(rbValue.charAt(l) == ",") {
                l++;
                ruby +=  rbValue.charAt(l) ;
            }else if(rbValue.charAt(l) == "]") {
                l++;
                ruby += '</rt><rp>}</rp></ruby><ruby><rb>' + rbValue.charAt(l) + '</rb>';
            }else {
                ruby += '<ruby><rb>' + rbValue.charAt(l) + '</rb>';
            }
        }

		tinyMCEPopup.editor.execCommand('mceInsertContent', false, ruby);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(RubyDialog.init, RubyDialog);
