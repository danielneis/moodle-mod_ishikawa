<?php
/**
* ishikawa restore task that provides all the settings and steps to perform one
* complete restore of the activity
*/

require_once($CFG->dirroot . '/mod/ishikawa/backup/moodle2/restore_ishikawa_stepslib.php'); // Because it exists (must)

class restore_ishikawa_activity_task extends restore_activity_task {
/**
 * Define (add) particular settings this activity can have
 */
    protected function define_my_settings() {
    // No particular settings for this activity
    }

/**
 * Define (add) particular steps this activity can have
 */
    protected function define_my_steps() {
    // Choice only has one structure step
        $this->add_step(new restore_ishikawa_activity_structure_step('ishikawa_structure', 'ishikawa.xml'));
    }

/**
 * Define the contents in the activity that must be
 * processed by the link decoder
 */
    static public function define_decode_contents() {
        $contents = array();
        $contents[] = new restore_decode_content('ishikawa', array('intro'), 'ishikawa');
        $contents[] = new restore_decode_content('ishikawa_connections', array('src_type'), 'ishikawa_connection');
        $contents[] = new restore_decode_content('ishikawa_connections', array('dst_type'), 'ishikawa_connection');
        $contents[] = new restore_decode_content('ishikawa_axis_blocks', array('texto'), 'ishikawa_axis_block');
        $contents[] = new restore_decode_content('ishikawa_causes_blocks', array('texto'), 'ishikawa_causes_block');
        $contents[] = new restore_decode_content('ishikawa_consequences_blocks', array('texto'), 'ishikawa_consequences_block');
        $contents[] = new restore_decode_content('ishikawa_grades', array('feedback'), 'ishikawa_grade');
        $contents[] = new restore_decode_content('ishikawa_submissions', array('tail_text'), 'ishikawa_submission');
        $contents[] = new restore_decode_content('ishikawa_submissions', array('head_text'), 'ishikawa_submission');
        return $contents;
    }

/**
 * Define the decoding rules for links belonging
 * to the activity to be executed by the link decoder
 */
    static public function define_decode_rules() {
        $rules = array();
        $rules[] = new restore_decode_rule('ISHIKAWAVIEWBYID', '/mod/ishikawa/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('ISHIKAWAINDEX', '/mod/ishikawa/index.php?id=$1', 'course');
        return $rules;
    }

/**
 * Define the restore log rules that will be applied
 * by the {@link restore_logs_processor} when restoring
 * ishikawa logs. It must return one array
 * of {@link restore_log_rule} objects
 */
    static public function define_restore_log_rules() {
        $rules = array();
     /* $rules[] = new restore_log_rule('ishikawa', 'add', 'view.php?id={course_module}', '{ishikawa}');
        $rules[] = new restore_log_rule('ishikawa', 'update', 'view.php?id={course_module}', '{ishikawa}');
        $rules[] = new restore_log_rule('ishikawa', 'view', 'view.php?id={course_module}', '{ishikawa}');
        $rules[] = new restore_log_rule('ishikawa', 'choose', 'view.php?id={course_module}', '{ishikawa}');
        $rules[] = new restore_log_rule('ishikawa', 'choose again', 'view.php?id={course_module}', '{ishikawa}');
        $rules[] = new restore_log_rule('ishikawa', 'report', 'report.php?id={course_module}', '{ishikawa}');
      */return $rules; 
    }

/**
 * Define the restore log rules that will be applied
 * by the {@link restore_logs_processor} when restoring
 * course logs. It must return one array
 * of {@link restore_log_rule} objects
 *
 * Note this rules are applied when restoring course logs
 * by the restore final task, but are defined here at
 * activity level. All them are rules not linked to any module instance (cmid = 0)
 */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        // Fix old wrong uses (missing extension)
        $rules[] = new restore_log_rule('ishikawa', 'view all', 'index?id={course}', null,
        null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('ishikawa', 'view all', 'index.php?id={course}', null);
        return $rules;
    }

}
