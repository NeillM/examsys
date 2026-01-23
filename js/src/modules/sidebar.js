// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.
//
// Sidebar
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//
define(['jsxls', 'rogoconfig', 'jquery', 'jqueryui'], function(jsxls, config, $) {
    return function() {
        /**
         * Initialise sidebar.
         */
        this.init = function() {
            this.scrollLine = 0;
            this.myUpInterval = 0;
            this.myDownInterval = 0;
            this.lastFocusedTrigger = null;
            var scope = this;

            // Add keyboard event listener for menu item navigation
            $(document).on('keydown', '.menuitem, .menuitem *', function(e) {
                if (!$('.sidebar:visible').length) return;
                // Prevent duplicate handlers from processing the same keypress
                e.stopImmediatePropagation();

                var currentFocus = $(':focus');
                var menuItem = currentFocus.closest('.menuitem');
                if (!menuItem.length) return;
                if (menuItem.attr('aria-disabled') === 'true') return;

                // Get sidebar items once for reuse in multiple cases
                var sidebarItems = $('.sidebar .menuitem').filter(function() {
                    return !$(this).attr('aria-disabled');
                });
                var currentSidebarIndex = sidebarItems.index(menuItem);

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (currentSidebarIndex >= 0 && currentSidebarIndex < sidebarItems.length - 1) {
                            sidebarItems.eq(currentSidebarIndex + 1).find('button, a').first().focus();
                        }
                        break;

                    case 'ArrowUp':
                        e.preventDefault();
                        if (currentSidebarIndex > 0) {
                            sidebarItems.eq(currentSidebarIndex - 1).find('button, a').first().focus();
                        }
                        break;

                    case 'Enter':
                        e.preventDefault();
                        scope.handleMenuItemAction(menuItem, e);
                        break;

                    case 'Escape':
                        e.preventDefault();
                        scope.hideMenus();
                        break;

                    case 'ArrowLeft':
                        e.preventDefault();
                        if ($('.popup:visible').length) {
                            scope.hideMenus();
                        }
                        break;

                    case 'ArrowRight':
                        e.preventDefault();
                        if (menuItem.attr('data-action') === 'openSubMenu') {
                            scope.handleMenuItemAction(menuItem, e);
                        }
                        break;
                }
            });

            // Add keyboard event listener specifically for popup menu navigation
            $(document).on('keydown', '.popup, .popup *', function(e) {
                if (!$('.sidebar:visible').length) return;
                // Prevent duplicate handlers from processing the same keypress
                e.stopImmediatePropagation();

                var currentFocus = $(':focus');
                var visibleMenu = $('.popup:visible').first();
                if (!visibleMenu.length) return;
                if (!currentFocus.closest('.popup').length) return;

                var menuItems = visibleMenu.find('.popupitem');
                var actionableItems = menuItems.filter(':visible').not('.scrollup, .scrolldown').filter(function() {
                    return $(this).find('a').length > 0 || $(this).attr('onclick');
                });
                if (!actionableItems.length) {
                    actionableItems = menuItems;
                }
                var popupItem = currentFocus.closest('.popupitem');
                var activeItem = actionableItems.filter('.popupitem-focus');
                if (!activeItem.length && currentFocus.is('a')) {
                    activeItem = currentFocus.closest('.popupitem');
                }
                var currentIndex = activeItem.length ? actionableItems.index(activeItem) :
                    (actionableItems.length > 0 ? actionableItems.index(popupItem) : -1);
                var nextIndex;
                var prevIndex;

                var findNextIndex = function(startIndex, direction) {
                    var count = actionableItems.length;
                    if (!count) return -1;
                    var idx = startIndex;
                    if (idx < 0) {
                        return direction > 0 ? 0 : count - 1;
                    }
                    return (idx + direction + count) % count;
                };

                var keyName = e.key || ({37: 'ArrowLeft', 38: 'ArrowUp', 39: 'ArrowRight', 40: 'ArrowDown', 13: 'Enter', 27: 'Escape', 9: 'Tab'}[(e.which || e.keyCode)] || '');
                if (e.repeat) {
                    return;
                }

                var focusPopupItem = function(item) {
                    actionableItems.removeClass('popupitem-focus');
                    if (!item || !item.length) {
                        return;
                    }
                    visibleMenu.addClass('popup-keyboard');
                    item.addClass('popupitem-focus');
                    var link = item.find('a').first();
                    if (link.length) {
                        link.focus();
                    } else {
                        item.focus();
                    }
                    var newIndex = actionableItems.index(item);
                    if (newIndex >= 0) {
                        visibleMenu.data('activeIndex', newIndex);
                    }
                };

                if (keyName === 'Tab') {
                    e.preventDefault();
                    if (e.shiftKey) {
                        // Shift+Tab - go backwards
                        prevIndex = findNextIndex(currentIndex, -1);
                        focusPopupItem(actionableItems.eq(prevIndex));
                    } else {
                        // Tab - go forwards
                        nextIndex = findNextIndex(currentIndex, 1);
                        focusPopupItem(actionableItems.eq(nextIndex));
                    }
                    return;
                }

                switch (keyName) {
                    case 'ArrowDown':
                        e.preventDefault();
                        nextIndex = findNextIndex(currentIndex, 1);
                        focusPopupItem(actionableItems.eq(nextIndex));
                        break;

                    case 'ArrowUp':
                        e.preventDefault();
                        prevIndex = findNextIndex(currentIndex, -1);
                        focusPopupItem(actionableItems.eq(prevIndex));
                        break;

                    case 'Enter':
                        e.preventDefault();
                        if (popupItem.length) {
                            scope.handleMenuItemAction(popupItem, e);
                        }
                        break;

                    case 'Escape':
                        e.preventDefault();
                        scope.hideMenus();
                        break;

                    case 'ArrowLeft':
                        e.preventDefault();
                        scope.hideMenus();
                        break;

                    case 'ArrowRight':
                        e.preventDefault();
                        if (popupItem.length && popupItem.attr('data-action') === 'openSubMenu') {
                            scope.handleMenuItemAction(popupItem, e);
                        } else if (popupItem.length) {
                            focusPopupItem(popupItem);
                        }
                        break;
                }
            });
            
            // click handler for menu items
            $(document).on('click', '.menuitem', function(e) {
                scope.handleMenuItemAction($(this), e);
            });
            
            $('.scrollup').mouseover(function() {
                var id = $(this).attr('data-menuno');
                var options = JSON.parse($("#popupmenu" + id).attr('data-myOptions'));
                var urls = JSON.parse($("#popupmenu" + id).attr('data-myURLs'));
                scope.scrollUpStart('popup' + id, options, urls);
            });

            $('.scrollup').mouseout(function() {
                scope.scrollUpEnd();
            });

            $('.scrolldown').mouseover(function() {
                var id = $(this).attr('data-menuno');
                var options = JSON.parse($("#popupmenu" + id).attr('data-myOptions'));
                var urls = JSON.parse($("#popupmenu" + id).attr('data-myURLs'));
                scope.scrollDownStart('popup' + id, options, urls);
            });

            $('.scrolldown').mouseout(function() {
                scope.scrollDownEnd();
            });

            $('.popup').mouseleave(function(e) {
                // FF/IE trigger mouseleave incorrectly on dropdown use so ignore.
                if (e.target.tagName !== 'SELECT') {
                    scope.hideMenus();
                }
            });
        };

        /**
         * Scroll up the menu
         * menus have a hardcoded display number of 20
         * @param integer submenuID submenu id
         * @param array arrayID array of submenu items
         * @param array urlID array of submenu urls
         */
        this.scrollUpStart = function (submenuID, arrayID, urlID) {
            this.myUpInterval = window.setInterval(function () {
                if (this.scrollLine > 0) {
                    this.scrollLine--;
                    var limit = (this.scrollLine + 19);
                    if (limit >= arrayID.length) {
                        limit = arrayID.length-1;
                    }
                    var line = 0;
                    for (var i = this.scrollLine; i <= limit; i++) {
                        var submenuItemID = submenuID.substr(5,1) + '_' + line;
                        if (urlID[i].substr(0,1) == '-') {
                            $('#' + submenuItemID).html('<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />');
                            $('#' + submenuItemID).attr('onclick', "window.location=''");
                        } else if (urlID[i].substr(0,1) == '#') {
                            $('#' + submenuItemID).html(urlID[i].substr(1));
                        } else {
                            $('#' + submenuItemID).html(arrayID[i]);
                            $('#' + submenuItemID).attr('onclick', "window.location='" + urlID[i] + "'");
                        }
                        line++;
                    }
                    var downID = submenuID.substr(5,1) + '_down';
                    $('#' + downID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_down_on.png" width="9" height="5" alt="'+ jsxls.lang_string["down"] + '" />&nbsp;');
                } else {
                    var upID = submenuID.substr(5,1) + '_up';
                    $('#' + upID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_up_off.png" width="9" height="5" alt="'+ jsxls.lang_string["up"] + '" />&nbsp;');
                    clearInterval(this.myDownInterval);
                }
            }.bind(this),50);
        };

        /**
         * Stop scrolling up.
         */
        this.scrollUpEnd = function() {
            clearInterval(this.myUpInterval);
        };

        /**
         * Scroll down the menu
         * menus have a hardcoded display number of 20
         * @param integer submenuID submenu id
         * @param array arrayID array of submenu items
         * @param array urlID array of submenu urls
         */
        this.scrollDownStart = function(submenuID, arrayID, urlID) {
            this.myDownInterval = window.setInterval(function () {
            if (this.scrollLine < (arrayID.length-20)) {
                if (this.scrollLine == 0) {
                    var upID = submenuID.substr(5,1) + '_up';
                    $('#' + upID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_up_on.png" width="9" height="5" alt="'+ jsxls.lang_string["up"] + '" />&nbsp;');
                }
                this.scrollLine++;
                var limit = (this.scrollLine + 19);
                if (limit >= arrayID.length) {
                    limit = arrayID.length-1;
                }
                var line = 0;
                for (var i = this.scrollLine; i <= limit; i++) {
                    var submenuItemID = submenuID.substr(5,1) + '_' + line;
                    if (urlID[i].substr(0,1) == '-') {
                        $('#' + submenuItemID).html('<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />');
                        $('#' + submenuItemID).attr('onclick', "window.location=''");
                    } else if (urlID[i].substr(0,1) == '#') {
                        $('#' + submenuItemID).html(urlID[i].substr(1));
                    } else {
                        $('#' + submenuItemID).html(arrayID[i]);
                        $('#' + submenuItemID).attr('onclick', "window.location='" + urlID[i] + "'");
                    }
                    line++;
                }
            } else {
                var downID = submenuID.substr(5,1) + '_down';
                $('#' + downID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_down_off.png" width="9" height="5" alt="'+ jsxls.lang_string["down"] + '" />&nbsp;');
                clearInterval(this.myDownInterval);
            }
            }.bind(this),50);
        };

        /**
         * Stop scrolling down.
         */
        this.scrollDownEnd = function() {
            clearInterval(this.myDownInterval);
        };

        /**
         * Display menu overlay.
         * @param integer submenuID submenu id
         * @param integer menuID menu id
         * @param integer callingID id of calling item
         * @param array arrayID array of item ids
         * @param array urlID array of item urls
         * @param object e event
         * @returns bool
         */
        this.showMenu = function(submenuID, menuID, callingID, arrayID, urlID, e) {
            var scope = this;
            this.scrollLine = 0;

            var limit = (this.scrollLine + 19);
            if (limit >= arrayID.length) {
                limit = arrayID.length-1;
            }
            if (arrayID.length > 20) {
                var upID = submenuID.substr(5,1) + '_up';
                $('#' + upID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_up_off.png" width="9" height="5" alt="'+ jsxls.lang_string["up"] + '" />&nbsp;');
                var downID = submenuID.substr(5,1) + '_down';
                $('#' + downID).html('<img src="' + config.cfgrootpath + '/artwork/submenu_down_on.png" width="9" height="5" alt="'+ jsxls.lang_string["down"] + '" />&nbsp;');
            }
            var line = 0;
            for (var i = this.scrollLine; i <= limit; i++) {
                var submenuItemID = submenuID.substr(5,1) + '_' + line;
                if (urlID[i].substr(0,1) == '-') {
                    $('#' + submenuItemID).html('<hr nonshade="nonshade" style="height:1px; border:none; background-color:#C0C0C0; color:#C0C0C0" />');
                    $('#' + submenuItemID).attr('onclick', "window.location=''");
                } else if (urlID[i].substr(0,1) == '#') {
                    $('#' + submenuItemID).html(urlID[i].substr(1));
                } else {
                    $('#' + submenuItemID).html(arrayID[i]);
                    $('#' + submenuItemID).attr('onclick', "window.location='" + urlID[i] + "'");
                }
                line++;
            }

            if (!e) e = window.event;
            if ($('#' + submenuID).css('display') != 'block') {
                scope.hideMenus(e);
                $('#' + submenuID).show();
                
                // Set aria-expanded to true 
                $('#' + callingID).attr('aria-expanded', 'true');
                
                // Make all menu items focusable first
                $('#' + submenuID + ' .popupitem').attr('tabindex', '0');
                
                // Focus the first menu item after showing the menu
                var firstItem = $('#' + submenuID + ' .popupitem').first();
                if (firstItem.length) {
                    // Reset any stale focus class and set it on the first item
                    $('#' + submenuID + ' .popupitem').removeClass('popupitem-focus');
                    firstItem.addClass('popupitem-focus');
                    $('#' + submenuID).data('activeIndex', 0);
                    var firstLink = firstItem.find('a').first();
                    if (firstLink.length) {
                        firstLink.focus();
                    } else {
                        firstItem.focus();
                    }
                }
            } else {
                scope.hideMenus(e);
            }
            var popupHeight = $('#' + submenuID).height();

            var sidebarHeight = $('#left-sidebar').height();

            var mytop = $('#' + callingID).offset().top - $(document).scrollTop();
            if ((mytop + popupHeight) > sidebarHeight) {
                mytop = sidebarHeight - popupHeight - 6;
            }
            $('#' + submenuID).css('top', mytop + 'px');

            e.cancelBubble = true;
            
            return false;
        };

        /**
         * Hide menu overlay.
         */
        this.hideMenus = function() {
            $(".popup").each(function() {
                $(this).hide();
            });
            $(".popup").removeClass('popup-keyboard');
            // Set aria-expanded to false for menu item
            $('[aria-expanded="true"]').attr('aria-expanded', 'false');
            // Restore focus to the triggering menuitem by finding its interactive element (button or a)
            if (this.lastFocusedTrigger && this.lastFocusedTrigger.length) {
                var focusTarget = this.lastFocusedTrigger.find('button, a').first();
                if (focusTarget.length) {
                    focusTarget.focus();
                } else {
                    this.lastFocusedTrigger.focus();
                }
            }
        };

        this.handleMenuItemAction = function(menuItem,e) {
            var action = menuItem.attr('data-action');
            if (action) {
                switch (action) {
                    case 'openSubMenu':
                        var options = JSON.parse($("#popupmenu" + menuItem.attr('data-popupid')).attr('data-myOptions'));
                        var urls = JSON.parse($("#popupmenu" + menuItem.attr('data-popupid')).attr('data-myURLs'));
                        var id = 'popup' + menuItem.attr('data-popupid');
                        var type = menuItem.attr('data-popuptype');
                        var name = menuItem.attr('data-popupname');
                        this.lastFocusedTrigger = menuItem.closest('.menuitem');  // Store the parent menuitem
                        this.showMenu(id, type, name, options, urls, e);
                        break;

                    case 'directUrl':
                        var href = menuItem.find('a').attr('href');
                        if (href) {
                            window.location = href;
                        }
                        break;

                    case 'openPopup':
                        var settings = menuItem.data();
                        if (settings.popuptype === 'window') {
                            var popup = window.open(settings.url, settings.name, settings.features);
                            if (settings.focus && window.focus) {
                                popup.focus();
                            }
                        }
                        break;
                   
                    default:
                        console.warn('Unknown action type:', action);
                        break;
                }
                return;
            }
            
            // Fallback for popup items without data-action: handle onclick
            if (menuItem.hasClass('popupitem')) {
                var onclick = menuItem.attr('onclick');
                if (onclick) {
                    var urlMatch = onclick.match(/window\.location\s*=\s*['"]([^'"]+)['"]/);
                    if (urlMatch && urlMatch[1]) {
                        window.location = urlMatch[1];
                    } else if (onclick.indexOf('JavaScript:') === -1) {
                        try {
                            eval(onclick);
                        } catch (err) {
                            console.warn('Error executing onclick:', err);
                        }
                    }
                }
            }

        };
    }
});
