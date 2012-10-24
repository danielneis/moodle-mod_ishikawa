<?php

require_once("../../config.php");
require_once($CFG->libdir.'/gradelib.php');
require_once("lib.php");

$id         = required_param('id', PARAM_INT);// Course module ID
$group      = optional_param('group', 0, PARAM_INT);

if (! $cm = get_coursemodule_from_id('ishikawa', $id)) {
    error("Course Module ID was incorrect");
}

if (! $ishikawa = $DB->get_record("ishikawa", "id", $cm->instance)) {
    error("ishikawa ID was incorrect");
}

if (! $course = $DB->get_record("course", "id", $ishikawa->course)) {
    error("Course is misconfigured");
}

require_login($course->id, false, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

require_capability('mod/ishikawa:grade', $context);

if ($data = data_submitted()) {

    $now = time();
    $grades = array();
    foreach ($data->student as $userid => $s) {
        if ($grade = ishikawa_get_grade($userid, $ishikawa->id)) {
            $grade->grade = $s['grade'];
            $grade->feedback = $s['feedback'];
            $grade->timecreated = $now;
            $grade->timemodified = $now;
            $DB->update_record('ishikawa_grades',$grade);

        } else {
            $grade = new stdclass();
            $grade->ishikawaid = $ishikawa->id;
            $grade->userid = $userid;
            $grade->grade = $s['grade'];
            $grade->feedback = $s['feedback'];
            $grade->timecreated = $now;
            $grade->timemodified = $now;
            $DB->insert_record('ishikawa_grades',$grade);
        }
        $g = new stdclass();
        $g->id    = $grade->userid;
        $g->userid = $grade->userid;
        $g->rawgrade = $grade->grade;
        $g->feedback = $grade->feedback;
        grade_update('mod/ishikawa', $ishikawa->course, 'mod', 'ishikawa', $ishikawa->id, 0, $g);
    }

    redirect($CFG->wwwroot.'/mod/ishikawa/submissions.php?id='.$cm->id. '&group='.$group);
}

$buttontext = '';
$strishikawa = get_string('modulename', 'ishikawa');
$buttontext = update_module_button($cm->id, $course->id, $strishikawa);
$navigation = build_navigation(get_string('submissions', 'ishikawa'), $cm);
$meta = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/mod/ishikawa/styles.css" />';
print_header_simple($ishikawa->name, "", $navigation, "", $meta, true, $buttontext,navmenu($course, $cm));

print_heading(get_string('title', 'ishikawa', $ishikawa->name));

if ($groupmode = groups_get_activity_groupmode($cm)) {   // Groups are being used
    groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/ishikawa/submissions.php?id='.$id);
}

$course_ctx = get_context_instance(CONTEXT_COURSE, $course->id);
$grade_item = $DB->get_record('grade_items', 'itemmodule', 'ishikawa', 'iteminstance', $ishikawa->id, 'courseid', $course->id);

$params = array($course_ctx->id, $CFG->gradebookroles, $ishikawa->id, $ishikawa->id, $grade_item->id);
$sql = "SELECT  u.id,u.picture, u.firstname, u.lastname, u.username,
                s.id as submission_id, s.timecreated,
                g.id as grade_id, g.grade, g.feedback,
                gg.id as grade_grades_id, gg.rawgrade, gg.finalgrade, gg.locked, gg.overridden
           FROM {user} u
           JOIN {role_assignments} ra
             ON (ra.userid = u.id AND
                 ra.contextid =  ? AND
                 ra.roleid IN (?))
      LEFT JOIN {ishikawa_submissions} s
             ON (s.userid = u.id and
                 s.ishikawaid = ? )
      LEFT JOIN {ishikawa_grades} g
             ON (g.userid = u.id and
                 g.ishikawaid = ? )
      LEFT JOIN {grade_grades} gg
             ON (gg.userid = u.id AND
                 gg.itemid = ? )";

if (!$group) {
    $group = groups_get_activity_group($cm, true);
}
if ($group > 0) {
    $sql .= " JOIN {groups_members} gm
                ON (gm.userid = u.id AND
                    gm.groupid = ? )";
    $params[] = $group;
}

$sql .= "ORDER BY firstname,lastname,username";

if (!$students = $DB->get_records_sql($sql,$params)) {
    print_heading(get_string('no_users_with_gradebookroles', 'ishikawa'));
} else {

    $act = "submissions.php?id={$cm->id}&group={$group}";

   echo '<form method="post" action="',$act,'" >',
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
           '<td>';
      if ($s->locked or $s->overridden) {
          echo $s->finalgrade;
      } else {
          echo choose_from_menu(make_grades_menu($ishikawa->grade), 'student['.$s->id.'][grade]', $s->grade,
                              get_string('nograde'),'',-1,true,false,$tabindex++);
      }
      echo '</td>',
          '<td><textarea name="student[',$s->id,'][feedback]">',$s->feedback,'</textarea></td>',
         '</tr>';
  }
  echo '</table>',
       '<input type="submit" value="', get_string('confirm'), '" />',
       '</form>';
}

print_footer($course);

?>
