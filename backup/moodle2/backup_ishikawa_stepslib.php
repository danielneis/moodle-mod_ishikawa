<?php
/**
  * Define all the backup steps that will be used by the backup_ishikawa_activity_task
  */
/**
 * Define the complete choice structure for backup, with file and id annotations
 */     

class backup_choice_activity_structure_step extends backup_activity_structure_step {
       
      protected function define_structure() {
          // To know if we are including userinfo
          $userinfo = $this->get_setting_value('userinfo');
          
          // Define each element separated
          $choice = new backup_nested_element('ishikawa', array('id'), array(
                                              'name', 'intro', 'maxchar', 'maxlines',
                                              'maxcolumns', 'grade', 'preventlate', 'timedue',
                                              'timeavaliable', 'introformat', 'timemodified'));
          
          $options = new backup_nested_element('ishikawa_axis_blocks');
          $option = new backup_nested_element('ishikawa_axis_block', array('id'), array(
                                              'submissionid', 'nivel_x', 'texto', 'cor'));
                            
          $answers = new backup_nested_element('ishikawa_causes_blocks');
          $answer = new backup_nested_element('ishikawa_causes_block', array('id'), array(
                                              'submissionid', 'nivel_x', 'nivel_y', 'texto' ,'cor'));

          $answers = new backup_nested_element('ishikawa_connections');
          $answer = new backup_nested_element('ishikawa_connection', array('id'), array(
                                              'submissionid', 'src_id', 'src_type', 'dst_id' ,'dst_type'));
          
          $answers = new backup_nested_element('ishikawa_consequences_blocks');
          $answer = new backup_nested_element('ishikawa_consequences_block', array('id'), array(
                                              'submissionid', 'nivel_x', 'nivel_y', 'texto' ,'cor'));
          
          $answers = new backup_nested_element('ishikawa_grades');
          $answer = new backup_nested_element('ishikawa_grade', array('id'), array(
                                              'userid', 'grade', 'feedback', 'timecreated' ,'timemodified'));
           
          $answers = new backup_nested_element('ishikawa_submissions');
          $answer = new backup_nested_element('ishikawa_submission', array('id'), array(
                                              'ishikawaid', 'userid', 'tail_text', 'head_text' ,'timecreated', 'timemodified'));
          
          // Build the tree
          
          // Define sources
          
          // Define id annotations
          
          // Define file annotations
          
          // Return the root element (choice), wrapped into standard activity structure
          }
  }
