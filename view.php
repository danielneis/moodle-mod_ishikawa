<?php  // $Id$

    require_once("../../config.php");
    require_once("lib.php");

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
    require_capability('mod/ishikawa:view', $context);

    add_to_log($course->id, "ishikawa", "view", "view.php?id={$cm->id}", $ishikawa->id, $cm->id);

    /// Print the page header

    /// Some capability checks.
    if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
        notice(get_string("activityiscurrentlyhidden"));
    }

    $submission = ishikawa_get_submission($USER->id, $ishikawa->id);

    $buttontext = '';
    $strishikawa = get_string('modulename', 'ishikawa');
    $buttontext = update_module_button($cm->id, $course->id, $strishikawa);
    $navigation = build_navigation('', $cm);
    print_header_simple($ishikawa->name, "", $navigation, "", "", true, $buttontext,navmenu($course, $cm));

/// Finish the page
    print_box(format_text($ishikawa->description), 'generalbox', 'intro');

    ishikawa_view_dates($ishikawa);

    // TODO testar duedate e timeavailable
    echo "<a href='edit.php?id={$id}'>Editar diagrama</a>";

    print_footer($course);
?>
