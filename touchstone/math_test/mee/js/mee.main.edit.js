MEE.Main.extend("MEE.Edit",
{
    //#region Static definitiona
    toolbar: null,
    toolbarelem: null,
    currentedit: null,
    redraw: function (element) {
        // DOESNT WORK! MAKE IT SO THIS ISNT NEEDED
        // fix for chrome that causes a page refresh to be made.
        // only works on so much depth, no idea how to made really nested stuff update properly
        if (jQuery.browser.webkit) {
            if (this.removeme)
                clearTimeout(this.removeme);
            $('.REMOVE_ME').remove();
            var temp = $('<span>');
            temp.html(MEE.Data.blankspace);
            temp.addClass("REMOVE_ME");
            $(element).append(temp);
            this.removeme = setTimeout("$('.REMOVE_ME').remove();", 10);
        }

    }
    //#endregion
},
{
    // class extensions for using as an editor
    //#region Initialization
    init: function (element) {
        this.debug = 0;
        this.basedepth = 1;
        this.mode = -1;

        var lochash = window.location.hash;
        if (lochash)
            $(element).val(lochash.substr(1));

        // save and hide input element
        if (this.debug) {
            $(element).css('width', '600px');
        } else {
            $(element).css("display", "none");
        }

        this.inputelement = element;
        this.inputname = element.id;

        // build edit container
        this.editdiv = $('<div>');
        this.editdiv.addClass('mee_edit');
        $(element).before(this.editdiv);

        this.eqndiv = $('<div>');
        this.eqndiv.addClass('mee_edit_eqn');
        $(this.editdiv).append(this.eqndiv);

        // if we dont already have one, build a toolbar
        if (!MEE.Edit.toolbar) {
            MEE.Edit.toolbar = new MEE.Toolbar(element);
            MEE.Edit.toolbar.loadToolBar();
        }

        // create raw input box
        this.rawinput = $('<textarea>');
        this.rawinput.addClass('mee_edit_raw');
        $(this.editdiv).prepend(this.rawinput);
        this.resizeRawInput();
        $(this.rawinput).autoResize();
        this.rawinput.val($(element).val());
        this.rawinput.trigger('change.dynSiz');
        this.rawinput.keyup(this.callback('rawKeyUp'));

        // take any content and render it into the element
        this.latex = element.value;

        // create input box
        this.input = new MEE.ElemInput();

        this.inputelembox = $('<input>');
        this.inputelembox.addClass('mee_elem_input_box');
        $(this.editdiv).prepend(this.inputelembox);

        this.curElemSet = null;

        // events for input box
        this.inputelem = this.input.html_elem;
        $(this.inputelembox).keydown(this.callback('inputKeyDown'));
        $(this.inputelembox).keyup(this.callback('inputKeyUp'));
        $(this.inputelembox).keypress(this.callback('inputKeyPress'));

        // click event for the container to make it active
        $(this.editdiv).click(this.callback('editdiv_click'));

        this.rebuildDisplay();

        this.modeRaw();

        this.rawinput.css('display', 'none');
        // dump element tree
        if (this.debug)
            $('.dump').html(this.elementset.dump());
    },
    //#endregion

    //#region Sort out equation display stuff

    // clear out the equation display and rebuild it
    rebuildDisplay: function () {
        this.elementset = new MEE.ElemSetNormal(this.latex, null);

        // create input box
        if (this.mode == 1 && this.active)
            this.elementset.elements.push(this.input);
        this.curElemSet = this.elementset;

        // build equation that is in the input
        this.html_elem = this.elementset.toHTML(this.basedepth);
        this.inputelem = null;
        if (this.mode == 1 && this.active)
            this.inputelem = this.input.html_elem;
        this.eqndiv.html("");
        this.eqndiv.append(this.html_elem);
        this.formatElemSet();
        $('.mee_elemsetbasic').click(this.callback('elementClick'));
        this.curElemSet = this.elementset;
    },

    clearAlign: function (elem) {
        // TODO: REMOVE THIS AND MAKE UNNECESSARY
        // this is a complete bodge, needs to be changed to only remove the align data in changed elements
        // will probably be not needed at all
        $(elem).css('padding', '');
        $(elem).css('margin', '');
        $(elem).children().each(this.callback('clearAlign'));
    },

    // format the top most element set with the padding required
    formatElemSet: function () {
        this.clearAlign(this.elementset.html_elem);

        this.elementset.sortAlign();
        $(this.eqndiv).css('height', this.elementset.align.height - (this.elementset.align.top + this.elementset.align.bottom) + 'px');
        $(this.eqndiv).css('padding-top', this.elementset.align.top + 6 + 'px');
        $(this.eqndiv).css('padding-bottom', this.elementset.align.bottom + 6 + 'px');
    },
    //#endregion

    //#region stuff for dealing with the content

    // set the current latex
    setLatex: function (latex, source) {
        this.latex = latex;

        if (source != "raw")
            this.rawinput.val(latex);
        if (source != "baseinput")
            $(this.inputelement).val(latex);

        window.location.hash = latex;

        this.rebuildDisplay();
    },

    addLatex: function (latex) {
        //alert(latex);
        if (this.mode == 0) {
            var newlatex = this.rawinput.val();
            var caret = this.rawinput.caret();
            var before = newlatex.substr(0, caret.start);
            var after = newlatex.substr(caret.end);

            if (latex.indexOf('$') == -1) {

                var padafter = false;
                if (latex.charAt(0) == "\\") {
                    if (after.charAt(0) != "[" & after.charAt(0) != "(" &
                    after.charAt(0) != "{" & after.charAt(0) != "\\") {
                        padafter = true; ;
                    }
                }

                // look up latex in commands and see if there are args needed
                var result = before;
                result += latex;

                var offset = result.length;
                if (padafter)
                    result += " ";
                result += after;

            } else {
                var inner = caret.text;

                var latex1 = latex.substr(0, latex.indexOf('$'));
                var latex2 = latex.substr(latex.indexOf('$') + 2);


                var padafter = false;
                if (latex2.charAt(latex2.length - 1) != "}") {
                    if (after.charAt(0) != "[" & after.charAt(0) != "(" &
                    after.charAt(0) != "{" & after.charAt(0) != "\\") {
                        padafter = true; ;
                    }
                }
                var padinner = false;
                if (latex1.charAt(latex1.length - 1) != "{") {
                    if (inner.charAt(0) != "[" & inner.charAt(0) != "(" &
                    inner.charAt(0) != "{" & inner.charAt(0) != "\\") {
                        padinner = true; ;
                    }
                }

                var result = before + latex1;
                if (padinner)
                    result += ' ';
                result += inner;
                var offset = result.length;
                result += latex2;
                if (padafter)
                    result += ' ';
                result += after;
            }
            this.setLatex(result);
            this.rawinput.focus();
            this.rawinput.caret(offset, offset);

        } else {

        }
    },

    // clear equation
    clear: function () {
        if (confirm("Are you sure you want to clear the equation?")) {
            this.setLatex("");
        }
    },

    // undo
    undo: function () {

    },

    // redo
    redo: function () {

    },
    //#endregion 

    //#region Mode change functions //

    // set mode to wysiwyg
    modeWYSIWYG: function () {
        if (this.mode == 1)
            return;

        this.mode = 1;
        this.rawinput.css('display', 'none');
        this.inputelembox.css('display', 'block');

        $('#mode_wysiwyg_check').attr('src', 'mee/images/toolbar/home_tick.png');
        $('#mode_raw_check').attr('src', 'mee/images/toolbar/home_tick_blank.png');
        this.rebuildDisplay();
    },

    // set mode to raw
    modeRaw: function () {
        if (this.mode == 0)
            return;

        this.mode = 0;
        this.rawinput.css('display', 'block');
        this.inputelembox.css('display', 'none');

        $('#mode_raw_check').attr('src', 'mee/images/toolbar/home_tick.png');
        $('#mode_wysiwyg_check').attr('src', 'mee/images/toolbar/home_tick_blank.png');
        this.rebuildDisplay();
    },
    //#endregion

    //#region Activation of edit area //
    // when an edit div is clicked
    editdiv_click: function () {
        if (this.mode == 1) {

        } else {
            this.rawinput.focus();
        }

        if (MEE.Edit.activeEdit == this)
            return false;

        // check for an active edit box, if there is on deactivate it
        if (MEE.Edit.activeEdit) {
            MEE.Edit.activeEdit.deactivate();
        }

        // activate current edit box
        this.activate();
        MEE.Edit.activeEdit = this;

        return false;
    },

    // activate this edit div
    activate: function () {

        if (this.active)
            return;
        this.active = true;

        $(this.editdiv).prepend(MEE.Edit.toolbarelem);
        MEE.Edit.toolbarelem.show();
        MEE.Edit.toolbar.currentEdit = this;
        MEE.Edit.activeEdit = this;
        MEE.Edit.toolbar.initEvents();
        if (this.mode == 0)
            this.rawinput.css('display', 'block');
        else
            this.inputelembox.css('display', 'block');
        this.rebuildDisplay();
        this.resizeRawInput();
        this.rawinput.focus();
    },

    // deactivate this edit div
    deactivate: function () {
        if (!this.active)
            return;
        this.active = false;
        MEE.Edit.toolbar.currentEdit = null;
        MEE.Edit.toolbar.closeTabs();
        MEE.Edit.toolbarelem.hide();
        MEE.Edit.activeEdit = null
        if (this.mode == 0)
            this.rawinput.css('display', 'none');
        else
            this.inputelembox.css('display', 'none');

        this.rebuildDisplay();
    },
    //#endregion

    //#region RAW Stuff

    // resize the raw input box to fit the width
    resizeRawInput: function () {
        if ($.browser.msie) {
            this.rawinput.css('width', $(this.editdiv).outerWidth() - 6 + 'px');
        } else {
            this.rawinput.css('width', $(this.editdiv).outerWidth() + 'px');
        }
    },

    // handle changes to the raw input
    rawKeyUp: function () {
        this.resizeRawInput();

        var latex = this.rawinput.val();
        if (latex == this.latex)
            return;

        this.setLatex(latex, "raw");
    },
    //#endregion

    //#region WYSIWYG Stuff

    // handle clicking on a element in the equation display
    elementClick: function (html_elem) {
        if (this.mode == 0)
            return;

        // need to find the element that contains the clicked element
        var elemid = $(html_elem).attr('elem');
        var elem = MEE.ElemSetBasic.basicelems[elemid];

        this.curElemSet.removeInput();
        this.curElemSet = elem.parent.parent;
        var offset = this.curElemSet.getElementOffset(elem.parent);
        this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
        this.curElemSet.elements.splice(offset + 1, 0, this.input);

        this.inputelembox.focus();
    },
    inputKeyUp: function (elem, event) {

    },

    inputKeyPress: function (elem, event) {
        // text entered, so parse it and add the input to the current element set
        var key = event.which;
        var newchar = String.fromCharCode(event.which);

        var curvalue = $(this.inputelembox).val() + newchar;

        var outputtext = new Object();
        outputtext.text = "";
        if (this.parseInput(curvalue, outputtext)) {
            $(this.inputelembox).val(outputtext.text);
            this.formatElemSet();

            this.inputelem.focus();
            return false;
        }
    },

    inputKeyDown: function (elem, event) {
        this.processInput(event);

        MEE.Edit.redraw();

        if (this.debug)
            $('.dump').html("<div>" + this.curElemSet.latex + "</div>" + this.elementset.dump());
    },

    // handle input to the wysiwyg editor
    processInput: function (event) {
        // build cursor naviagtion here
        var key = event.which;

        if (key == 8) { // delete
            // need to remove the element after the input. If the element is has more than just a simple main (ie things like subscripts
            // and super scripts), highlight it. then if press delete again delete it.

        } else if (key == 46) { // backspace
            // need to remove the element before the input. If the element is has more than just a simple main (ie things like subscripts
            // and super scripts), highlight it. then if press delete again delete it.

        } else if (key == 37) { // left 
            //////////////////////////////
            //#region LEFT LEFT LEFT LEFT LEFT //
            //////////////////////////////
            // need to move the input box left a place

            // get position of input
            var offset = this.curElemSet.getInputPos();


            if (offset == 0) {
                // we are at the beginning of this element set, so need to move the cursor to the parent set
                var parelem = this.curElemSet.parent;
                if (!parelem)
                    return;
                var parset = parelem.parent;

                // remove the input from the current set
                this.curElemSet.removeInput();

                if (parset.isarray) {
                    // if parent element is an array, then try to move to the previous column
                    var pos = parset.getPosition(this.curElemSet);
                    this.curElemSet = parset;

                    if (pos.col > 0) {
                        // prev col available, move to it
                        var newcol = pos.col - 1;
                        this.curElemSet = this.curElemSet['row' + pos.row]['col' + newcol];
                        this.curElemSet.elements.push(this.input);
                        this.curElemSet.html_elem.append(this.inputelem);

                        return;
                    }

                    // no previous column, so move out of the array to the parent elementset
                    parelem = parset.parent;
                    parset = parelem.parent;

                }

                // nothing in this element set to move to, so move to the parent element set before the current element
                this.curElemSet = parset;
                var offset = this.curElemSet.getElementOffset(parelem);
                this.inputelem.insertBefore(this.curElemSet.elements[offset].html_elem);
                this.curElemSet.elements.splice(offset, 0, this.input);

                return;
            }

            // get previous element
            var prevelem = this.curElemSet.elements[offset - 1];

            if (!prevelem)
                return;

            this.curElemSet.removeInput();

            // is the previous element an array, if so try to move to its top row end
            if (prevelem.main && prevelem.main.isarray) {
                // array here
                this.curElemSet = prevelem.main;
                if (this.curElemSet.rows > 0 || this.curElemSet.cols > 0) {
                    this.curElemSet = this.curElemSet.row0['col' + (this.curElemSet.cols - 1)];
                    this.curElemSet.elements.push(this.input);
                    this.curElemSet.html_elem.append(this.inputelem);

                    return;
                }
            }


            if (prevelem.eldata.arg0_as_main && prevelem.main.elements) {
                // arg0_as_main on previous element, then move to the end of its elem set
                prevelem.main.elements.push(this.input);
                prevelem.main.html_elem.append(this.inputelem);
                this.curElemSet = prevelem.main;

                return;
            }

            // normal movement to previous element
            this.inputelem.insertBefore(this.curElemSet.elements[offset - 1].html_elem);
            this.curElemSet.elements.splice(offset - 1, 0, this.input);

            //#endregion
        } else if (key == 39) { // right
            ///////////////////////////////////
            //#region  RIGHT RIGHT RIGHT RIGHT RIGHT //
            ///////////////////////////////////

            // get position of input
            var offset = this.curElemSet.getInputPos();

            // are we at the end of the current element set?
            if (offset == this.curElemSet.elements.length - 1) {
                // get parent set
                var parelem = this.curElemSet.parent;
                if (!parelem)
                    return;

                var parset = parelem.parent;
                // remove input from current set
                this.curElemSet.removeInput();

                // if parent is an array, then try to move to the previous columns
                if (parset.isarray) {
                    var pos = parset.getPosition(this.curElemSet);
                    this.curElemSet = parset;

                    if (pos.col < parset.cols - 1) {
                        var newcol = pos.col + 1;
                        this.curElemSet = this.curElemSet['row' + pos.row]['col' + newcol];
                        this.curElemSet.elements.unshift(this.input);
                        this.curElemSet.html_elem.prepend(this.inputelem);

                        return;
                    }

                    // out the array!
                    parelem = parset.parent;
                    parset = parelem.parent;

                }

                // move to the parent set before the current element
                this.curElemSet = parset;
                var offset = this.curElemSet.getElementOffset(parelem);
                this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
                this.curElemSet.elements.splice(offset + 1, 0, this.input);

                return;
            }

            var nextelem = this.curElemSet.elements[offset + 1];

            if (!nextelem)
                return;

            this.curElemSet.removeInput();

            if (nextelem.main && nextelem.main.isarray) {
                // array here
                this.curElemSet = nextelem.main;
                if (this.curElemSet.rows > 0 || this.curElemSet.cols > 0) {
                    this.curElemSet = this.curElemSet.row0.col0;
                    this.curElemSet.elements.unshift(this.input);
                    this.curElemSet.html_elem.prepend(this.inputelem);

                    return;
                }
            }

            if (nextelem.eldata.arg0_as_main && nextelem.main.elements) {
                // arg0_as_main on previous element, then move to the end of its elem set
                nextelem.main.elements.unshift(this.input);
                nextelem.main.html_elem.prepend(this.inputelem);
                this.curElemSet = nextelem.main;
            } else {
                // no arg0_as_main
                this.curElemSet.elements.splice(offset + 1, 0, this.input);
                this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
            }
            /*this.inputelem.insertAfter(this.curElemSet.elements[offset+1].html_elem);
            this.curElemSet.elements.splice(offset+1, 0, this.input);*/

            //#endregion

        } else if (key == 38) { // up
            ////////////////////////////////
            //#region  UP UP UP UP UP UP UP UP UP //
            ////////////////////////////////

            // check for a subscript element to navigate to
            var offset = this.curElemSet.getInputPos();
            var curelem = this.curElemSet.elements[offset - 1];
            if (curelem && curelem.superscript) {
                this.curElemSet.removeInput();
                this.curElemSet = curelem.superscript;
                this.curElemSet.elements.push(this.input);
                this.curElemSet.html_elem.append(this.inputelem);

                return;
            }

            // check previous element is a array or not, if so then goto its top row
            if (curelem && curelem.main && curelem.main.isarray) {
                // array here
                if (curelem.main.rows > 0 || curelem.main.cols > 0) {
                    this.curElemSet.removeInput();
                    this.curElemSet = curelem.main;
                    this.curElemSet = this.curElemSet.row0['col' + (this.curElemSet.cols - 1)];
                    this.curElemSet.elements.push(this.input);
                    this.curElemSet.html_elem.append(this.inputelem);

                    return;
                }
            }

            // get parent element and set
            var parelem = this.curElemSet.parent;
            var savedcurElem = this.curElemSet;

            while (parelem) {
                // iterate up parent elements to try and find somewhere to move to

                var parset = parelem.parent;

                // if this is a superscript, and parent is an array, try to move to the prev array row
                // TODO!!!!!

                // if parent is a superscript, then move back down to the element
                if (this.curElemSet.subscript) {
                    savedcurElem.removeInput();
                    this.curElemSet = parset;
                    var offset = this.curElemSet.getElementOffset(parelem);
                    this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
                    this.curElemSet.elements.splice(offset + 1, 0, this.input);

                    return;
                }

                // if parent is an array, then move down an item
                if (parset.isarray) {
                    var pos = parset.getPosition(this.curElemSet);

                    if (pos.row > 0) {
                        savedcurElem.removeInput();
                        this.curElemSet = parset;

                        var newrow = pos.row - 1;
                        this.curElemSet = this.curElemSet['row' + newrow]['col' + pos.col];
                        this.curElemSet.elements.unshift(this.input);
                        this.curElemSet.html_elem.prepend(this.inputelem);

                        return;
                    }
                }

                this.curElemSet = parset;
                var parelem = this.curElemSet.parent;
            }
            this.curElemSet = savedcurElem;

            //#endregion

        } else if (key == 40) { // down
            //////////////////////////////
            //#region  DOWN DOWN DOWN DOWN DOWN //
            //////////////////////////////
            // does the opposite of up basically

            var offset = this.curElemSet.getInputPos();


            // check for a subscript element to navigate to
            var curelem = this.curElemSet.elements[offset - 1];
            if (curelem && curelem.subscript) {
                this.curElemSet.removeInput();
                this.curElemSet = curelem.subscript;
                this.curElemSet.elements.push(this.input);
                this.curElemSet.html_elem.append(this.inputelem);

                return;
            }

            // check previous element is a array or not, if so then goto its bottom row
            if (curelem && curelem.main && curelem.main.isarray) {
                // array here
                if (curelem.main.rows > 0 || curelem.main.cols > 0) {
                    this.curElemSet.removeInput();
                    this.curElemSet = curelem.main;
                    this.curElemSet = this.curElemSet['row' + (this.curElemSet.rows - 1)]['col' + (this.curElemSet.cols - 1)];
                    this.curElemSet.elements.push(this.input);
                    this.curElemSet.html_elem.append(this.inputelem);

                    return;
                }
            }
            // get parent element and set
            var parelem = this.curElemSet.parent;
            var savedcurElem = this.curElemSet;

            while (parelem) {
                // iterate up parent elements to try and find somewhere to move to

                var parset = parelem.parent;


                // if this is a subscript, and parent is an array, try to move to the next array row
                // TODO !!!!

                // if parent is a superscript, then move back down to the element
                if (this.curElemSet.superscript) {
                    savedcurElem.removeInput();
                    this.curElemSet = parset;
                    var offset = this.curElemSet.getElementOffset(parelem);
                    this.inputelem.insertAfter(this.curElemSet.elements[offset].html_elem);
                    this.curElemSet.elements.splice(offset + 1, 0, this.input);

                    return;
                }

                // if parent is an array, then move down an item
                if (parset.isarray) {
                    var pos = parset.getPosition(this.curElemSet);

                    if (pos.row < parset.rows - 1) {
                        savedcurElem.removeInput();
                        this.curElemSet = parset;

                        var newrow = pos.row + 1;
                        this.curElemSet = this.curElemSet['row' + newrow]['col' + pos.col];
                        this.curElemSet.elements.unshift(this.input);
                        this.curElemSet.html_elem.prepend(this.inputelem);

                        return;
                    }
                }

                this.curElemSet = parset;
                var parelem = this.curElemSet.parent;
            }
            this.curElemSet = savedcurElem;
            //#endregion
        }

    },


    // parse some text and add it to elements
    parseInput: function (text, o) {
        // strip all but keyboard characters that we care about, as firefox passes all char codes here (chrome only passes visible so not needed, no idea about IE)

        text = text.replace(/[^a-zA-z0-9 .\,\/\<\>\?\;\:\"\'\`\!\@\#\$\%\^\&\*\(\)\[\]\{\}\_\+\=\-\|\\]+/g, '');
        //text = text.replace(/[^a-zA-z0-9\\\+\-\=\_\^\$\/]+/g,'');
        if (text == "")
            return false;

        var parser = new MEE.Parser();
        var tokens = parser.tokenize(text);

        var lastvalid = -1;
        for (var i = tokens.length; i > 0; i--) {
            if ('valid' in tokens[i - 1] && tokens[i - 1].valid) {
                lastvalid = i;
                break;
            }
        }

        if (lastvalid == -1)
            return false;

        // check for sub and superscript tokens
        for (var i = 0; i < lastvalid; i++) {
            var token = tokens[i];
            if (token.type == "subscript" || token.type == "superscript") {
                var elem = this.curElemSet.getElemBeforeInput();
                if (token.type == "subscript") {
                    // do we have a subscript?
                    if (!elem.subscript) {
                        elem.subscript = new MEE.ElemSetNormal(token.latex);
                        elem.createSubscriptHTML();
                        elem.html_elem.append(elem.html_subscript);
                    }

                    if (token.latex == "") {
                        // if the token is empty, then we have no more text available so have typed a _

                        // remove the input element from the current element set
                        this.curElemSet.removeInput();

                        // move input to the end of the subscript element set
                        elem.subscript.elements.push(this.input);

                        // move the input html to the subscript element html
                        this.inputelem.appendTo(elem.subscript.html_elem);

                        // set current elemset to subscript
                        this.curElemSet = elem.subscript;
                        this.curElemSet.single = true;
                    }
                    //o.text = "";
                    //return true;

                } else if (token.type == "superscript") {
                    // do we have a superscript?
                    if (!elem.superscript) {
                        elem.superscript = new MEE.ElemSetNormal(token.latex);
                        elem.createSuperscriptHTML();
                        elem.html_elem.append(elem.html_superscript);
                    }

                    if (token.latex == "") {
                        // remove the input element from the current element set
                        this.curElemSet.removeInput();

                        // move input to the end of the superscript element set
                        elem.superscript.elements.push(this.input);

                        // move the input html to the subscript element html
                        this.inputelem.appendTo(elem.superscript.html_elem);

                        // set current elemset to subscript
                        this.curElemSet = elem.superscript;
                        this.curElemSet.single = true;
                        //o.text = "";
                        //return true;
                    }

                }
            } else if (token.type == "arg") {
                if (token.incompletearg) {
                    this.curElemSet.single = false;
                } else {
                    var tokens3 = new Array();
                    tokens3.push(token);
                    var elem = parser.buildelements(tokens3);
                    elem = elem[0];

                    var prevelem = this.curElemSet.getElemBeforeInput();

                    if (prevelem.args > 0) {
                        if (prevelem.arg01_as_upperlower) {
                            // fraction or binom (+ others)
                            if (prevelem.args == 2) {
                                // top argument
                                prevelem.AddUpper(elem);
                            } else {
                                // bottom argument
                                prevelem.AddLower(elem);
                            }
                        } else {

                        }
                    }
                }
            } else if (token.type == "extsingle" && token.latex == "}") {

                // shuffle up to the parent element set
                this.curElemSet.removeInput();

                // need to find the parent element set, and the element that contains this element set
                var elem = this.curElemSet.parent;
                this.curElemSet = elem.parent;

                // sort the html out
                this.inputelem.insertAfter(elem.html_elem);

                // move this.input to the correct position in curElemSet.elements
                var offset = elem.offset;
                this.curElemSet.elements.splice(offset + 1, 0, this.input);

            } else {

                var tokens3 = new Array();
                tokens3.push(token);
                var elem = parser.buildelements(tokens3);
                elem = elem[0];



                var offset = this.curElemSet.insertElemBeforeInput(elem);
                this.curElemSet.insertHTMLFor(elem, offset);

                if (this.curElemSet.single) {
                    // element is only a single element, so shuffle up to the parent element set
                    this.curElemSet.removeInput();

                    // need to find the parent element set, and the element that contains this element set
                    var elem = this.curElemSet.parent;
                    this.curElemSet = elem.parent;

                    // sort the html out
                    this.inputelem.insertAfter(elem.html_elem);

                    // move this.input to the correct position in curElemSet.elements
                    var offset = elem.offset;
                    this.curElemSet.elements.splice(offset + 1, 0, this.input);
                }
            }
        }

        for (var i = lastvalid; i < tokens.length; i++) {
            if (tokens[i].type == "command")
                o.text += "\\";
            o.text += tokens[i].latex;
        }

        return true;
    }
    //#endregion 
});
