<?php
 
 require_once($CFG->dirroot . '/mod/ishikawa/backup/moodle2/backup_ishikawa_stepslib.php'); // Because it exists (must)
 require_once($CFG->dirroot . '/mod/ishikawa/backup/moodle2/backup_ishikawa_settingslib.php'); // Because it exists (optional)
  
/**
 * ishikawa backup task that provides all the settings and steps to perform one
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
       global $CFG;

       $base = preg_quote($CFG->wwwroot,"/");

       // Link to the list of ishikawa
       $search="/(".$base."\/mod\/ishikawa\/index.php\?id\=)([0-9]+)/";
       $content= preg_replace($search, '$@CHOICEINDEX*$2@$', $content);

       // Link to ishikawa view by moduleid
       $search="/(".$base."\/mod\/ishikawa\/view.php\?id\=)([0-9]+)/";
       $content= preg_replace($search, '$@CHOICEVIEWBYID*$2@$', $content);

       return $content;
   }
}