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

if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    notice(get_string("activityiscurrentlyhidden"));
}

$strishikawa = get_string('modulename', 'ishikawa');

$navigation = build_navigation('', $cm);
$meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/ishikawa/styles.css" />';
print_header_simple($ishikawa->name, "", $navigation, "", $meta, true, '',navmenu($course, $cm));

$submission = ishikawa_get_submission($USER->id, $ishikawa->id);
ishikawa_edit_blocks($cm->id, ishikawa_blocks_from_submission($submission, $ishikawa), $submission);

print_footer($course);

?>
