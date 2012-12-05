<?php
 
 require_once($CFG->dirroot . '/mod/ishikawa/backup/moodle2/backup_ishikawa_stepslib.php'); // Because it exists (must)
 require_once($CFG->dirroot . '/mod/ishikawa/backup/moodle2/backup_ishikawa_settingslib.php'); // Because it exists (optional)
  
/**
* choice backup task that provides all the settings and steps to perform one
* complete backup of the activity
*/
class backup_ishikawa_activity_task extends backup_activity_task {
   /**
   * Define (add) particular settings this activity can have
   */
   protected function define_my_settings() {
   }
                    
   /**
   * Define (add) particular steps this activity can have
   */
   protected function define_my_steps() {
   $this->add_step(new backup_ishikawa_activity_structure_step('ishikawa_structure', 'ishikawa.xml'));
   }
                                  
   /**
   * Code the transformations to perform in the activity in
   * order to get transportable (encoded) links
   */
   static public function encode_content_links($content) {
       return $content;
   }
}
