// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// Initialise rogo js.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
var mathjax = 0;
var three = 0;
// This is before jquery is loaded.
if(document.getElementById("rogoconfig").getAttribute("data-mathjax")) {
    mathjax = 1;
}
if(document.getElementById("rogoconfig").getAttribute("data-three")) {
    three = 1;
}
var root = document.getElementById("rogoconfig").getAttribute("data-root");
var require = {
    config: {
        'requireconfig.min': {
            cfgrootpath: root,
            mathjax: mathjax,
            three: three
        }
    }
};
