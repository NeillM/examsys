$.Class.extend("MEE.Text",
{
    // initialisze an element. pass in a token and the data that is associated with the token
    init: function (token, eldata) {
        this._name = 'MEE.Elem';
        this.args = new Array();
        this.latex = token.latex;
        this.type = token.type;
        this.eldata = jQuery.extend({}, eldata);
        this.size = token.size;
        this.sizer = 0;
        if (token.closing) {
            this.eldata.rb = token.closing;
        }
        if (token.sizer)
            this.sizer = token.sizer;
        if (token.type == "extsingle") {
            this.main = new MEE.ElemSetBasic("", new Object(), this);
            this.eldata.lb = token.latex;
            if (eldata.text)
                this.eldata.lb = eldata.text;
        } else {
            this.main = new MEE.ElemSetBasic(token.latex, eldata, this);
        }
    },
});
