$.Class.extend("MEE.ElemSet",
{
    single: false,
    isarray: 0,

    toHTML: function () {

    },

    sortAlign: function () {
        this.align = new MEE.Align();

        
        if (this.elements) {
            for (var i = 0; i < this.elements.length; i++) {
                var elalign = this.elements[i].sortAlign();
                this.align.Merge(elalign);

                this.elements[i].html_elem.attr('al',elalign.toString());
            }
        }

        // when an element set has a lot of elements, its not quite wide enough
        //this.align.width += this.elements.length;

        /*if (this.align.top)
            $(this.html_elem).css('margin-top', this.align.top + 'px');
        if (this.align.bottom)
            $(this.html_elem).css('margin-bottom', this.align.bottom + 'px');*/
        this.html_elem.attr('al', this.align.toString());

        return this.align;
    },


    ///////////////////////////
    // EDIT STUFF BELOW HERE //
    ///////////////////////////
    insertElemBeforeInput: function (elem) {
        // find input element
        for (var i = 0; i < this.elements.length; i++) {
            var curelem = this.elements[i];
            var name = curelem.__proto__.Class.shortName;
            if (name == "ElemInput") {
                // we have found the input element, insert the elem before it
                this.elements.splice(i, 0, elem);
                return i;
            }
        }
        alert("Cannot find input element");
        this.elements.push(elem);
        return this.elements.length - 1;
    },

    getInputPos: function () {
        // find input element
        for (var i = 0; i < this.elements.length; i++) {
            var curelem = this.elements[i];
            var name = curelem.__proto__.Class.shortName;
            if (name == "ElemInput") {
                return i;
            }
        }
        return -1;
    },

    insertHTMLFor: function (elem, index) {
        var html = elem.toHTML();

        var input = this.elements[index + 1];
        html.insertBefore(input.html_elem);
    },

    getElemBeforeInput: function () {
        for (var i = 0; i < this.elements.length; i++) {
            var curelem = this.elements[i];
            var name = curelem.__proto__.Class.shortName;
            if (name == "ElemInput") {
                // we have found the input element, insert the elem before it
                this.elements[i - 1].offset = i - 1;
                return this.elements[i - 1];
            }
        }
    },

    removeInput: function () {
        for (var i = 0; i < this.elements.length; i++) {
            var curelem = this.elements[i];
            var name = curelem.__proto__.Class.shortName;
            if (name == "ElemInput") {
                // we have found the input element, insert the elem before it
                this.elements.splice(i, 1);
                return;
            }
        }
    },

    getElementOffset: function (elem) {
        for (var i = 0; i < this.elements.length; i++) {
            if (elem == this.elements[i])
                return i;
        }
        return -1;
    }

});
