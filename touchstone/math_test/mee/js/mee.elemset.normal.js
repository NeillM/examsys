MEE.ElemSet.extend("MEE.ElemSetNormal",
{
    html_elem: null,
    latex: null,
    eldata: null,

    // prototype stuff
    init: function (latex, par) {
        this._name = 'MEE.ElemSetNormal';
        //alert(latex);
        this.latex = latex;
        this.parent = par;

        var parser = new MEE.Parser();

        this.elements = parser.parse(latex, par);

        // set the parent
        for (var i = 0; i < this.elements.length; i++) {
            this.elements[i].parent = this;
        }

        // store any eldata to be applied to the parent element for when parent is set later on
        if (par && parser.apply_to_parent) {
            for (var ttname in parser.apply_to_parent) {
                var value = parser.apply_to_parent[ttname];
                par.eldata[ttname] = value;
            }
        }

        // element sets do not strictly have eldata, but this is for text and display style, so alters the depth function here
        this.eldata = parser.apply_to_thisset;
    },

    toHTML: function (depth) {
        this.html_elem = $('<span>');
        this.html_elem.addClass('mee_elemset');

        // sort out depth
        if (this.eldata.displaystyle)
            depth = 1;
        if (this.eldata.textstyle)
            depth = 2;
        this.depth = depth;
        this.html_elem.attr('depth', depth);

        var dodepth = true;
        if (this.parent && this.parent.depth == this.depth)
            dodepth = false;

        if (dodepth) {
            var size = MEE.Data.fontsizes[this.depth];
            $(this.html_elem).css('font-size', size);
        }


        for (var i = 0; i < this.elements.length; i++) {
            var elemhtml = this.elements[i].toHTML(depth);
            this.html_elem.append(elemhtml);
        }

        if (this.elements.length == 0) {
            var blank = $('<span>');
            blank.html(MEE.Data.blankspace);
            this.html_elem.append(blank);
        }

        return this.html_elem;
    },

    ////////////////////////////
    // DEBUG STUFF BELOW HERE //
    ////////////////////////////
    dump: function () {
        var dump = "<div class='dump_set'>";
        /*dump += "<div class='dump_set_info'>Normal: " + this.latex + "</div>";*/
        dump += "<div class='dump_set_elemes'>";
        for (var i = 0; i < this.elements.length; i++) {
            dump += this.elements[i].dump();
        }
        dump += "</div></div>";

        return dump;
    }
});
