<?php

    require_once('../../config.php');
    require_once('lib.php');

    $id = required_param('id', PARAM_INT);  // Course Module ID


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

    //$submission = ishikawa_get_submission();

    /// Some capability checks.
    if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
        notice(get_string("activityiscurrentlyhidden"));
    }

    $strishikawa = get_string('modulename', 'ishikawa');

    $navigation = build_navigation('', $cm);
    print_header_simple($ishikawa->name, "", $navigation, "", "", true, '',navmenu($course, $cm));

    echo "AQUI A COISA PEGA!";

    print_footer($course);

?>
