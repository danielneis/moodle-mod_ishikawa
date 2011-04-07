<?php

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_course_login($course);
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
    require_capability('mod/ishikawa:view', $context);

    add_to_log($course->id, "ishikawa", "view all", "index.php?id=$course->id", "");

    $strishikawa = get_string('modulename', 'ishikawa');
    $strishikawas = get_string('modulenameplural', 'ishikawa');

    $navlinks = array();
    $navlinks[] = array('name' => $strishikawas, 'link' => '', 'type' => 'activity');
    $navigation = build_navigation($navlinks);
    print_header_simple($strishikawas, "", $navigation, "", "", true, '',navmenu($course));

    $ishikawas = get_records('ishikawa', 'course', $course->id);

    echo '<table class="generaltable">',
         '<tr>',
           '<th>Diagrama</th>',
           '<th>Descrição</th>',
           '<th>Data de entrega</th>',
           '<th>Enviada</th>',
           '<th>Nota</th>',
         '</tr>';


    $ishimod = get_record('modules', 'name', 'ishikawa');
    foreach ($ishikawas as $ishi) {
        if(!$sub = ishikawa_get_submission($USER->id, $ishi->id)) {
            $sub->timemodified = 0;
        }

        $cm = get_record('course_modules', 'module', $ishimod->id, 'instance', $ishi->id);
        echo '<tr>',
              '<td><a href="', $CFG->wwwroot, '/mod/ishikawa/view.php?id=',$cm->id, '">', $ishi->name,'</td>',
              '<td>', $ishi->description,'</td>',
              '<td>',userdate($ishi->timedue),'</td>',
              '<td>',userdate($sub->timemodified),'</td>',
              '<td>',10,'</td>',
             '</tr>';
    }
    echo '</table>';

    print_footer($course);
?>
