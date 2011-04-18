<?php

    require_once('../../config.php');
    require_once('lib.php');

    $id = required_param('id', PARAM_INT);  // Course Module ID
    $src = optional_param('src', 0, PARAM_INT);
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

        if ($src_type != $dst_type) {
            print_error('src_type_differ_from_dst_type');
            // arrumar redirect para voltar para a edição de links
        }

        if (!in_array($src_type, array('causes', 'consequences', 'axis'))) {
            print_error('invalid_src_type');
        }

        $table = 'ishikawa_'.$src_type.'_blocks_connections';

        $connection = new stdClass();
        $connection->source_id = $src;
        $connection->destination_id = $dst;
        if (!insert_record($table, $connection)) {
            var_dump($table, $connection);
            print_error('cannot_add_connection');
        }
        redirect($CFG->wwwroot.'/mod/ishikawa/createconnections.php?id='.$cm->id);
    } else {

        $strishikawa = get_string('modulename', 'ishikawa');

        $navigation = build_navigation('', $cm);
        $meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/ishikawa/styles.css" />';
        print_header_simple($ishikawa->name, "", $navigation, "", $meta, true, '',navmenu($course, $cm));

        $blocks = ishikawa_blocks_from_submission($submission);

        ishikawa_edit_links($cm->id, $blocks, $submission, $src, $dst);

        $connections = array();

        echo '<img src="image.php?id=',$cm->id,'&userid=',$USER->id,'" />';

        print_footer($course);
    }

?>
