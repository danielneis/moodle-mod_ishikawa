<?php

require_once("../../config.php");
require_once($CFG->libdir.'/gradelib.php');
require_once("lib.php");

$id   = required_param('id', PARAM_INT);          // Course module ID
$quickgrade = optional_param('quickgrade', 0, PARAM_INT);

if (! $cm = get_coursemodule_from_id('ishikawa', $id)) {
    error("Course Module ID was incorrect");
}

if (! $ishikawa = get_record("ishikawa", "id", $cm->instance)) {
    error("ishikawa ID was incorrect");
}

if (! $course = get_record("course", "id", $ishikawa->course)) {
    error("Course is misconfigured");
}

require_login($course->id, false, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

require_capability('mod/ishikawa:grade', $context);

if ($data = data_submitted()) {

    $now = time();
    foreach ($data->student as $userid => $s) {
        if ($grade = ishikawa_get_grade($userid, $ishikawa->id)) {
            $grade->grade = $s['grade'];
            $grade->feedback = $s['feedback'];
            $grade->timecreated = $now;
            $grade->timemodified = $now;
            update_record('ishikawa_grades',$grade);
        } else {
            $grade = new stdclass();
            $grade->ishikawaid = $ishikawa->id;
            $grade->userid = $userid;
            $grade->grade = $s['grade'];
            $grade->feedback = $s['feedback'];
            $grade->timecreated = $now;
            $grade->timemodified = $now;
            insert_record('ishikawa_grades',$grade);
        }
    }

    redirect($CFG->wwwroot.'/mod/ishikawa/submissions.php?id='.$cm->id);
}

$buttontext = '';
$strishikawa = get_string('modulename', 'ishikawa');
$buttontext = update_module_button($cm->id, $course->id, $strishikawa);
$navigation = build_navigation('', $cm);
$meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/ishikawa/styles.css" />';
print_header_simple($ishikawa->name, "", $navigation, "", $meta, true, $buttontext,navmenu($course, $cm));


print_heading(get_string('title', 'ishikawa', $ishikawa->name));

$course_ctx = get_context_instance(CONTEXT_COURSE, $course->id);

$sql = "SELECT  u.id,u.picture, u.firstname, u.lastname, u.username,
                s.id as submission_id, s.timecreated,
                g.id as grade_id, g.grade, g.feedback
           FROM {$CFG->prefix}user u
           JOIN {$CFG->prefix}role_assignments ra
             ON (ra.userid = u.id AND
                 ra.contextid =  {$course_ctx->id} AND
                 ra.roleid IN ({$CFG->gradebookroles}))
      LEFT JOIN {$CFG->prefix}ishikawa_submissions s
             ON (s.userid = u.id and
                 s.ishikawaid = {$ishikawa->id})
      LEFT JOIN {$CFG->prefix}ishikawa_grades g
             ON (g.userid = u.id and
                 g.ishikawaid = {$ishikawa->id})";

if (!$students = get_records_sql($sql)) {
    print_heading(get_string('no_users_with_gradebookroles', 'ishikawa'));
} else {

   echo '<form method="post" action="submissions.php?id=',$cm->id, '" >',
        '<table id="ishikawa_submissions" class="generaltable">',
         '<tr>',
          '<th></th>',
          '<th>',get_string('fullname'),'</th>',
          '<th>',get_string('submission', 'ishikawa'),'</th>',
          '<th>',get_string('grade'),'</th>',
          '<th>',get_string('feedback'),'</th>',
         '</tr>';

  $tabindex = 0;
  foreach ($students as $s) {
      echo '<tr>',
          '<td>',print_user_picture($s, $course->id),'</td>',
          '<td>',fullname($s),'</td>',
          '<td>';
      if ($s->timecreated > 0) {
          echo userdate($s->timecreated), '&nbsp;',
               '<a href="image.php?id=',$cm->id,'&userid=',$s->id,'" target="_blank">',get_string('view'), '</a>';
      } else {
          echo get_string('never_sent', 'ishikawa');
      }
      echo '</td>',
           '<td>',
             choose_from_menu(make_grades_menu($ishikawa->grade), 'student['.$s->id.'][grade]', $s->grade,
                              get_string('nograde'),'',-1,true,false,$tabindex++),
           '</td>',
          '<td><textarea name="student[',$s->id,'][feedback]">',$s->feedback,'</textarea></td>',
         '</tr>';
  }
  echo '</table>',
       '<input type="submit" value="', get_string('confirm'), '" />',
       '</form>';
}

print_footer($course);

?>
