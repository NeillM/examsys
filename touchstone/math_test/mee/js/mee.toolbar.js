$.Class.extend("MEE.Toolbar",
{
    //#region static stuff, keep track of all instances of the main classes here
    defs: {},
    activetab: null,
    popupmenuid: 1,
    curpopupmenu: null,
    nomouseover: false,
    leavecount: 0,

    images: {
        '102x22': 1,
        '220x58': 1,
        '220x32': 1,
        '220x35': 1,
        '60x69': 1,
        '75x22': 1,
        '110x22': 1,
        '53x24': 1,
        '50x24': 1,
        '100x24': 1,
        '100x36': 1,
        '32x32': 1,
        '96x96': 1,
        '160x36': 1,
        '120x36': 1,
        '64x64': 1
    },

    appendElement: function (base, element, classlist, content) {
        var newelem = $('<' + element + '>');
        if (classlist) {
            $(newelem).addClass(classlist);
        }
        if (content) {
            $(newelem).html(content);
        }
        $(base).append(newelem);
        return newelem;
    },

    getToolbarFromClass: function (item) {
        var classList = $(item).attr('class').split(/\s+/);
        var result = "";
        $.each(classList, function (index, item) {
            var parts = item.split(/:/);
            if (parts.length == 2 && parts[0] == "toolbar") {
                result = parts[1];
            }
        });
        return result;
    }
    //#endregion
},
{
    // main class for dealing with a equation editor element
    tbbase: null,
    inputelem: null,
    eventsinit: false,

    init: function (element) {
        this.inputelem = element;
        this.tbbase = MEE.Toolbar.getToolbarFromClass(element);
        this.currentEdit = null;
    },

    loadToolBar: function () {
        $.ajax({
            type: "GET",
            url: "mee/toolbar/toolbar.xml",
            dataType: "xml",
            success: this.callback("buildToolBar")
        });
    },

    //#region Build Toolbar html
    buildToolBar: function (temp, xml) {
        var toolbar = $.xml2json(xml); // this.xmlToToolbar(xml); // MEE.Toolbar.defs[this.tbbase];
        //MEE.Toolbar.defs["xml"] = toolbar;
        var main = $('<div>');
        this.html_elem = main;
        main.addClass('mee_toolbar');
        var main_list = MEE.Toolbar.appendElement(main, "div", "mt_main");
        var tabs_list = MEE.Toolbar.appendElement(main, "div", "mt_tabs");

        // add orb menu to tree
        var home = MEE.Toolbar.appendElement(main_list, "div", "mt_home");
        var home_link = MEE.Toolbar.appendElement(home, "a", "", "");
        home_link.attr('href', '#home');

        var homemenu = MEE.Toolbar.appendElement(main, "div", "mt_home_popup");
        var homeinner = MEE.Toolbar.appendElement(homemenu, "div", "mt_home_inner");

        for (var k = 0; k < toolbar.home.items.length; k++) {
            var data = toolbar.home.items[k];
            if (data.spacer) {
                var homeitem = MEE.Toolbar.appendElement(homeinner, "div", "mt_home_popup_spacer");
            } else {
                var homeitem = MEE.Toolbar.appendElement(homeinner, "div", data._class);
                var image = MEE.Toolbar.appendElement(homeitem, "img");
                image.attr("src", 'mee/images/toolbar/' + data.image);
                var text = MEE.Toolbar.appendElement(homeitem, "span", "mt_home_popup_item_label");
                if (data.checked == 1 || data.checked == -1) {
                    var check = MEE.Toolbar.appendElement(homeitem, "img", "mt_home_popup_check");
                    if (data.checked == 1) {
                        check.attr("src", 'mee/images/toolbar/home_tick.png');
                    } else {
                        check.attr("src", 'mee/images/toolbar/home_tick_blank.png');
                    }
                    if (data.id) {
                        check.attr('id', data.id + '_check');
                    }
                }
                text.html(data.name);
                if (data.command) {
                    homeitem.data('command', data.command);
                    homeitem.click(this.callback('itemClick'));
                }

                if (data.id) {
                    homeitem.attr('id', data.id);
                    image.attr('id', data.id + '_img');
                }
            }
        }

        // create tabs
        for (var r = 0; r < toolbar.tabs.length; r++) {
            var tab = toolbar.tabs[r];
            if (!tab) continue;

            // create tab and header link
            var tab_elem = MEE.Toolbar.appendElement(main_list, "div", "mt_tab");
            var link = MEE.Toolbar.appendElement(tab_elem, "a", "", tab.name);
            link.attr('href', '#' + tab.id);


            // create panes container
            var panes = MEE.Toolbar.appendElement(tabs_list, "div", "mt_tabblock");
            panes.attr('id', tab.id);

            if (!tab.panes) continue;

            // for all panes, add em to panes container
            for (var s = 0; s < tab.panes.length; s++) {
                var pane = tab.panes[s];
                if (!pane) continue;

                var pane_cont = MEE.Toolbar.appendElement(panes, "div", "mt_tabpanecont");
                var pane_elem = MEE.Toolbar.appendElement(pane_cont, "div", "mt_tabpane");
                var pane_label = MEE.Toolbar.appendElement(pane_cont, "div", "mt_tabpanelabel", pane.name);

                var divider = MEE.Toolbar.appendElement(panes, "div", "mt_tabdivider");

                // add pane header name

                if (!pane.items) continue;

                var itemcount = pane.items.length;

                // if panes type is icons, then add all icons to it
                if (pane.type == "icons") {
                    for (var u = 0; u < pane.items.length; u++) {
                        var paneitem = pane.items[u];
                        if (!paneitem) continue;

                        temp = MEE.Toolbar.appendElement(pane_elem, "div", "tb_symbol");
                        temp.data('latex', paneitem.latex);
                        MEE.Toolbar.appendElement(temp, "a", "", paneitem.display);
                    }

                    // if pane type is menu then add all menu items
                } else if (pane.type == "bigicons") {
                    for (var u = 0; u < pane.items.length; u++) {
                        var paneitem = pane.items[u];
                        if (!paneitem) continue;

                        temp = MEE.Toolbar.appendElement(pane_elem, "div", "tb_bigicons");

                        temp.data('latex', paneitem.latex);
                        var div = MEE.Toolbar.appendElement(temp, "div", "icon");
                        var img = MEE.Toolbar.appendElement(div, "img", "");
                        img.attr("src", 'mee/images/tbicons/' + paneitem.image);
                        MEE.Toolbar.appendElement(temp, "div", "label", paneitem.display);

                    }

                    // if pane type is menu then add all menu items
                } else if (pane.type == "list") {
                    for (var u = 0; u < pane.items.length; u++) {
                        var paneitem = pane.items[u];
                        if (!paneitem) continue;

                        temp = MEE.Toolbar.appendElement(pane_elem, "div", "tb_" + pane.type);

                        temp.data('latex', paneitem.latex);
                        if (!paneitem.image)
                            paneitem.image = 'blank_1x16.png';

                        var div = MEE.Toolbar.appendElement(temp, "span", "icon");
                        var img = MEE.Toolbar.appendElement(div, "img", "");
                        img.attr("src", 'mee/images/tbicons/' + paneitem.image);

                        var label = MEE.Toolbar.appendElement(temp, "span", "label", paneitem.display);

                        if (paneitem._class)
                            label.addClass(paneitem._class);

                        if (pane.itemwidth)
                            temp.css('width', pane.itemwidth + 'px');
                    }

                    if (!pane.itemwidth)
                        pane.itemwidth = 103;
                    itemcount = Math.ceil(pane.items.length / 3);
                    pane.width = itemcount * pane.itemwidth;
                    // if pane type is menu then add all menu items
                } else if (pane.type == "menus" || pane.type == "hmenus") {

                    var menuclass = "tb_menu";
                    var menuimg = "mee/images/toolbar/arrow_down.png";
                    if (pane.type == "hmenus") {
                        menuclass = "tb_menu_horiz";
                        menuimg = "mee/images/toolbar/arrow_down-16.png";
                    }

                    // add menus to pane
                    for (var t = 0; t < pane.items.length; t++) {
                        var paneitem = pane.items[t];

                        if (!paneitem) continue;

                        var menuitemconta = MEE.Toolbar.appendElement(pane_elem, "div", menuclass);
                        menuitemconta.attr('id', "popupmenubutton" + MEE.Toolbar.popupmenuid);
                        menuitemconta.attr('popuptype', pane.type);
                        menuitemconta.attr('popupmenu', MEE.Toolbar.popupmenuid);
                        menuitemconta.addClass('tb_menu_button');

                        var menuitemcont = MEE.Toolbar.appendElement(menuitemconta, "a");
                        menuitemcont.attr('popupmenu', MEE.Toolbar.popupmenuid);

                        // click event for popup menus
                        //$(menuitemcont).click( this.callback('menuClick') );

                        temp = MEE.Toolbar.appendElement(menuitemcont, "div", "icon");
                        temp = MEE.Toolbar.appendElement(temp, "img");
                        if (paneitem.image) {
                            temp.attr("src", 'mee/images/tbicons/' + paneitem.image);
                        }
                        temp.attr("alt", paneitem.display);

                        temp = MEE.Toolbar.appendElement(menuitemcont, "div", "text", paneitem.display);

                        temp = MEE.Toolbar.appendElement(menuitemcont, "div", "arrow");
                        temp = MEE.Toolbar.appendElement(temp, "img");
                        temp.attr('src', menuimg);

                        if (!paneitem.sections) continue;



                        // add menu popup div to document
                        var menupopup = MEE.Toolbar.appendElement(main, "div", "tb_popupmenu");
                        //var menupopup = MEE.Toolbar.appendElement(pane_elem, "div", "tb_popupmenu");
                        if (pane.type == "hmenus")
                            menupopup.addClass("tb_popupmenu_horiz");
                        else
                            menupopup.addClass("tb_popupmenu_vert");

                        menupopup.css("display", "none");
                        menupopup.attr('id', "popupmenu" + MEE.Toolbar.popupmenuid);
                        var menuinner = MEE.Toolbar.appendElement(menupopup, "div", "tb_popupmenu_inner");
                        var menufooter = MEE.Toolbar.appendElement(menupopup, "div", "tb_popupmenu_footer");

                        if (paneitem.popupwidth)
                            menuinner.css('width', paneitem.popupwidth + 'px');

                        // add panes to popup menu
                        for (var p = 0; p < paneitem.sections.length; p++) {
                            var section = paneitem.sections[p];
                            if (!section) continue;

                            MEE.Toolbar.appendElement(menuinner, "div", "tbpm_header", section.heading);
                            MEE.Toolbar.appendElement(menuinner, "div", "clear");

                            // add items to popup menu
                            if (section.items) {
                                for (var q = 0; q < section.items.length; q++) {
                                    var item = section.items[q];
                                    if (!item) continue;

                                    var _class = "tbpm_item";
                                    if (section._class)
                                        _class = section._class;
                                    var item_elem = MEE.Toolbar.appendElement(menuinner, "div", _class);
                                    if (item._class)
                                        item_elem.addClass(item._class);
                                    if (section.listwidth)
                                        item_elem.css('width', section.listwidth + 'px');
                                    item_elem.data('latex', item.latex);
                                    item_elem = MEE.Toolbar.appendElement(item_elem, "a");
                                    var div2 = MEE.Toolbar.appendElement(item_elem, "div", "icon");
                                    if (item.image) {
                                        var img = MEE.Toolbar.appendElement(div2, "img");
                                        img.attr('src', 'mee/images/tbicons/' + item.image);
                                    }
                                    MEE.Toolbar.appendElement(item_elem, "div", "label", item.display);
                                }
                            }

                        }
                        MEE.Toolbar.popupmenuid++;
                    }
                }

                if (itemcount) {
                    if (pane.type == "menus") {
                        pane.width = itemcount * 61;
                    } else if (pane.type == "bigicons") {
                        pane.width = itemcount * 60;
                    } else if (pane.type == "icons") {
                        itemcount = Math.ceil(itemcount / 3);
                        pane.width = itemcount * 23;
                    } else if (pane.type == "hmenus") {
                        itemcount = Math.ceil(itemcount / 3);
                        pane.width = itemcount * 103;
                    }
                }

                // check for a width specified
                if (pane.width) {
                    pane_cont.css("width", pane.width);
                    pane_elem.css("width", pane.width);
                    pane_label.css("width", pane.width);
                }

            }
        }

        MEE.Edit.toolbarelem = main;
        return main;
    },

    //#endregion

    //#region Set up events
    initEvents: function () {

        if (this.eventsinit)
            return;

        this.eventsinit = true;

        // remove right hand tabblock border
        $('.mt_tabblock').each(function () {
            $(this).children('div:last').css('border', 'none');
        });

        // sort mouse over images
        // home stuff
        this.setMouseImages('.mt_home_popup_item_big');
        this.setMouseImages('.mt_home_popup_item');

        // toolbar stuff
        this.setMouseImages('.tb_menu_horiz');
        this.setMouseImages('.tb_menu');

        this.setMouseImages('.tb_symbol');
        this.setMouseImages('.tb_bigicons');
        this.setMouseImages('.tb_list');

        // popup menu stuff
        this.setMouseImages('.tbpm_item');
        this.setMouseImages('.tbpm_list');
        this.setMouseImages('.tbpm_listbig');
        this.setMouseImages('.tbpm_grid');
        this.setMouseImages('.tbpm_gridbig');
        this.setMouseImages('.tbpm_gridxbig');



        // set up click handlers
        $(document).click(this.callback('docClick'));
        $('.mt_home').click(this.callback('homeClick'));

        $('.mt_tab').children('a').click(this.callback('tabClick'));

        $('.tb_menu_button').click(this.callback('menuClick'));

        $('.tb_symbol').click(this.callback('itemClick'));
        $('.tb_bigicons').click(this.callback('itemClick'));
        $('.tb_list').click(this.callback('itemClick'));

        $('.tbpm_item').click(this.callback('itemClick'));
        $('.tbpm_list').click(this.callback('itemClick'));
        $('.tbpm_listbig').click(this.callback('itemClick'));
        $('.tbpm_grid').click(this.callback('itemClick'));
        $('.tbpm_gridbig').click(this.callback('itemClick'));
        $('.tbpm_gridxbig').click(this.callback('itemClick'));

        // scale background on home panel
        $('.mt_home_popup').scale9Grid({ top: 4, bottom: 15, left: 4, right: 15 });

        this.hideHome();
        $('.mt_tabblock').hide();

    },
    //#endregion

    ///////////////////////
    //#region Global events
    /////////////////////////
    docClick: function (e) {
        this.hideMenus();
        this.hideHome();

        return true;
    },
    //#endregion

    ///////////////////////
    //#region Tab events
    /////////////////////////

    // tab clicked event
    tabClick: function (e) {
        this.hideHome();

        if (MEE.Toolbar.activetab == e) {
            this.closeTabs()
        } else {
            if (MEE.Toolbar.activetab != null) {
                this.closeTabs();
            }
            this.openTab(e);
        }

        if (MEE.Toolbar.activetab == null) {
            $(e).parent().parent().parent().parent().parent().css('height', '25px');
        } else {
            $(e).parent().parent().parent().parent().parent().css('height', '116px');
        }

        if (MEE.Edit.toolbar.currentEdit)
            MEE.Edit.toolbar.currentEdit.rebuildDisplay();
        return false;
    },

    // close any open tabs
    closeTabs: function () {

        if (MEE.Toolbar.activetab) {
            // if we have an active tab then close it
            var tabid = $(MEE.Toolbar.activetab).attr('href');
            $(tabid).hide();
            $(MEE.Toolbar.activetab).parent().removeClass('mt_tab_active');
            MEE.Toolbar.activetab = null;
            this.hideMenus();
        }
    },

    // open a new tab
    openTab: function (e) {
        var tabid = $(e).attr('href');
        $(tabid).show();
        $(e).parent().addClass('mt_tab_active');
        MEE.Toolbar.activetab = e;
    },
    //#endregion

    /////////////////////////////
    //#region menu events
    /////////////////////////////

    // menu dropdown clicked
    menuClick: function (element) {
        var menuid = $(element).attr('popupmenu');
        this.hideHome();
        if (menuid == MEE.Toolbar.curpopupmenu)
            return this.hideMenus();

        if (MEE.Toolbar.curpopupmenu)
            this.hideMenus();

        this.showMenu(menuid);
        return false;
    },

    // show a menu
    showMenu: function (menuid) {
        var menu = $('#popupmenu' + menuid);
        if (menu) {
            menu.css('display', 'block');

            // if we havent already sorted the menus background then do it
            if (!$(menu).attr('scaled')) {
                $(menu).children('.tb_popupmenu_footer').scale9Grid({ top: 0, bottom: 0, left: 2, right: 11 });
                $(menu).scale9Grid({ top: 4, bottom: 15, left: 4, right: 15 });
                $(menu).attr('scaled', 1);
            }

            // get button
            var menubutton = $('#popupmenubutton' + menuid);

            // position the menu relative to its button
            /*$(menu).css('left', menubutton.position().left);
            var top = menubutton.position().top + menubutton.outerHeight() - 1;
            if (top > 68) top = 68;
            $(menu).css('top', top);*/

            $(menu).css('left', menubutton.offset().left + 'px');
            $(menu).css('top', menubutton.offset().top + menubutton.innerHeight() + 'px');

            $(menubutton).trigger('mousedown');
            $(menubutton).data('showingmenu', 1);


        }
        MEE.Toolbar.curpopupmenu = menuid;
    },

    // hide any open menus
    hideMenus: function () {
        if (MEE.Toolbar.curpopupmenu) {
            var menu = $('#popupmenu' + MEE.Toolbar.curpopupmenu);
            $(menu).css('display', 'none');
            var menubutton = $('#popupmenubutton' + MEE.Toolbar.curpopupmenu);

            // Change this to a function that works on the item
            $(menubutton).data('showingmenu', 0);
            $(menubutton).trigger('mouseout');

            MEE.Toolbar.curpopupmenu = null;
        }
    },
    //#endregion

    ///////////////////////////
    //#region Home events
    ///////////////////////////
    homeClick: function (element) {
        if (this.home_showing) {
            this.hideHome();
        } else {
            this.showHome(element);
        }

        return false;
    },

    showHome: function (element) {
        this.home_showing = 1;
        var pos = $(element).position();
        $('.mt_home_popup').css('left', pos.left + 4 + 'px');
        $('.mt_home_popup').css('top', pos.top + 21 + 'px');
        $('.mt_home_popup').css('display', 'block');
    },

    hideHome: function () {
        /*if (this.home_showing == 2) {
        this.home_showing = 1;
        return;
        }*/
        //if (!this.home_showing)
        //    return;
        this.home_showing = 0;
        $('.mt_home_popup').hide();
    },
    //#endregion

    ///////////////////////
    //#region item events
    /////////////////////
    itemClick: function (item) {
        if (this.currentEdit == null)
            return true;

        this.hideMenus();
        this.hideHome();

        var command = $(item).data('command');
        if (command) {
            this.currentEdit[command]();
        }
        var latex = $(item).data('latex');
        if (latex) {
            this.currentEdit.addLatex(latex);
        }

        return true;
    },
    //#endregion

    ///////////////////////
    //#region random stuff
    ///////////////////////
    setMouseImages: function (selector) {
        $(selector).each(function () {
            var width = $(this).outerWidth();
            var height = $(this).outerHeight();

            var size = width + 'x' + height;

            if (size in MEE.Toolbar.images) {
                if (MEE.Toolbar.images[size] == -1)
                    return;

                $(this).mouseover(function () {
                    if ($(this).data('showingmenu') == 1)
                        return;
                    $(this).css('background-image', 'url(mee/images/toolbar/sizes/' + size + '-over.png)');
                });
                $(this).mouseout(function () {
                    if ($(this).data('showingmenu') == 1)
                        return;
                    $(this).css('background-image', '');
                });
                $(this).mousedown(function () {
                    if ($(this).data('showingmenu') == 1)
                        return;
                    $(this).css('background-image', 'url(mee/images/toolbar/sizes/' + size + '-click.png)');
                });
                $(this).mouseup(function () {
                    if ($(this).data('showingmenu') == 1)
                        return;
                    $(this).css('background-image', 'url(mee/images/toolbar/sizes/' + size + '-over.png)');
                });
            } else {
                icon = 'tall';

                // create template at the end of the page
                /*var div = $('<div>');
                div.css('clear', 'both');
                div.css('width', '500px');
                div.html("<div style='clear:both;'>" + size + "</div>");

                var over = $('<div>');
                over.css('width', width + 'px');
                over.css('height', height + 'px');
                over.css('margin-right', '20px');
                over.css('float', 'left');
                $(over).css('background-image', 'url(mee/images/toolbar/base/' + icon + '-over.png)');
                $(over).scale9Grid({ top: 4, bottom: 4, left: 4, right: 4 });
                div.append(over);

                over = $('<div>');
                over.css('width', width + 'px');
                over.css('height', height + 'px');
                over.css('float', 'left');
                $(over).css('background-image', 'url(mee/images/toolbar/base/' + icon + '-click.png)');
                $(over).scale9Grid({ top: 4, bottom: 4, left: 4, right: 4 });
                div.append(over);


                $(document.body).append(div);*/

                MEE.Toolbar.images[size] = -1;

                // dont scale with ie
                if ($.browser.msie)
                    return;

                var overlaydiv = $('<div>');
                overlaydiv.css('border', '1px solid orange');
                overlaydiv.css('position', 'absolute');
                overlaydiv.css('z-index', '2');
                overlaydiv.css('left', '0px');
                overlaydiv.css('top', '0px');
                overlaydiv.css('width', width + 'px');
                overlaydiv.css('height', height + 'px');
                overlaydiv.data('elem', this);
                overlaydiv.html('&nbsp;');


                $(this).css('position', 'relative');
                $(overlaydiv).mouseover(function () {
                    var elem = $(this).data('elem');
                    if ($(elem).data('showingmenu') == 1)
                        return;
                    $(elem).remove9Grid();
                    $(elem).css('background-image', 'url(mee/images/toolbar/base/' + icon + '-over.png)');
                    $(elem).scale9Grid({ top: 4, bottom: 4, left: 4, right: 4 });
                });
                $(overlaydiv).mouseout(function () {
                    var elem = $(this).data('elem');
                    if ($(elem).data('showingmenu') == 1)
                        return;
                    $(elem).remove9Grid();
                });
                $(overlaydiv).mousedown(function () {
                    var elem = $(this).data('elem');
                    if ($(elem).data('showingmenu') == 1)
                        return;
                    $(elem).remove9Grid();
                    $(elem).css('background-image', 'url(mee/images/toolbar/base/' + icon + '-click.png)');
                    $(elem).scale9Grid({ top: 4, bottom: 4, left: 4, right: 4 });
                });
                $(overlaydiv).mouseup(function () {
                    var elem = $(this).data('elem');
                    if ($(elem).data('showingmenu') == 1)
                        return;
                    $(elem).remove9Grid();
                    $(elem).css('background-image', 'url(mee/images/toolbar/base/' + icon + '-over.png)');
                    $(elem).scale9Grid({ top: 4, bottom: 4, left: 4, right: 4 });
                });
                $(overlaydiv).click(function () {
                    //var elem = $(this).data('elem');
                    //$(elem).trigger('click');
                });
                $(this).prepend(overlaydiv);
            }
        });
    }
    //#endregion
});
