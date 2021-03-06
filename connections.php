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
/**
 * @package mod_ishikawa
 *
 * @author Luis Henrique Mulinari
 * @author Daniel Neis Araujo
 * @upgrade moodle 2.0+ Caio Bressan Doneda
 **/
      
require_once('../../config.php');
require_once('lib.php');

$id       = required_param('id', PARAM_INT);  // Course Module ID
$src      = optional_param('src', 0, PARAM_INT);
$src_type = optional_param('src_type', 0, PARAM_ALPHA);
$dst      = optional_param('dst', 0, PARAM_INT);
$dst_type = optional_param('dst_type', 0, PARAM_ALPHA);

$delete_connection_id = optional_param('delete_connection', NULL, PARAM_INT);

if (! $cm = get_coursemodule_from_id('ishikawa', $id)) {
    print_error("Course Module ID was incorrect");
}

if (! $ishikawa = $DB->get_record("ishikawa", array("id" => $cm->instance))) {
    print_error("ishikawa ID was incorrect");
}

if (! $course = $DB->get_record("course", array("id" => $ishikawa->course))) {
    print_error("Course is misconfigured");
}

if (!$submission = ishikawa_get_submission($USER->id, $ishikawa->id)) {
    print_error('submission not found');
}

require_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/ishikawa:submit', $context);
/// Some capability checks.
if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    notice(get_string("activityiscurrentlyhidden"));
}

if (!is_null($delete_connection_id)) {
    $link = $CFG->wwwroot.'/mod/ishikawa/connections.php?id='.$cm->id;
    if (ishikawa_delete_connection($delete_connection_id)) {
        redirect($link);
    } else {
        print_error('cannot_delete_connection', $link);
    }
}

if ($src && $dst) {
    $src_type = required_param('src_type', PARAM_ALPHA);
    $dst_type = required_param('dst_type', PARAM_ALPHA);

    $link = $CFG->wwwroot.'/mod/ishikawa/connections.php?id='.$cm->id;

    if (!in_array($src_type, array('causes', 'consequences', 'axis'))) {
        print_error('invalid_src_type', 'ishikawa', $link);
    }
    // adcionado uma condição a mais para possibilitar a conexão entre os mesmos campos em eixos diferentes.
    if ($src == $dst) {
        if ($src_type == $dst_type){
            print_error('src_equal_dst', 'ishikawa', $link);
        }
    }

    $connection = new stdClass();
    $connection->src_id = $src;
    $connection->src_type = $src_type;
    $connection->dst_id = $dst;
    $connection->dst_type = $dst_type;
    $connection->submissionid = $submission->id;
    try{
        if (!$DB->insert_record('ishikawa_connections', $connection)) {
       }
    } catch (Exception $e){
         print_error('cannot_add_connection', 'ishikawa', $link);
    }
    redirect($link);
} else {

    $strishikawa = get_string('modulename', 'ishikawa');

    $PAGE->set_url('/mod/ishikawa/connections.php', array('id' => $course->id));
    $PAGE->set_title($strishikawa);
    $PAGE->set_heading($course->fullname);
    $PAGE->navbar->add($strishikawa);
    echo $OUTPUT->header();

    $blocks = ishikawa_blocks_from_submission($submission);
    $connections = ishikawa_connections_from_submission($submission);

    ishikawa_edit_connections($cm->id, $blocks, $connections, $submission, $src, $src_type, $dst);
    echo $OUTPUT->footer();

}
