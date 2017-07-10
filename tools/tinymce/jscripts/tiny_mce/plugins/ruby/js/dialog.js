tinyMCEPopup.requireLangPack();

var RubyDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
        var rubyValue = tinyMCEPopup.editor.dom.getParent(tinyMCEPopup.editor.selection.getNode(),'span');
        var elements = rubyValue.getElementsByTagName('ruby');
        for (var i=0; i<elements.length; i++) {
            if(elements[i].textContent.indexOf('{')  > -1 ){
                f.rb.value += elements[i].textContent.split("{")[0]+'('+ elements[i].getElementsByTagName('rt')[0].innerHTML +')';
            }else{
                f.rb.value += elements[i].textContent;
            }
        }
	},

	edit : function(){
        //editing the value
	    var rubyValue = tinyMCEPopup.editor.dom.getParent(tinyMCEPopup.editor.selection.getNode(),'span');
        rubyValue.innerHTML = '';
        rubyValue.innerHTML = this.constructTag();
        tinyMCEPopup.close();
	},

    preview : function() {
        // Preview the contents from the input into the preview area
        document.getElementById('rubyPreview').innerHTML = this.constructTag();
    },

	insert : function() {
		// Insert the contents from the input into the document
        var rubyValue1 = tinyMCEPopup.editor.dom.getParent(tinyMCEPopup.editor.selection.getNode(),'span');
        if(rubyValue1 != null){
            this.edit();
        }else{
            var ruby = "<span class='wrap'>"+ this.constructTag() +"</span>";
		    tinyMCEPopup.editor.execCommand('mceInsertContent', false, ruby);
		    tinyMCEPopup.close();
        }
	},

	constructTag : function () {
        /**
         * Constructing the string of tags.
         */
        var rbValue = document.forms[0].rb.value;
        var ruby = '';
        for(var l=0; l<rbValue.length; l++) {
            if(rbValue.charAt(l) == "(") {
                l++;
                ruby += '<rp>{</rp><rt>' + rbValue.charAt(l) ;
            }else if(rbValue.charAt(l) == ",") {
                l++;
                ruby +=  rbValue.charAt(l) ;
            }else if(rbValue.charAt(l) == ")") {
                ruby += '</rt><rp>}</rp></ruby>';
            }else {
                ruby += '<ruby><rb>' + rbValue.charAt(l) + '</rb>';
                if(rbValue.charAt(l+1) != "("){
                    ruby += '</ruby>';
                }
            }
        }
        return ruby;
    }
};

tinyMCEPopup.onInit.add(RubyDialog.init, RubyDialog);
