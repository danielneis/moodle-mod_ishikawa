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
require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);  // Course Module ID

if (! $cm = get_coursemodule_from_id('ishikawa', $id)) {
    print_error("Course Module ID was incorrect");
}

if (! $ishikawa = $DB->get_record("ishikawa", array("id" => $cm->instance))){ 
    print_error("ishikawa ID was incorrect");
}

if (! $course = $DB->get_record("course", array("id" => $ishikawa->course))) {
    print_error("Course is misconfigured");
}

require_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/ishikawa:submit', $context);

if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    notice(get_string("activityiscurrentlyhidden"));
}

/**                   $navigation = build_navigation('', $cm);
 Old functions        $meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/ishikawa/styles.css" />';
**///                 print_header_simple($ishikawa->name, "", $navigation, "", $meta, true, '',navmenu($course, $cm));
$strishikawa = get_string('modulename', 'ishikawa');
$PAGE->set_url('/mod/ishikawa/edit.php', array('id' => $course->id));
$PAGE->navbar->add($strishikawa);
$PAGE->set_title($ishikawa->name);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();		

$submission = ishikawa_get_submission($USER->id, $ishikawa->id);
ishikawa_edit_blocks($cm->id, ishikawa_blocks_from_submission($submission, $ishikawa), $submission);

echo $OUTPUT->footer();

?>
