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

$buttontext = '';
$strishikawa = get_string('modulename', 'ishikawa');
$buttontext = update_module_button($cm->id, $course->id, $strishikawa);
$navigation = build_navigation('', $cm);
print_header_simple($ishikawa->name, "", $navigation, "", "", true, $buttontext,navmenu($course, $cm));

$tablecolumns = array('picture', 'fullname', 'grade', 'submissioncomment', 'timemodified', 'timemarked', 'status', 'finalgrade');
$tableheaders = array('',
        get_string('fullname'),
        get_string('grade'),
        get_string('comment', 'ishikawa'),
        get_string('lastmodified').' ('.$course->student.')',
        get_string('lastmodified').' ('.$course->teacher.')',
        get_string('status'),
        get_string('finalgrade', 'grades'));

require_once($CFG->libdir.'/tablelib.php');
$table = new flexible_table('mod-assignment-submissions');

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($CFG->wwwroot.'/mod/ishikawa/submissions.php?id='.$cm->id.'&amp;currentgroup=');

$table->sortable(true, 'lastname');//sorted by lastname by default
$table->collapsible(true);
$table->initialbars(true);

$table->column_suppress('picture');
$table->column_suppress('fullname');

$table->column_class('picture', 'picture');
$table->column_class('fullname', 'fullname');
$table->column_class('grade', 'grade');
$table->column_class('submissioncomment', 'comment');
$table->column_class('timemodified', 'timemodified');
$table->column_class('timemarked', 'timemarked');
$table->column_class('status', 'status');
$table->column_class('finalgrade', 'finalgrade');

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'attempts');
$table->set_attribute('class', 'submissions');
$table->set_attribute('width', '100%');

$table->no_sorting('finalgrade');

$table->setup();

$select = "SELECT u.id, u.firstname, u.lastname, u.picture, u.imagealt,
                  s.id AS submissionid, s.grade,
                  s.timemodified
             FROM {$CFG->prefix}user u
        LEFT JOIN {$CFG->prefix}ishikawa_submissions s
               ON u.id = s.userid
              AND s.ishikawaid = {$ishikawa->id}";

if (($ausers = get_records_sql($select, $table->get_page_start(), $table->get_page_size())) !== false) {
    $grading_info = grade_get_grades($course->id, 'mod', 'ishikawa', $ishikawa->id, array_keys($ausers));
    foreach ($ausers as $auser) {
        $final_grade = $grading_info->items[0]->grades[$auser->id];
        $grademax = $grading_info->items[0]->grademax;
        $final_grade->formatted_grade = round($final_grade->grade,2) .' / ' . round($grademax,2);
        $locked_overridden = 'locked';
        if ($final_grade->overridden) {
            $locked_overridden = 'overridden';
        }

        /// Calculate user status
        $auser->status = 0;//($auser->timemarked > 0) && ($auser->timemarked >= $auser->timemodified);
        $picture = print_user_picture($auser, $course->id, $auser->picture, false, true);

        if (empty($auser->submissionid)) {
            $auser->grade = -1; //no submission yet
        }

        if (!empty($auser->submissionid)) {
            ///Prints student answer and student modified date
            ///attach file or print link to student answer, depending on the type of the ishikawa.
            ///Refer to print_student_answer in inherited classes.
            if ($auser->timemodified > 0) {
                $studentmodified = '<div id="ts'.$auser->id.'">'.$this->print_student_answer($auser->id)
                    . userdate($auser->timemodified).'</div>';
            } else {
                $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
            }
            ///Print grade, dropdown or text
            if ($auser->timemarked > 0) {
                $teachermodified = '<div id="tt'.$auser->id.'">'.userdate($auser->timemarked).'</div>';

                if ($final_grade->locked or $final_grade->overridden) {
                    $grade = '<div id="g'.$auser->id.'" class="'. $locked_overridden .'">'.$final_grade->formatted_grade.'</div>';
                } else if ($quickgrade) {
                    $menu = choose_from_menu(make_grades_menu($ishikawa->grade),
                            'menu['.$auser->id.']', $auser->grade,
                            get_string('nograde'),'',-1,true,false,$tabindex++);
                    $grade = '<div id="g'.$auser->id.'">'. $menu .'</div>';
                } else {
                    $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                }

            } else {
                $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
                if ($final_grade->locked or $final_grade->overridden) {
                    $grade = '<div id="g'.$auser->id.'" class="'. $locked_overridden .'">'.$final_grade->formatted_grade.'</div>';
                } else if ($quickgrade) {
                    $menu = choose_from_menu(make_grades_menu($ishikawa->grade),
                            'menu['.$auser->id.']', $auser->grade,
                            get_string('nograde'),'',-1,true,false,$tabindex++);
                    $grade = '<div id="g'.$auser->id.'">'.$menu.'</div>';
                } else {
                    $grade = '<div id="g'.$auser->id.'">'.$this->display_grade($auser->grade).'</div>';
                }
            }
        } else {
            $studentmodified = '<div id="ts'.$auser->id.'">&nbsp;</div>';
            $teachermodified = '<div id="tt'.$auser->id.'">&nbsp;</div>';
            $status          = '<div id="st'.$auser->id.'">&nbsp;</div>';

            if ($final_grade->locked or $final_grade->overridden) {
                $grade = '<div id="g'.$auser->id.'">'.$final_grade->formatted_grade . '</div>';
            } else if ($quickgrade) {   // allow editing
                $menu = choose_from_menu(make_grades_menu($ishikawa->grade),
                        'menu['.$auser->id.']', $auser->grade,
                        get_string('nograde'),'',-1,true,false,$tabindex++);
                $grade = '<div id="g'.$auser->id.'">'.$menu.'</div>';
            } else {
                $grade = '<div id="g'.$auser->id.'">-</div>';
            }

            if ($final_grade->locked or $final_grade->overridden) {
                $comment = '<div id="com'.$auser->id.'">'.$final_grade->str_feedback.'</div>';
            } else if ($quickgrade) {
                $comment = '<div id="com'.$auser->id.'">'
                    . '<textarea tabindex="'.$tabindex++.'" name="submissioncomment['.$auser->id.']" id="submissioncomment'
                    . $auser->id.'" rows="2" cols="20">'.($auser->submissioncomment).'</textarea></div>';
            } else {
                $comment = '<div id="com'.$auser->id.'">&nbsp;</div>';
            }
        }

        if (empty($auser->status)) { /// Confirm we have exclusively 0 or 1
            $auser->status = 0;
        } else {
            $auser->status = 1;
        }

        $strgrade  = get_string('grade');
        $buttontext = ($auser->status == 1) ? $strupdate : $strgrade;

        ///No more buttons, we use popups ;-).
        $popup_url = '/mod/ishikawa/submissions.php?id='.$cm->id.'&amp;userid='.$auser->id.'&amp;mode=single';
        $button = link_to_popup_window ($popup_url, 'grade'.$auser->id, $buttontext, 600, 780, $buttontext, 'none', true, 'button'.$auser->id);

        $status  = '<div id="up'.$auser->id.'" class="s'.$auser->status.'">'.$button.'</div>';

        $finalgrade = '<span id="finalgrade_'.$auser->id.'">'.$final_grade->str_grade.'</span>';
        $userlink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $auser->id . '&amp;course=' . $course->id . '">' . fullname($auser, has_capability('moodle/site:viewfullnames', $context)) . '</a>';
        $row = array($picture, $userlink, $grade, $comment, $studentmodified, $teachermodified, $status, $finalgrade);

        $table->add_data($row);
    }
    $table->print_html();  /// Print the whole table
}
?>
