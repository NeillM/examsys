
// handles boxed stuff
MEE.Elem.extend("MEE.ElemBoxed",
{
    toHTML: function (depth) {
        var res = this._super(depth);

        this.html_box = $('<span>');
        this.html_box.html(MEE.Data.blankspace);
        this.html_box.addClass('mee_boxed');
        this.html_elem.append(this.html_box);
        return res;
    },

    sortAlign: function () {
        var res = this._super();

        this.html_box.css('left', '0px');
        this.html_box.css('top', -this.main.align.top + 'px');
        this.html_box.css('padding-right', this.main.align.width - MEE.Data.blankspacesize(this.html_box) - 3 + 'px');
        this.html_box.css('padding-top', this.main.align.top + 'px');
        this.html_box.css('padding-bottom', this.main.align.bottom + 'px');
        return res;
    }
});
