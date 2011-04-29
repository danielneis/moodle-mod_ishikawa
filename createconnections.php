<?php

    require_once('../../config.php');
    require_once('lib.php');

    $id = required_param('id', PARAM_INT);  // Course Module ID
    $src = optional_param('src', 0, PARAM_INT);
    $src_type = optional_param('src_type', 0, PARAM_ALPHA);
    $dst = optional_param('dst', 0, PARAM_INT);

    if (! $cm = get_coursemodule_from_id('ishikawa', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $ishikawa = get_record("ishikawa", "id", $cm->instance)) {
        error("ishikawa ID was incorrect");
    }

    if (! $course = get_record("course", "id", $ishikawa->course)) {
        error("Course is misconfigured");
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

    if ($src && $dst) {
        $src_type = required_param('src_type', PARAM_ALPHA);
        $dst_type = required_param('dst_type', PARAM_ALPHA);

        if (!in_array($src_type, array('causes', 'consequences', 'axis'))) {
            print_error('invalid_src_type');
        }

        $link = $CFG->wwwroot.'/mod/ishikawa/createconnections.php?id='.$cm->id;

        $connection = new stdClass();
        $connection->src_id = $src;
        $connection->src_type = $src_type;
        $connection->dst_id = $dst;
        $connection->dst_type = $dst_type;
        $connection->submissionid = $submission->id;
        if (!insert_record('ishikawa_connections', $connection)) {
            print_error('cannot_add_connection', 'ishikawa', $link);
        }
        redirect($link);
    } else {

        $strishikawa = get_string('modulename', 'ishikawa');

        $navigation = build_navigation('', $cm);
        $meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/ishikawa/styles.css" />';
        print_header_simple($ishikawa->name, "", $navigation, "", $meta, true, '',navmenu($course, $cm));

        $blocks = ishikawa_blocks_from_submission($submission);
        $connections = ishikawa_connections_from_submission($submission);

        ishikawa_edit_connections($cm->id, $blocks, $connections, $submission, $src, $src_type, $dst);

        print_footer($course);
    }

?>
