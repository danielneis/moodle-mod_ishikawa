<?php

    require_once('../../config.php');
    require_once('lib.php');

$id = required_param('cmid', PARAM_INT);
$subid = optional_param('subid', 0, PARAM_INT);

    if (! $cm = get_coursemodule_from_id('ishikawa', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $ishikawa = get_record("ishikawa", "id", $cm->instance)) {
        error("ishikawa ID was incorrect");
    }

    if (! $course = get_record("course", "id", $ishikawa->course)) {
        error("Course is misconfigured");
    }

    require_login($course, true, $cm);

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/ishikawa:submit', $context);

if (!$subid) {
    // create submission
    if ($data = data_submitted()) {
        var_dump($data);

        $submission = new stdclass();
        $submission->ishikawaid = $ishikawa->id;
        $submission->userid = $USER->id;
        $submission->timecreated = time();
        $submission->timemodified = $submission->timecreated;
        if (!$submissionid = insert_record('ishikawa_submissions', $submission)) {
            print_error('cant_insert_submit_record');
        }

        foreach ($data->block as $nivel_y => $blocks) {
            foreach ($blocks as $nivel_x => $text) {
                $b = new stdclass();
                $b->submissionid = $submissionid;
                $b->nivel_x = $nivel_x;
                $b->nivel_y = $nivel_y;
                $b->texto = $text;
                if (!insert_record('ishikawa_blocks', $b)) {
                    print_error('cant_insert_block');
                }
            }
        }

    } else {
        print_error('no_data_submitted');
    }
} else {
   //  update_submission
}

?>
