<?php
/**
* Structure step to restore one ishikawa activity
 */
class restore_ishikawa_activity_structure_step extends restore_activity_structure_step {
     
     protected function define_structure() {
                
         $paths = array();
         $userinfo = $this->get_setting_value('userinfo');
       
         $paths[] = new restore_path_element('ishikawa', '/activity/ishikawa');
         $paths[] = new restore_path_element('ishikawa_submission', '/activity/ishikawa/ishikawa_submissions/ishikawa_submission');
         if ($userinfo) {
             $paths[] = new restore_path_element('ishikawa_axis_block', '/activity/ishikawa/ishikawa_axis_blocks/ishikawa_axis_block');
             $paths[] = new restore_path_element('ishikawa_causes_block', '/activity/ishikawa/ishikawa_causes_blocks/ishikawa_causes_block');
             $paths[] = new restore_path_element('ishikawa_consequences_block', '/activity/ishikawa/ishikawa_consequences_blocks/ishikawa_consequences_block');
             $paths[] = new restore_path_element('ishikawa_grade', '/activity/ishikawa/ishikawa_grades/ishikawa_grades');
             $paths[] = new restore_path_element('ishikawa_connection', '/activity/ishikawa/ishikawa_connections/ishikawa_connection');
         }
                                                                          
         // Return the paths wrapped into standard activity structure
         return $this->prepare_activity_structure($paths);
     }   
                                                                                                       
     protected function process_ishikawa($data) {
         global $DB;
                                                                                                                  
         $data = (object)$data;
         $oldid = $data->id;
         $data->course = $this->get_courseid();
                                                                                                                                                 
         $data->timeopen = $this->apply_date_offset($data->timeopen);
         $data->timeclose = $this->apply_date_offset($data->timeclose);
         $data->timemodified = $this->apply_date_offset($data->timemodified);
                                                                                                                                                            // insert the choice record
         $newitemid = $DB->insert_record('ishikawa', $data);
         // immediately after inserting "activity" record, call this
         $this->apply_activity_instance($newitemid);
     }

     protected function process_ishikawa_axis($data) {
         global $DB;

         $data = (object)$data;
         $oldid = $data->id;
                    
         $data->ishikawaid = $this->get_new_parentid('ishikawa');
         $data->timemodified = $this->apply_date_offset($data->timemodified);
                                      
         $newitemid = $DB->insert_record('ishikawa_axis_blocks', $data);
         $this->set_mapping('ishikawa_axis_block', $oldid, $newitemid);
     }
                                                           
     protected function process_ishikawa_causes($data) {
         global $DB;

         $data = (object)$data;

         $data->ishikawaid = $this->get_new_parentid('ishikawa');
         $data->submissionid = $this->get_mappingid('ishikawa_axis_block', $data->optionid);
         $data->userid = $this->get_mappingid('user', $data->userid);
         $data->timemodified = $this->apply_date_offset($data->timemodified);

         $newitemid = $DB->insert_record('ishikawa_causes_blocks', $data);
         // No need to save this mapping as far as nothing depend on it
         // (child paths, file areas nor links decoder)
     }
     
     protected function process_ishikawa_consequences($data) {
         global $DB;
     }
     
     protected function process_ishikawa_connections($data) {
         global $DB;
     }
     
     protected function process_ishikawa_grades($data) {
         global $DB;
     }
     
     protected function process_ishikawa_submissions($data) {
         global $DB;
     }
     
     protected function after_execute() {
     // Add ishikawa related files, no need to match by itemname (just internally handled context)
     $this->add_related_files('mod_ishikawa', 'intro', null);
     }
}
