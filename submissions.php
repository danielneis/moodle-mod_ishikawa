<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// this file contains all the functions that aren't needed by core moodle
// but start becoming required once we're actually inside the ishikawa module.
/**
 * @package     mod
 * @subpackage  ishikawa
 **/

require_once("../../config.php");
require_once($CFG->libdir.'/gradelib.php');
require_once("lib.php");

$id         = required_param('id', PARAM_INT);// Course module ID
$group      = optional_param('group', 0, PARAM_INT);

if (! $cm = get_coursemodule_from_id('ishikawa', $id)) {
    print_error('invalidcoursemodule');
}

if (! $ishikawa = $DB->get_record("ishikawa", array("id" => $cm->instance))) {
    print_error('ishikawa ID was incorrect', 'ishikawa');
}

if (! $course = $DB->get_record("course", array("id" => $ishikawa->course))) {
    print_error('Course is misconfigured', 'ishikawa');
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

$strishikawa = get_string('modulename', 'ishikawa');
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'ishikawa')); //New function update_module_button
$PAGE->set_url('/mod/ishikawa/submissions.php', array('id'=>$course->id));  
$PAGE->navbar->add($strishikawa);
$PAGE->set_title($strishikawa);
$PAGE->set_heading($course->fullname);      
echo $OUTPUT->header();
$title = get_string('title', 'ishikawa');
echo $OUTPUT->heading($title . $ishikawa->name);
if ($groupmode = groups_get_activity_groupmode($cm)) {   // Groups are being used
    groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/ishikawa/submissions.php?id='.$id);
}

$course_ctx = get_context_instance(CONTEXT_COURSE, $course->id);
$grade_item = $DB->get_record('grade_items', array('itemmodule' => 'ishikawa', 'iteminstance' => $ishikawa->id, 'courseid' => $course->id));

$userfields = user_picture::fields('u', array('id', 'picture' ,'firstname', 'lastname' , 'username'));
$sql = "SELECT  $userfields,
                s.id as submission_id, s.timecreated,
                g.id as grade_id, g.grade, g.feedback,
                gg.id as grade_grades_id, gg.rawgrade, gg.finalgrade, gg.locked, gg.overridden
          FROM {user} u
          JOIN {role_assignments} ra
            ON (ra.userid = u.id AND
                ra.contextid =  {$course_ctx->id} AND
                ra.roleid IN ({$CFG->gradebookroles}))
          LEFT JOIN {ishikawa_submissions} s
            ON (s.userid = u.id and
                s.ishikawaid = {$ishikawa->id})
          LEFT JOIN {ishikawa_grades} g
            ON (g.userid = u.id and
               g.ishikawaid = {$ishikawa->id})
          LEFT JOIN {grade_grade}s gg
            ON (gg.userid = u.id AND
               gg.itemid = {$grade_item->id})";
 
 if (!$group) {
    $group = groups_get_activity_group($cm, true);
 }
 if ($group > 0) {
    $sql .= " JOIN {groups_members} gm
                ON (gm.userid = u.id AND
                   gm.groupid = {$group})";
 }
 
 $sql .= "ORDER BY firstname,lastname,username";
 if (!$students = $DB->get_records_sql($sql)) {
         echo $OUTPUT->heading(get_string('no_users_with_gradebookroles', 'ishikawa'));
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
                     '<td>', $OUTPUT->user_picture($s, array('courseid' => $course->id)), '</td>',
                     '<td>', fullname($s),'</td>',
                     '<td>';
                        if ($s->timecreated > 0) {
                            echo userdate($s->timecreated), '&nbsp;',
                            '<a href="image.php?id=',$cm->id,'&amp;userid=',$s->id,'" target="_blank">',get_string('view'), '</a>';
                        } else {
                            echo get_string('never_sent', 'ishikawa');
                        }
                   echo '</td>',
                   '<td>';
                        if ($s->locked or $s->overridden) {
                            echo $s->finalgrade;
                        } else {
                            $attributes['disabled'] = false;
                            $attributes['tabindex'] = $tabindex++;
                            echo html_writer::select(make_grades_menu($ishikawa->grade),'student['.$s->id.'][grade]', $s->grade, array(get_string('nograde')), $attributes);
                        }
                   echo '</td>',
                   '<td><textarea name="student[',$s->id,'][feedback]">',$s->feedback,'</textarea></td>',
                '</tr>';
                }
            echo '</table>',
            '<input type="submit" value="', get_string('confirm'), '" />',
       '</form>';
}
echo $OUTPUT->footer();
?>
