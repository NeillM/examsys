// create MEE class
$.Class.extend("MEE.Base",
{
    Render: function () {
        // build all recursive definitions
        MEE.Base.buildDefs();
        MEE.Base.to_process = new Array();

        $("div.mee").each(this.callback('processDiv'));
        $("span.mee").each(this.callback('processSpan'));
        $("input.mee").each(this.callback('processInput'));

        MEE.Base.createProgress(MEE.Base.to_process.length);

        MEE.Base.current = 0;

        setTimeout("MEE.Base.ProcessNext()", 1);
        $(document.body).click(this.callback('pageClick'));
    },

    pageClick: function () {
        if (MEE.Edit.toolbar && MEE.Edit.toolbar.currentEdit) {
            var edit = MEE.Edit.toolbar.currentEdit;
            edit.deactivate();
        }
        return false;
    },

    //#region Process elements
    ProcessNext: function () {
        i = MEE.Base.current++;
        if (i >= MEE.Base.to_process.length) {
            MEE.Base.removeProgress();
            return;
        }

        var proc = this.to_process[i];
        MEE.Base.process(proc);
        MEE.Base.updateProgress();

        setTimeout("MEE.Base.ProcessNext()", 1);
    },

    processDiv: function (elem) {
        var proc = new Object();
        proc.elem = elem;
        proc.type = 'display';
        proc.inline = false;

        MEE.Base.to_process.push(proc);
    },

    processSpan: function (elem) {
        var proc = new Object();
        proc.elem = elem;
        proc.type = 'display';
        proc.inline = true;
        MEE.Base.to_process.push(proc);
    },

    processInput: function (elem) {
        var proc = new Object();
        proc.elem = elem;
        proc.type = 'edit';
        MEE.Base.to_process.push(proc);
    },

    process: function (proc) {
        if (proc.type == "display") {
            var meeeqn = new MEE.Display(proc.elem, proc.inline);
        } else if (proc.type == "edit") {
            var meeeqn = new MEE.Edit(proc.elem);
        }
    },
    //#endregion 

    //#region Progress bar
    createProgress: function () {
        MEE.Base.html_progress = $('<div>');
        MEE.Base.html_progress.addClass('mee_progress');
        MEE.Base.html_progress.html("Processing equations: 0%");
        $(document.body).append(MEE.Base.html_progress);
    },

    updateProgress: function () {
        var pct = Math.ceil(MEE.Base.current / MEE.Base.to_process.length * 100);
        MEE.Base.html_progress.html("Processing equations: " + pct + "%");
    },

    removeProgress: function () {
        MEE.Base.html_progress.remove();
    },
    //#endregion

    // go through all the tex data, and copy any base attributes into the elements
    buildDefs: function () {
        $.each(MEE.Data.commands, function (cmd, data) {
            if (data.base) {
                // we have a base required to copy the data from
                var base = MEE.Data.commands[data.base];
                if (!base)
                    return;

                $.each(base, function (basecmd, baseval) {
                    if (basecmd in data) {
                        var k = 0;
                    } else {
                        data[basecmd] = baseval;
                    }
                });
            }
        });

        var k = 0;
    }
},
{
});

//#region class to handle element alignment
$.Class.extend("MEE.Align",
{
    width: 0,
    height: 0,
    top: 0,
    bottom: 0,
    init: function () {
        this.width = 0;
        this.height = 0;
        this.top = 0;
        this.bottom = 0;
    },
    Merge: function (align) {
        this.width += align.width;
        if (this.height == 0) {
            this.height = align.height;
        } else {
            if (align.top > this.top)
                this.height += align.top - this.top;
            if (align.bottom > this.bottom)
                this.height += align.bottom - this.bottom;
        }
        this.top = Math.max(this.top, align.top);
        this.bottom = Math.max(this.bottom, align.bottom);
    },
    toString: function () {
        var res = "";
        res += "w " + this.width;
        res += " h " + this.height;
        res += " t " + this.top;
        res += " b " + this.bottom;
        return res;
    }
});
//#endregion