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
// Initialise hotspot edit page.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
requirejs(['qhotspot', 'jqueryhotspot'], function (qhotspot, HOTSPOT) {
    var hotspot = new HOTSPOT();
    hotspot.init();

    var language = $('#dataset').attr('data-language');
    $("canvas[id^=canvas]").each(function() {
        var type = "edit";
        if ($(this).attr('class') == 'hotspotcorrection') {
            type = "correction";
        }
        var hotspot = new qhotspot();
        hotspot.setUpHotspot($(this).attr('data-qno'),
            "flash" + $(this).attr('data-qno'),
            language, $(this).attr('data-qmedia'),
            $(this).attr('data-qcorrect'), $(this).attr('data-user'), $(this).attr('data-marking'), "#FFC0C0", type);

    });
});