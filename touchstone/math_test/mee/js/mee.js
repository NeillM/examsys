// MEE Loader


function require(jspath) {
    document.write('<script type="text/javascript" src="' + jspath + '"><\/script>');
}

function loadjscssfile(filename, filetype) {
    if (!filetype) {
        // no filetype specified, get this from the filename
        var lastdotpos = filename.lastIndexOf(".");
        if (lastdotpos)
            filetype = filename.substr(lastdotpos + 1);
    }
    if (filetype == "js") { //if filename is a external JavaScript file
        document.write('<script type="text/javascript" src="' + filename + '"><\/script>');
    }
    else if (filetype == "css") { //if filename is an external CSS file
        document.write('<link rel="stylesheet" type="text/css" href="' + filename + '"><\/link>');
    }
}

var filesadded = "" //list of files already added

function checkloadjscssfile(filename, filetype) {
    if (filesadded.indexOf("[" + filename + "]") == -1) {
        loadjscssfile(filename, filetype)
        filesadded += "[" + filename + "]" //List of files added in the form "[filename1],[filename2],etc"
    }
    else
        alert("file already added!")
}

// load jquery and plugins needed
checkloadjscssfile("mee/jquery/jquery-1.6.1.js");
checkloadjscssfile("mee/jquery/jquery.caret.js");
checkloadjscssfile("mee/jquery/jquery.class.js");
checkloadjscssfile("mee/jquery/jquery.pxem.js");
checkloadjscssfile("mee/jquery/jquery.scale9.js");
checkloadjscssfile("mee/jquery/jquery.textarea.js");
//checkloadjscssfile("mee/jquery/jquery.json2xml.js");
checkloadjscssfile("mee/jquery/jquery.xml2json.js");

checkloadjscssfile("mee/js/mee.main.js");
checkloadjscssfile("mee/js/mee.main.edit.js");
checkloadjscssfile("mee/js/mee.main.display.js");
checkloadjscssfile("mee/js/mee.tools.html.js");
checkloadjscssfile("mee/js/mee.parser.js");
checkloadjscssfile("mee/js/mee.data.js");
checkloadjscssfile("mee/js/mee.data.tex.js");
checkloadjscssfile("mee/js/mee.data.chars.js");

checkloadjscssfile("mee/js/mee.elem.js");
checkloadjscssfile("mee/js/mee.elem.accent.js");
checkloadjscssfile("mee/js/mee.elem.boxed.js");
checkloadjscssfile("mee/js/mee.elem.space.js");
checkloadjscssfile("mee/js/mee.elem.input.js");
checkloadjscssfile("mee/js/mee.elem.answer.js");

checkloadjscssfile("mee/js/mee.elemset.js");
checkloadjscssfile("mee/js/mee.elemset.normal.js");
checkloadjscssfile("mee/js/mee.elemset.basic.js");
checkloadjscssfile("mee/js/mee.elemset.array.js");

checkloadjscssfile("mee/js/mee.toolbar.js");
checkloadjscssfile("mee/js/mee.base.js");

checkloadjscssfile("mee/css/toolbar.css");
checkloadjscssfile("mee/css/main.css");
checkloadjscssfile("mee/css/edit.css");
checkloadjscssfile("mee/css/fonts.css");

// toolbar definitions
if (!Array.indexOf) {
    Array.prototype.indexOf = function (obj) {
        for (var i = 0; i < this.length; i++) {
            if (this[i] == obj) {
                return i;
            }
        }
        return -1;
    } 
}

var debug_text = "";

// on page load call MEE init
$().ready(function () {
    // search page for items to display and create an instnce of MEE per item
    setTimeout("MEE.Base.Render();", 100);

    $('.debug').html(debug_text);
});
