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

    require_once("../../config.php");
    require_once("lib.php");


    $id = required_param('id', PARAM_INT);   // course

    if (! $course = $DB->get_record("course", array("id" => $id))) {
        error("Course ID is incorrect");
    }

    require_course_login($course);
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    require_capability('mod/ishikawa:view', $context);

    add_to_log($course->id, "ishikawa", "view all", "index.php?id=$course->id", "");

    $strishikawa = get_string('modulename', 'ishikawa');
    $strishikawas = get_string('modulenameplural', 'ishikawa');

    $navlinks = array();
    $navlinks[] = array('name' => $strishikawas, 'link' => '', 'type' => 'activity');
    $navigation = build_navigation($navlinks);
    print_header_simple($strishikawas, "", $navigation, "", "", true, '',navmenu($course));

    $ishikawas = $DB->get_records('ishikawa', 'course', $course->id);

    echo '<table class="generaltable">',
         '<tr>',
           '<th>Diagrama</th>',
           '<th>Descrição</th>',
           '<th>Data de entrega</th>',
           '<th>Enviada</th>',
         '</tr>';

    $ishimod = $DB->get_record('modules', 'name', 'ishikawa');
    foreach ($ishikawas as $ishi) {
        if(!$sub = ishikawa_get_submission($USER->id, $ishi->id)) {
            $sub->timemodified = 0;
        }

        $cm = $DB->get_record('course_modules', 'module', $ishimod->id, 'instance', $ishi->id);
        echo '<tr>',
              '<td><a href="', $CFG->wwwroot, '/mod/ishikawa/view.php?id=',$cm->id, '">', $ishi->name,'</td>',
              '<td>', $ishi->description,'</td>',
              '<td>',userdate($ishi->timedue),'</td>',
              '<td>',userdate($sub->timemodified),'</td>',
             '</tr>';
    }
    echo '</table>';

    echo $OUTPUT->footer();
?>
