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

// Helper functions that lets us check for running JavaScript in a page.
//
// @author Neill Magill <neill.magill@nottingham.ac.uk>
// @copyright Copyright (c) 2026 The University of Nottingham

define([], function() {
    /**
     * Stores all JavaScript that is flagged as running.
     *
     * @type {Map<{String}, {String}>}
     * @private
     */
    let runningJS = new Map();

    /**
     * Flags the JavaScript as no longer running.
     *
     * @param {String} name
     */
    function done(name) {
        if (!runningJS.has(name)) {
            throw new Error(name + ' is not running');
        }

        runningJS.delete(name);
    }

    /**
     * Tests if any JavaScript is currently flagged as running.
     *
     * @returns {boolean}
     */
    function isJSRunning() {
        return runningJS.size > 0;
    }

    /**
     * Gets a comma separated list of all the JS that is currently running.
     *
     * @return {String}
     */
    function listRunningJS() {
        let keysArray = [];

        runningJS.forEach((value) => {
            keysArray.push(value);
        });

        return keysArray.join(', ');
    }

    /**
     * Flag some JavaScript as  running.
     *
     * The name that is passed here must be unique and not already running.
     *
     * @param {String} name A unique name for the JavaScript that is running.
     */
    function start(name) {
        if (runningJS.has(name)) {
            // We cannot allow duplicates here, otherwise there will be confusion.
            throw new Error(name + ' is already running');
        }

        runningJS.set(name, name);
    }

    // We want an easy way to access the code in behat, so we will make it available
    // globally whenever it is used in a page.
    ROGO.pendingJS = ROGO.pendingJS || {
        done: done,
        isJSRunning: isJSRunning,
        listRunningJS: listRunningJS,
        start: start,
    };

    // Give the user an object that lets them manipulate the running JavaScript.
    return ROGO.pendingJS;
});
