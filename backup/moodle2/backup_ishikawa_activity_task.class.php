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
/**
 * @package mod_ishikawa
 *
 * @author Luis Henrique Mulinari
 * @author Daniel Neis Araujo
 * @upgrade moodle 2.0+ Caio Bressan Doneda
 **/
      
defined('MOODLE_INTERNAL') || die();

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
       $content= preg_replace($search, '$@ISHIKAWAINDEX*$2@$', $content);

       // Link to ishikawa view by moduleid
       $search="/(".$base."\/mod\/ishikawa\/view.php\?id\=)([0-9]+)/";
       $content= preg_replace($search, '$@ISHIKAWAVIEWBYID*$2@$', $content);

       return $content;
   }
}
