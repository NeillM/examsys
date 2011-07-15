MEE.Main.extend("MEE.Display",
{
    inline: false,
    elementset: null,

    // class extensions for using as an editor
    init: function (element, inline) {
        this.inline = inline;

        var showcomp = 1;
        if ($(element).hasClass('nocomp')) showcomp = 0;
        if ($(element).hasClass('comp')) showcomp = 1;

        var latex = element.innerHTML;
        if (latex.substr(0, 2) == "\\[") {
            latex = latex.substr(2, latex.length - 4);
        }

        if (latex.charAt(0) == "<")
            return;

        this.elementset = new MEE.ElemSetNormal(latex, null);

        var depth = 1;
        if (inline) depth = 2;
        var res = this.elementset.toHTML(depth);

        if (inline) {
            $(element).html("");
            $(element).append(res);
            this.elementset.sortAlign();
            $(element).css('height', this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
            if ($.browser.msie && document.documentMode == 7) {
                $(element).css('margin-top', this.elementset.align.top + 'px');
                $(element).css('margin-bottom', this.elementset.align.bottom + 'px');
            } else {
                $(element).css('padding-top', this.elementset.align.top + 'px');
                $(element).css('padding-bottom', this.elementset.align.bottom + 'px');
            }

        } else if (showcomp) {

            // show side by side comparison with alernate render
            $(element).html("");
            var table = $('<table>');
            var tr = $('<tr>');
            var td1 = $('<td>');
            td1.attr("width", "50%");
            var td2 = $('<td>');
            td2.attr("width", "50%");
            td2.css('border-left', '1px solid blue');
            td2.css('padding-left', '4px');
            td2.css('vertical-align', 'top');
            var span = $('<div>');
            span.css('border', '1px solid blue');

            table.append(tr);
            $(table).attr('width', '100%');
            $(table).css('font-size', '200%');
            tr.append(td1);
            tr.append(td2);

            var img = $('<img>');
            img.attr('src', 'http://latex.codecogs.com/gif.latex?\\LARGE ' + latex);
            $(td2).append(img);

            var eqn = $('<div>');
            eqn.css('font-size', '14px');
            eqn.text(latex);

            $(element).append(table);
            $(span).append(res);
            $(td1).append(span);
            $(td1).append(eqn);

            this.elementset.sortAlign();
            //$(span).css('height',this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
            if ($.browser.msie && document.documentMode == 7) {
                $(span).css('margin-top', this.elementset.align.top + 'px');
                $(span).css('margin-bottom', this.elementset.align.bottom + 'px');
            } else {
                $(span).css('padding-top', this.elementset.align.top + 'px');
                $(span).css('padding-bottom', this.elementset.align.bottom + 'px');
            }

        } else {

            $(element).html("");
            $(element).append(res);
            this.elementset.sortAlign();

            //$(element).css('height',this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
            if ($.browser.msie && document.documentMode == 7) {
                $(element).css('margin-top', this.elementset.align.top + 'px');
                $(element).css('margin-bottom', this.elementset.align.bottom + 'px');
            } else {
                $(element).css('padding-top', this.elementset.align.top + 'px');
                $(element).css('padding-bottom', this.elementset.align.bottom + 'px');
            }

            //$(element).css('border', '1px solid green');

        }
    }
});
