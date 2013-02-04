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

/**
 * Structure step to restore one ishikawa activity
 */
class restore_ishikawa_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('ishikawa', '/activity/ishikawa');
        if ($userinfo) {
            $paths[] = new restore_path_element('ishikawa_submission',         '/activity/ishikawa/submissions/ishikawa_submission');
            $paths[] = new restore_path_element('ishikawa_axis_block',         '/activity/ishikawa/submissions/ishikawa_submission/axis_blocks/ishikawa_axis_block');
            $paths[] = new restore_path_element('ishikawa_causes_block',       '/activity/ishikawa/submissions/ishikawa_submission/ishikawa_causes_blocks/ishikawa_causes_block');
            $paths[] = new restore_path_element('ishikawa_consequences_block', '/activity/ishikawa/submissions/ishikawa_submission/ishikawa_consequences_blocks/ishikawa_consequences_block');
            $paths[] = new restore_path_element('ishikawa_connection',         '/activity/ishikawa/submissions/ishikawa_submission/ishikawa_connections/ishikawa_connection');
            $paths[] = new restore_path_element('ishikawa_grade',              '/activity/ishikawa/ishikawa_grades/ishikawa_grade');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_ishikawa($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timedue = $this->apply_date_offset($data->timedue);
        $data->timeavailable = $this->apply_date_offset($data->timeavailable);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('ishikawa', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_ishikawa_submission($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->ishikawaid = $this->get_new_parentid('ishikawa');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $newitemid = $DB->insert_record('ishikawa_submissions', $data);
        $this->set_mapping('ishikawa_submission', $oldid, $newitemid);
    }

    protected function process_ishikawa_axis_block($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->submissionid = $this->get_new_parentid('ishikawa_submission');

        $newitemid = $DB->insert_record('ishikawa_axis_blocks', $data);
        $this->set_mapping('ishikawa_axis_block', $oldid, $newitemid);
    }

    protected function process_ishikawa_causes_block($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->submissionid = $this->get_new_parentid('ishikawa_submission');

        $newitemid = $DB->insert_record('ishikawa_causes_blocks', $data);
        $this->set_mapping('ishikawa_causes_block', $oldid, $newitemid);
    }

    protected function process_ishikawa_consequences_block($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->submissionid = $this->get_new_parentid('ishikawa_submission');

        $newitemid = $DB->insert_record('ishikawa_consequences_blocks', $data);
        $this->set_mapping('ishikawa_consequences_block', $oldid, $newitemid);
    }

    protected function process_ishikawa_grade($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->ishikawaid = $this->get_new_parentid('ishikawa');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $newitemid = $DB->insert_record('ishikawa_grades', $data);
        $this->set_mapping('ishikawa_grade', $oldid, $newitemid);
    }

    protected function process_ishikawa_connection($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->submissionid = $this->get_new_parentid('ishikawa_submission');

        if ($data->src_type == 'axis'){
            $data->src_id = $data->src_id + $this->verifies_difference_axis($data);
        } else {
            $data->src_id = $data->src_id + $this->verifies_difference($data);
        }

        if ($data->dst_type == 'axis'){
            $data->dst_id = $data->dst_id + $this->verifies_difference_axis($data);
        } else {
            $data->dst_id = $data->dst_id + $this->verifies_difference($data);
        }

        $newitemid = $DB->insert_record('ishikawa_connections', $data);
        $this->set_mapping('ishikawa_connection', $oldid, $newitemid);
    }

    protected function verifies_difference($data){
        global $DB;

        $sql = "select max(id)
                  from ishikawa_causes_blocks as c
                 where c.submissionid = :sid";

        $max_id = $DB->get_field_sql($sql, array('sid' => $data->submissionid));
        return $max_id - ($this->select_max_lines($data) * $this->select_max_columns($data));
    }

    protected function verifies_difference_axis($data){
        global $DB;

        $sql = "SELECT max(id)
                  FROM ishikawa_axis_blocks as c
                 WHERE c.submissionid = :sid";

        $max_id = $DB->get_field_sql($sql, array('sid' => $data->submissionid));
        return $max_id - $this->select_max_columns($data);
    }

    protected function select_max_lines($data){
        global $DB;

        $sql = "SELECT distinct i.maxlines
                  FROM ishikawa as i
                  JOIN ishikawa_submissions as s
                    ON (i.id = s.ishikawaid)
                 WHERE s.id = :sid";

        return $DB->get_field_sql($sql, array('sid' => $data->submissionid));
    }

    protected function select_max_columns($data){
        global $DB;

        $sql = "SELECT distinct i.maxcolumns
                  FROM ishikawa as i
                  JOIN ishikawa_submissions as s
                    ON (i.id = s.ishikawaid)
                 WHERE s.id = :sid";

        return $DB->get_field_sql($sql, array('sid' => $data->submissionid));
    }

    protected function after_execute() {
    }
}
