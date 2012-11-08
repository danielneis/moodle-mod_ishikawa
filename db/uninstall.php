<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// this file contains all the functions that aren't needed by core moodle
// but start becoming required once we're actually inside the ishikawa module.
/**
 * @package     mod
 * @subpackage  ishikawa
 **/

defined('MOODLE_INTERNAL') || die();

function mod_ishikawa_uninstall() {
     global $DB;
     //$dbman = $DB->get_manager();
     $param = "SELECT modules.id FROM modules WHERE modules.name = 'ishikawa' ";	
     $DB->delete_records('course_modules', 'module', $param);	
					$DB->delete_records('grade_items', 'itemmodule', 'ishikawa');
					$DB->delete_records('grade_items_history', 'itemmodule', 'ishikawa');
     
					return true;
}


