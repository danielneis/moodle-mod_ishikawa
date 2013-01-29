<?php
/**
 * Structure step to restore one ishikawa activity
 */
global $numCausas, $numConse;
class restore_ishikawa_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('ishikawa', '/activity/ishikawa');
        if ($userinfo){
            $paths[] = new restore_path_element('ishikawa_submission', '/activity/ishikawa/ishikawa_submissions/ishikawa_submission');
            $paths[] = new restore_path_element('ishikawa_connection', '/activity/ishikawa/ishikawa_submissions/ishikawa_submission/ishikawa_connections/ishikawa_connection');
            $paths[] = new restore_path_element('ishikawa_axis_block', '/activity/ishikawa/ishikawa_submissions/ishikawa_submission/ishikawa_axis_blocks/ishikawa_axis_block');
            $paths[] = new restore_path_element('ishikawa_causes_block', '/activity/ishikawa/ishikawa_submissions/ishikawa_submission/ishikawa_causes_blocks/ishikawa_causes_block');
            $paths[] = new restore_path_element('ishikawa_consequences_block', '/activity/ishikawa/ishikawa_submissions/ishikawa_submission/ishikawa_consequences_blocks/ishikawa_consequences_block');
            $paths[] = new restore_path_element('ishikawa_grade', '/activity/ishikawa/ishikawa_grades/ishikawa_grade');
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
        // insert the ishikawa record
        $newitemid = $DB->insert_record('ishikawa', $data);
        // immediately after inserting "activity" record, call this
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

    protected function process_ishikawa_connection($data) {
        global $DB;
        $data = (object)$data;
        $sql = 'select max(id) from ishikawa_causes_blocks';
        $maxId = $DB->get_field_sql($sql);
        $sqlAxis = 'select max(id) from ishikawa_axis_blocks';
        $maxIdAxis = $DB->get_field_sql($sqlAxis);
        
        $dif = $maxId - (4*3); // 4x3 é o numero de linhas e colunas (default) do diagrama, precisa fazer a pesquisa dos valores corretos ainda
        $difAxis = $maxIdAxis - 4; 
        if ($data->src_type == 'axis'){
            $data->src_id = $data->src_id + $difAxis; 
        }else
            $data->src_id = $data->src_id + $dif;        
        
        if ($data->dst_type == 'axis'){
            $data->dst_id = $data->dst_id + $difAxis;
        }else
        $data->dst_id = $data->dst_id + $dif;        
       
        $oldid = $data->id;
        $data->submissionid = $this->get_new_parentid('ishikawa_submission');

        $newitemid = $DB->insert_record('ishikawa_connections', $data);
        $this->set_mapping('ishikawa_connection', $oldid, $newitemid);
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

    protected function after_execute() {
        // Add ishikawa related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_ishikawa', 'intro', null);
    }
}
