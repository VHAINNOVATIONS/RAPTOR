/*
 * ------------------------------------------------------------------------------------
 * Created by SAN Business Consultants
 * Designed and implemented by Frank Font (ffont@sanbusinessconsultants.com)
 * In collaboration with Andrew Casertano (acasertano@sanbusinessconsultants.com)
 * Open source enhancements to this module are welcome!  Contact SAN to share updates.
 *
 * Copyright 2014 SAN Business Consultants, a Maryland USA company (sanbusinessconsultants.com)
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.gnu.org/copyleft/gpl.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ------------------------------------------------------------------------------------
 */

/* ----------------- Events fired on the drag ----------------- */
(function($) {
	$(document).bind('dragstart', function (event) {
		if ($(event.target).is('.dragtarget')) {
                    //alert("LOOKMEHERE>>>"+event.target.id);
                    event.originalEvent.dataTransfer.setData("Text", event.target.id);
                    // Change the opacity of the draggable element
                    event.target.style.opacity = "0.4";
		}
	})
	$(document).bind('drag', function (event) {
		if ($(event.target).is('.dragtarget')) {
		}
	})
	$(document).bind('dragover', function (event) {
                event.preventDefault();
	})
	$(document).bind('dragend', function (event) {
		if ($(event.target).is('.droptarget')) {
                    event.target.style.opacity = "1";
		}
	})
	$(document).bind('dragenter', function (event) {
		if ($(event.target).is('.droptarget')) {
                    event.target.style.border = "3px dotted red";
		}
	})
	$(document).bind('dragleave', function (event) {
		if ($(event.target).is('.droptarget')) {
                    event.target.style.border = "";
		}
	})
	$(document).bind('drop', function (event) {
		if ($(event.target).is('.droptarget')) {
                    event.target.style.border = "";
                    var data = event.originalEvent.dataTransfer.getData("Text");
                    var targstr = event.target.value.trim();
                    if(targstr !== "")
                    {
                        event.target.value = targstr + ", " + data;
                    } else {
                        event.target.value = data;
                    }
		}
	})
})(jQuery);
