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
            
            // Add keyboard event listener for menu navigation
            $(document).on('keydown', function(e) {
                if (!$('.sidebar:visible').length) return;          
                           
                // Check if a popup is open
                var visibleMenu = $('.popup:visible').first();
                var hasVisiblePopup = visibleMenu.length > 0;
                var menuItems = hasVisiblePopup ? visibleMenu.find('.popupitem') : $();
                var currentFocus = $(':focus');
                var isFocusInPopup = hasVisiblePopup && currentFocus.closest('.popup').length > 0;
                var currentIndex = menuItems.length > 0 ? menuItems.index(currentFocus) : -1;
                // Get sidebar items once for reuse in multiple cases
                var sidebarItems = $('.sidebar .menuitem').filter(function() {
                    return $(this).find('button, a').length > 0 && !$(this).attr('aria-disabled');
                });

                // Only handle if focus is within sidebar or sidebar popup
                var isInSidebar = currentFocus.closest('.sidebar').length > 0 || 
                                  currentFocus.closest('.popup').length > 0;
                if (!isInSidebar) return;
                
                if (e.key === 'Tab') {
                    if (hasVisiblePopup){
                        e.preventDefault();
                        // Constrain tabbing to be within the popupmenu once its open
                        if (e.shiftKey) {
                            // Shift+Tab - go backwards
                            var prevIndex = currentIndex <= 0 ? menuItems.length - 1 : currentIndex - 1;
                            menuItems.eq(prevIndex).focus(); 
                        } else {
                            // Tab - go forwards
                            var nextIndex = currentIndex >= menuItems.length - 1 ? 0 : currentIndex + 1;
                            menuItems.eq(nextIndex).focus();
                        }
                    }
                } else {
                    var menuItem = currentFocus.closest('.menuitem');
                    var popupItem = currentFocus.closest('.popupitem');
                    var currentSidebarIndex;

                    switch (e.key) {
                        case 'ArrowDown':
                            e.preventDefault();
                            if (hasVisiblePopup && isFocusInPopup) {
                                // Navigating within popup
                                nextIndex = currentIndex < 0 ? 0 : 
                                          currentIndex >= menuItems.length - 1 ? menuItems.length - 1 : currentIndex + 1;
                                menuItems.eq(nextIndex).focus();
                            } else {
                                // Navigate in sidebar menu items
                                currentSidebarIndex = sidebarItems.index(menuItem);
                                if (hasVisiblePopup && !isFocusInPopup) {
                                    // Popup is open but focus is on parent - navigate to next sidebar item
                                    // This allows navigation past the parent while submenu remains open
                                    if (currentSidebarIndex >= 0 && currentSidebarIndex < sidebarItems.length - 1) {
                                        sidebarItems.eq(currentSidebarIndex + 1).find('button, a').first().focus();
                                    }
                                } else {
                                    // Navigate down in sidebar menu items
                                    if (currentSidebarIndex >= 0 && currentSidebarIndex < sidebarItems.length - 1) {
                                        sidebarItems.eq(currentSidebarIndex + 1).find('button, a').first().focus();
                                    }
                                }
                            }
                            break;
                            
                        case 'ArrowUp':
                            e.preventDefault();
                            if (hasVisiblePopup && isFocusInPopup) {
                                // Navigating within popup
                                prevIndex = currentIndex <= 0 ? 0 : currentIndex - 1;
                                menuItems.eq(prevIndex).focus();
                            } else {
                                // Navigate in sidebar menu items
                                currentSidebarIndex = sidebarItems.index(menuItem);
                                if (hasVisiblePopup && !isFocusInPopup) {
                                    // Popup is open but focus is on parent - navigate to previous sidebar item
                                    // This allows navigation above the parent while submenu remains open
                                    if (currentSidebarIndex > 0) {
                                        sidebarItems.eq(currentSidebarIndex - 1).find('button, a').first().focus();
                                    }
                                } else {
                                    // Navigate up in sidebar menu items
                                    if (currentSidebarIndex > 0) {
                                        sidebarItems.eq(currentSidebarIndex - 1).find('button, a').first().focus();
                                    }
                                }
                            }
                            break;
                            
                        case 'Enter':
                            e.preventDefault();
                            if (popupItem.length) {
                                // User pressed Enter on a popup item - handle via action type
                                scope.handleMenuItemAction(popupItem, e);
                            } else if (menuItem.length) {
                                scope.handleMenuItemAction(menuItem, e);
                            }
                            break;
                            
                        case 'Escape':
                            e.preventDefault();
                            scope.hideMenus();
                            break;
                            
                        case 'ArrowLeft':
                            e.preventDefault();
                            if (hasVisiblePopup) {
                                scope.hideMenus();
                            }
                            break;
                        
                        case 'ArrowRight':
                            e.preventDefault();
                            if (hasVisiblePopup && isFocusInPopup) {
                                // Per ARIA treeview: Right arrow on open node moves focus to first child node
                                menuItems.first().focus();
                            } else if (menuItem.length && menuItem.attr('data-action') === 'openSubMenu') {
                                // Open submenu - focus will be set to first item in showMenu
                                scope.handleMenuItemAction(menuItem, e);
                            }
                            break;
                    }
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
                    firstItem.focus();
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
            console.log(action);
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
                        try { eval(onclick); } catch (err) { console.warn('Error executing onclick:', err); }
                    }
                }
            }

        };
    }
});
