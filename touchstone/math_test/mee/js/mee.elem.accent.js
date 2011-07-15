
// handles accents
MEE.Elem.extend("MEE.ElemAccent",
{
    // create the accent in html
    // if its a wide accent then increase the font size
    // TODO: inplement scaled accents here such as arrows
    toHTML: function (depth) {
        var res = this._super(depth);
        if (this.args.length < 1)
            return res;

        if (this.eldata.accent_wide) {
            var textlen = this.args[0].latex.length;
            if (textlen == 1) {
                $(this.html_main).css('font-family', 'MathJax_Size1');
                $(this.html_main).css('top', '0.1em');
            } else if (textlen == 2) {
                $(this.html_main).css('font-family', 'MathJax_Size2');
                $(this.html_main).css('top', '-0.45em');
            } else if (textlen == 3) {
                $(this.html_main).css('font-family', 'MathJax_Size3');
                $(this.html_main).css('top', '-0.55em');
            } else if (textlen > 3) {
                $(this.html_main).css('font-family', 'MathJax_Size4');
                $(this.html_main).css('top', '-0.85em');
            }
        }
        this.html_elem.css('position', 'relative');
        return res;
    },

    // sort out the alignment of the element
    sortAlign: function () {
        this.align = new MEE.Align();

        this.main.sortAlign();
        this.align.height = this.main.align.height;
        this.align.width = this.main.align.width;

        if (this.subscript)
            this.subscript.sortAlign();

        if (this.superscript)
            this.superscript.sortAlign();

        if (this.args.length < 1) {
            $(this.html_main).css('top', '-0.3em');
            return this.align;
        }

        this.args[0].sortAlign();

        var accentwidth = this.main.align.width;
        var textwidth = this.args[0].align.width;

        // vector character in MathJax_Main is boggered, so this.eldata.nopadleft was added to get around this.
        // changed font to Arial Unicode so no need for it anymore
        var tall = '0.22em';

        if (this.eldata.handledots) {
            $(this.html_main).css('position', 'absolute');
            $(this.html_main).css('top', '-0.57em');
            var offset = Math.floor((accentwidth - textwidth) / 2);
            if (offset > 0) {
                $(this.html_arg0).css('padding-left', offset + 'px');
                $(this.html_elem).css('padding-right', offset + 'px');
            } else {
                offset = Math.abs(offset);
                $(this.html_main).css('padding-left', offset + 'px');
            }

            if (hasTall(this.args[0].latex)) {
                $(this.html_main).css('top', "-0.79em");
            }

        } else if (this.eldata.nopadleft) {
            var accentwidth = 0.45;
            accentwidth = $(accentwidth).toPx({ 'scope': this.html_elem });
            var accentoffset = 0.5;
            accentoffset = $(accentoffset).toPx({ 'scope': this.html_elem });
            var offset = Math.floor((textwidth - accentwidth) / 2);
            $(this.html_main).css('position', 'relative');
            $(this.html_main).css('left', offset + accentoffset + 'px');


        } else if (textwidth > accentwidth && !this.eldata.nopadleft) {
            var offset = Math.floor((textwidth - accentwidth) / 2);
            $(this.html_main).css('padding-left', offset + 'px');
        } else if (accentwidth > textwidth && !this.eldata.nopadleft) {
            var offset = Math.floor((accentwidth - textwidth) / 2);
            $(this.html_main).css('left', -offset + 'px');
        }

        if (hasTall(this.args[0].latex) && !this.eldata.handledots) {
            $(this.html_main).css('bottom', tall);
        }

        // if we have scripts, then process them
        if (this.subscript || this.superscript)
            this.alignSS();

        this.align.width = Math.max(textwidth, accentwidth);
        return this.align;
    }
});
