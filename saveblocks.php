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

$id = required_param('cmid', PARAM_INT);
$subid = optional_param('subid', 0, PARAM_INT);

$tail_text = required_param('tail_text', PARAM_ALPHA);
$head_text = required_param('head_text', PARAM_ALPHA);

if (empty($tail_text) || empty($head_text)) {
    $link = $CFG->wwwroot.'/mod/ishikawa/edit.php?id='.$id;
    print_error('required_tail_head', 'ishikawa', $link);
}

if (! $cm = get_coursemodule_from_id('ishikawa', $id)) {
    error("Course Module ID was incorrect");
}

if (! $ishikawa = $DB->get_record("ishikawa", "id", $cm->instance)) {
    error("ishikawa ID was incorrect");
}

if (! $course = $DB->get_record("course", "id", $ishikawa->course)) {
    error("Course is misconfigured");
}

require_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/ishikawa:submit', $context);

if (!$subid) {
    // create submission
    if ($data = data_submitted()) {

        $submission = new stdclass();
        $submission->ishikawaid = $ishikawa->id;
        $submission->userid = $USER->id;
        $submission->tail_text = $data->tail_text;
        $submission->head_text = $data->head_text;
        $submission->timecreated = time();
        $submission->timemodified = $submission->timecreated;

        if (!$submissionid = $DB->insert_record('ishikawa_submissions', $submission)) {
            print_error('cant_insert_submit_record');
        }

        foreach ($data->causes as $nivel_y => $blocks) {
            foreach ($blocks as $nivel_x => $block) {
                $b = new stdclass();
                $b->submissionid = $submissionid;
                $b->nivel_x = $nivel_x;
                $b->nivel_y = $nivel_y;
                $b->texto = $block['texto'];
                if (!$DB->insert_record('ishikawa_causes_blocks', $b)) {
                    print_error('cant_insert_cause');
                }
            }
        }

        foreach ($data->axis as $nivel_x => $block) {
            $b = new stdclass();
            $b->submissionid = $submissionid;
            $b->nivel_x = $nivel_x;
            $b->texto = $block['texto'];
            if (!$DB->insert_record('ishikawa_axis_blocks', $b)) {
                print_error('cant_insert_axis');
            }
        }

        foreach ($data->consequences as $nivel_y => $blocks) {
            foreach ($blocks as $nivel_x => $block) {
                $b = new stdclass();
                $b->submissionid = $submissionid;
                $b->nivel_x = $nivel_x;
                $b->nivel_y = $nivel_y;
                $b->texto = $block['texto'];
                if (!$DB->insert_record('ishikawa_consequences_blocks', $b)) {
                    print_error('cant_insert_consequence');
                }
            }
        }
        redirect('connections.php?id='.$cm->id);

    } else {
        print_error('no_data_submitted');
    }
} else {

    if ($data = data_submitted()) {

        $submission = new stdclass();
        $submission->id = $subid;
        $submission->timemodified = time();
        $submission->tail_text = $data->tail_text;
        $submission->head_text = $data->head_text;
        if (!$DB->update_record('ishikawa_submissions', $submission)) {
            print_error('cant_insert_submit_record');
        }

        foreach ($data->causes as $nivel_y => $blocks) {
            foreach ($blocks as $nivel_x => $block) {
                $b = new stdclass();
                $b->id = $block['id'];
                $b->texto = $block['texto'];
                if (!$DB->update_record('ishikawa_causes_blocks', $b)) {
                    print_error('cant_insert_cause');
                }
                if (empty($b->texto) && $b->texto != '0') {
                   $DB->delete_records('ishikawa_connections', 'src_id', $b->id, 'src_type', 'causes');
                   $DB->delete_records('ishikawa_connections', 'dst_id', $b->id, 'src_type', 'causes');
                }
            }
        }

        foreach ($data->axis as $nivel_x => $block) {
            $b = new stdclass();
            $b->id = $block['id'];
            $b->texto = $block['texto'];
            if (!$DB->update_record('ishikawa_axis_blocks', $b)) {
                print_error('cant_insert_axis');
            }
        }

        foreach ($data->consequences as $nivel_y => $blocks) {
            foreach ($blocks as $nivel_x => $block) {
                $b = new stdclass();
                $b->id = $block['id'];
                $b->texto = $block['texto'];
                if (!$DB->update_record('ishikawa_consequences_blocks', $b)) {
                    print_error('cant_insert_consequence');
                }
                if (empty($b->texto) && $b->texto != '0') {
                    $DB->delete_records('ishikawa_connections', 'src_id', $b->id, 'src_type', 'consequences');
                    $DB->delete_records('ishikawa_connections', 'dst_id', $b->id, 'src_type', 'consequences');
                }
            }
        }
        redirect('connections.php?id='.$cm->id);
    } else {
        print_error('no_data_submitted');
    }
}
?>
