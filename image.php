<?php

require_once("../../config.php");
require_once("Ishikawa.class.php");
require_once("lib.php");

$id     = required_param('id', PARAM_INT);  // Course Module ID
$userid = required_param('userid', PARAM_INT); // user id to get submission

$editing  = optional_param('editing', false, PARAM_BOOL);
$src      = optional_param('src', 0, PARAM_INT);
$src_type = optional_param('src_type', 0, PARAM_ALPHA);

$download = optional_param('download', false, PARAM_BOOL);

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

$submission = ishikawa_get_submission($userid, $ishikawa->id);

$blocks = ishikawa_blocks_from_submission($submission);

$connections = ishikawa_connections_from_submission($submission);

$footer = fullname(get_record('user', 'id', $userid));

$sql = "SELECT g.id, g.name
          FROM {$CFG->prefix}groups g
          JOIN {$CFG->prefix}groups_members gm
            ON gm.groupid = g.id
         WHERE gm.userid = {$userid}
           AND g.courseid = {$course->id}";
if (!$groups = get_records_sql($sql)) {
    $groups = array();
} else {
    $footer .= ' - ';
}

// aí conhece! ou não ...
$footer .= implode(array_map(create_function('$a', 'return $a->name;'), $groups), ', ');

$ishikawa = new Ishikawa($blocks, $connections, $src, $src_type, $ishikawa->name, $footer);
$ishikawa->draw($editing, $download);
?>
