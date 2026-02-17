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
// Initialise faculty page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2019 The University of Nottingham
//

/**
 * Contains the code that makes tabs work.
 *
 * This code is based on the JavaScript for the WAI tab example:
 * https://www.w3.org/WAI/ARIA/apg/patterns/tabs/examples/tabs-automatic/
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright 2026 University of Nottingham
 */
class TabHandler {
    /** Selectors for various elements in tabs. */
    static SELECTORS = {
        tabList: '.component-tabs',
        tab: '[role=tab]',
    };

    /** Attributes used to store data for TabLists. */
    static ATTRIBUTES = {
        orientation: 'data-orientation',
    };

    /**
     * The constructor.
     *
     * @param {HTMLElement} tabList Should be the HTML element for a tab.
     */
    constructor(tabList) {
        this.tabList = tabList;

        this.tabs = [];

        this.firstTab = null;
        this.lastTab = null;

        // The orientation changes which keys navigate the tabs.
        this.orientation = this.tabList.getAttribute(TabHandler.ATTRIBUTES.orientation);

        this.tabs = Array.from(this.tabList.querySelectorAll(TabHandler.SELECTORS.tab));
        this.panels = [];

        for (const tab of this.tabs) {
            const panel = document.getElementById(tab.getAttribute('aria-controls'));

            tab.tabIndex = -1;
            tab.setAttribute('aria-selected', 'false');
            this.panels.push(panel);

            tab.addEventListener('keydown', this.onKeydown.bind(this));
            tab.addEventListener('click', this.onClick.bind(this));

            if (!this.firstTab) {
                this.firstTab = tab;
            }
            this.lastTab = tab;
        }

        this.setSelectedTab(this.firstTab, false);
    }

    /**
     * Sets if a tab is focused.
     *
     * @param {HTMLElement} currentTab
     * @param {bool} setFocus
     */
    setSelectedTab(currentTab, setFocus) {
        if (typeof setFocus !== 'boolean') {
            setFocus = true;
        }

        for (const index in this.tabs) {
            const tab = this.tabs[index];
            if (currentTab === tab) {
                tab.setAttribute('aria-selected', 'true');
                tab.removeAttribute('tabindex');
                this.panels[index].classList.remove('is-hidden');
                if (setFocus) {
                    tab.focus();
                }
            } else {
                tab.setAttribute('aria-selected', 'false');
                tab.tabIndex = -1;
                this.panels[index].classList.add('is-hidden');
            }
        }
    }

    /**
     * Moves one tab backwards in the list.
     *
     * @param {HTMLElement} currentTab
     */
    setSelectedToPreviousTab(currentTab) {
        let index;

        if (currentTab === this.firstTab) {
            this.setSelectedTab(this.lastTab);
        } else {
            index = this.tabs.indexOf(currentTab);
            this.setSelectedTab(this.tabs[index - 1]);
        }
    }

    /**
     * Moves one tab forward in the list.
     *
     * @param {HTMLElement} currentTab
     */
    setSelectedToNextTab(currentTab) {
        let index;

        if (currentTab === this.lastTab) {
            this.setSelectedTab(this.firstTab);
        } else {
            index = this.tabs.indexOf(currentTab);
            this.setSelectedTab(this.tabs[index + 1]);
        }
    }

    /**
     * The keyboard handler for TabLists.
     *
     * @param {KeyboardEvent} event
     */
    onKeydown(event) {
        let target = event.currentTarget,
            flag = false;

        switch (event.key) {
            case 'ArrowLeft':
                this.setSelectedToPreviousTab(target);
                flag = true;
                break;
            case 'ArrowUp':
                if (this.orientation === 'vertical') {
                    this.setSelectedToPreviousTab(target);
                    flag = true;
                }
                break;
            case 'ArrowRight':
                this.setSelectedToNextTab(target);
                flag = true;
                break;
            case 'ArrowDown':
                if (this.orientation === 'vertical') {
                    this.setSelectedToNextTab(target);
                    flag = true;
                }
                break;
            case 'Home':
                this.setSelectedTab(this.firstTab);
                flag = true;
                break;
            case 'End':
                this.setSelectedTab(this.lastTab);
                flag = true;
                break;
        }

        if (flag) {
            event.stopPropagation();
            event.preventDefault();
        }
    }

    /**
     * Handles a tab being clicked on.
     *
     * @param {MouseEvent} event
     */
    onClick(event) {
        this.setSelectedTab(event.currentTarget, false);
    }
}

requirejs(['log'], function (Log) {
    Log('Loading Tab JS', 'info');

    // Find all the tab areas.
    let tabLists = document.querySelectorAll(TabHandler.SELECTORS.tabList);
    tabLists.forEach(function(tabList) {
        new TabHandler(tabList);
    });

    Log('Tabs loaded', 'info');
});
