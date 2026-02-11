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
define(['jsxls', 'rogoconfig', 'jquery', 'log', 'jqueryui'], function(jsxls, config, $, log) {
    return function() {
        /**
         * Initialise sidebar.
         */
        this.init = function() {
            this.lastFocusedTrigger = null;
            var scope = this;

            // Add keyboard event listener for menu item navigation
            $(document).on('keydown', '.menuitem, .menuitem *', function(e) {
                if (!$('.sidebar:visible').length) return;

                var currentFocus = $(':focus');
                var menuItem = currentFocus.closest('.menuitem');
                if (!menuItem.length) return;
                if (menuItem.attr('aria-disabled') === 'true') return;

                // Get sidebar items once for reuse in multiple cases
                var sidebarItems = $('.sidebar .menuitem').filter(function() {
                    return !$(this).attr('aria-disabled');
                });
                var currentSidebarIndex = sidebarItems.index(menuItem);
                var handled = false;

                switch (e.key) {
                    case 'ArrowDown':
                        handled = true;
                        e.preventDefault();
                        if (currentSidebarIndex >= 0 && currentSidebarIndex < sidebarItems.length - 1) {
                            sidebarItems.eq(currentSidebarIndex + 1).find('button, a').first().focus();
                        }
                        break;

                    case 'ArrowUp':
                        handled = true;
                        e.preventDefault();
                        if (currentSidebarIndex > 0) {
                            sidebarItems.eq(currentSidebarIndex - 1).find('button, a').first().focus();
                        }
                        break;

                    case 'Enter':
                        handled = true;
                        e.preventDefault();
                        scope.handleMenuItemAction(menuItem, e);
                        break;

                    case 'Escape':
                        handled = true;
                        e.preventDefault();
                        scope.hideMenus();
                        break;

                    case 'ArrowLeft':
                        handled = true;
                        e.preventDefault();
                        if ($('.popup:visible').length) {
                            scope.hideMenus();
                        }
                        break;

                    case 'ArrowRight':
                        handled = true;
                        e.preventDefault();
                        if (menuItem.attr('data-action') === 'openSubMenu') {
                            scope.handleMenuItemAction(menuItem, e);
                        }
                        break;
                }
                if (handled) {
                    e.stopImmediatePropagation();
                }
            });

            // Add keyboard event listener specifically for popup menu navigation
            $(document).on('keydown', '.popup, .popup *', function(e) {
                if (!$('.sidebar:visible').length) return;

                var currentFocus = $(':focus');
                var visibleMenu = $('.popup:visible').first();
                if (!visibleMenu.length) return;
                if (!currentFocus.closest('.popup').length) return;

                var getActionableItems = function() {
                    var menuItems = visibleMenu.find('.popupitem');
                    var actionableItems = menuItems.filter(':visible').filter(function() {
                        return $(this).find('a').length > 0 || $(this).attr('data-onclick') || $(this).attr('onclick');
                    });
                    // If nothing actionable is visible (e.g., separators/headings),
                    // fall back to all items to keep keyboard navigation working.
                    if (!actionableItems.length) {
                        actionableItems = menuItems;
                    }
                    return actionableItems;
                };
                var actionableItems = getActionableItems();
                var popupItem = currentFocus.closest('.popupitem');
                var currentIndex = actionableItems.length > 0 ? actionableItems.index(popupItem) : -1;
                var nextIndex;
                var prevIndex;
                var FORWARD = 1;
                var BACKWARD = -1;

                /**
                 * Get the next actionable index, wrapping within bounds.
                 *
                 * @param {number} startIndex Current index.
                 * @param {number} direction 1 for forward, -1 for backward.
                 * @returns {number} The next index to focus.
                 */
                var findNextIndex = function(startIndex, direction) {
                    var count = actionableItems.length;
                    if (!count) return -1;
                    if (startIndex === -1) {
                        return direction === BACKWARD ? count - 1 : 0;
                    }
                    // Add the count first to avoid negative wrapping when moving backward.
                    return (startIndex + direction + count) % count;
                };

                var keyName = e.key;
                if (e.repeat) {
                    return;
                }

                if (!actionableItems.length) {
                    return;
                }

                var handled = false;

                // If nothing is focused, don't change focus for Tab/Up/Down.
                if (currentIndex === -1 && (keyName === 'Tab' || keyName === 'ArrowDown' || keyName === 'ArrowUp')) {
                    return;
                }

                /**
                 * Focus a popup menu item and update active index.
                 *
                 * @param {jQuery} item The menu item to focus.
                 * @returns {void}
                 */
                var focusPopupItem = function(item) {
                    if (!item || !item.length) {
                        return;
                    }
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
                    handled = true;
                    e.preventDefault();
                    if (e.shiftKey) {
                        // Shift+Tab - go backwards
                        prevIndex = findNextIndex(currentIndex, BACKWARD);
                        focusPopupItem(actionableItems.eq(prevIndex));
                    } else {
                        // Tab - go forwards
                        nextIndex = findNextIndex(currentIndex, FORWARD);
                        focusPopupItem(actionableItems.eq(nextIndex));
                    }
                    if (handled) {
                        e.stopImmediatePropagation();
                    }
                    return;
                }

                switch (keyName) {
                    case 'ArrowDown':
                        handled = true;
                        e.preventDefault();
                        nextIndex = findNextIndex(currentIndex, FORWARD);
                        focusPopupItem(actionableItems.eq(nextIndex));
                        break;

                    case 'ArrowUp':
                        handled = true;
                        e.preventDefault();
                        prevIndex = findNextIndex(currentIndex, BACKWARD);
                        focusPopupItem(actionableItems.eq(prevIndex));
                        break;

                    case 'Enter':
                        handled = true;
                        e.preventDefault();
                        if (popupItem.length) {
                            scope.handleMenuItemAction(popupItem, e);
                        }
                        break;

                    case 'Escape':
                        handled = true;
                        e.preventDefault();
                        scope.hideMenus();
                        break;

                    case 'ArrowLeft':
                        handled = true;
                        e.preventDefault();
                        scope.hideMenus();
                        break;

                    case 'ArrowRight':
                        handled = true;
                        e.preventDefault();
                        if (popupItem.length && popupItem.attr('data-action') === 'openSubMenu') {
                            scope.handleMenuItemAction(popupItem, e);
                        } else if (popupItem.length) {
                            focusPopupItem(popupItem);
                        }
                        break;
                }
                if (handled) {
                    e.stopImmediatePropagation();
                }
            });
            
            // click handler for menu items
            $(document).on('click', '.menuitem', function(e) {
                scope.handleMenuItemAction($(this), e);
            });
            
            $('.popup').mouseleave(function(e) {
                // FF/IE trigger mouseleave incorrectly on dropdown use so ignore.
                if (e.target.tagName !== 'SELECT') {
                    scope.hideMenus();
                }
            });
        };

        /**
         * Display menu overlay.
         * @param integer submenuID submenu id
         * @param integer callingID id of calling item
         * @param object e event
         * @returns bool
         */
        this.showMenu = function(submenuID, callingID, e) {
            var scope = this;

            if (!e) e = window.event;
            if ($('#' + submenuID).css('display') != 'block') {
                scope.hideMenus(e);
                $('#' + submenuID).show();
                
                // Set aria-expanded to true 
                $('#' + callingID).attr('aria-expanded', 'true');
                
                // Focus the first menu item after showing the menu
                var firstItem = $('#' + submenuID + ' .popupitem').first();
                if (firstItem.length) {
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
                        var id = 'popup' + menuItem.attr('data-popupid');
                        var name = menuItem.attr('data-popupname');
                        this.lastFocusedTrigger = menuItem.closest('.menuitem');  // Store the parent menuitem
                        this.showMenu(id, name, e);
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
                        log('Unknown action type: ' + action, 'warn');
                        break;
                }
                return;
            }
            
            // Fallback for popup items without data-action: handle link/data-onclick
            if (menuItem.hasClass('popupitem')) {
                var link = menuItem.find('a').first();
                var linkHref = link.attr('href');
                if (linkHref && linkHref !== '#') {
                    window.location = linkHref;
                    return;
                }

                var onclick = menuItem.attr('data-onclick') || link.attr('data-onclick') || menuItem.attr('onclick');
                if (onclick) {
                    var urlMatch = onclick.match(/window\.location\s*=\s*['"]([^'"]+)['"]/);
                    if (urlMatch && urlMatch[1]) {
                        window.location = urlMatch[1];
                        return;
                    }

                    var javascriptPrefix = 'JavaScript:';
                    var script = onclick;
                    if (script.indexOf(javascriptPrefix) === 0) {
                        script = script.slice(javascriptPrefix.length);
                    }
                    try {
                        eval(script);
                    } catch (err) {
                        log('Error executing onclick: ' + err, 'warn');
                    }
                }
            }

        };
    }
});
