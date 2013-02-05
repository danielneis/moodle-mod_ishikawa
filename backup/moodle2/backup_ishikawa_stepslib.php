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
      
/**
 * Define all the backup steps that will be used by the backup_ishikawa_activity_task
 */

/**
 * Define the complete ishikawa structure for backup, with file and id annotations
 */
class backup_ishikawa_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $ishikawa = new backup_nested_element('ishikawa', array('id'), array(
                                              'name', 'intro', 'maxchar', 'maxlines',
                                              'maxcolumns', 'grade', 'preventlate', 'timedue',
                                              'timeavailable', 'introformat', 'timemodified'));

        $submissions_container = new backup_nested_element('submissions');

        $submissions = new backup_nested_element('ishikawa_submission', array('id'), array(
                                                 'ishikawaid', 'userid', 'tail_text', 'head_text',
                                                 'timecreated', 'timemodified'));

        $axis_container = new backup_nested_element('axis_blocks');

        $axis = new backup_nested_element('ishikawa_axis_block', array('id'), array(
                                          'submissionid', 'nivel_x', 'texto', 'cor'));

        $causes_container = new backup_nested_element('ishikawa_causes_blocks');

        $causes = new backup_nested_element('ishikawa_causes_block', array('id'), array(
                                            'submissionid', 'nivel_x', 'nivel_y', 'texto' ,'cor'));

        $consequences_container = new backup_nested_element('ishikawa_consequences_blocks');

        $consequences = new backup_nested_element('ishikawa_consequences_block', array('id'), array(
                                                  'submissionid', 'nivel_x', 'nivel_y', 'texto' ,'cor'));

        $connections_container = new backup_nested_element('ishikawa_connections');

        $connections = new backup_nested_element('ishikawa_connection', array('id'), array(
                                                         'submissionid', 'src_id', 'src_type', 'dst_id' ,'dst_type'));

        $grades_container = new backup_nested_element('ishikawa_grades');
        $grades = new backup_nested_element('ishikawa_grade', array('id'), array(
                                            'userid', 'grade', 'feedback', 'timecreated' ,'timemodified'));

        // Build the tree
        $ishikawa->add_child($submissions_container);
        $submissions_container->add_child($submissions);

        $submissions->add_child($axis_container);
        $axis_container->add_child($axis);

        $submissions->add_child($causes_container);
        $causes_container->add_child($causes);

        $submissions->add_child($consequences_container);
        $consequences_container->add_child($consequences);

        $submissions->add_child($connections_container);
        $connections_container->add_child($connections);

        $ishikawa->add_child($grades_container);
        $grades_container->add_child($grades);

        // Define sources
        $ishikawa->set_source_table('ishikawa', array('id' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            $submissions->set_source_sql('SELECT *
                                            FROM {ishikawa_submissions}
                                           WHERE ishikawaid = :ishikawa',
                                         array('ishikawa' => backup::VAR_PARENTID));

            $axis->set_source_table('ishikawa_axis_blocks', array('submissionid' => backup::VAR_PARENTID));
            $causes->set_source_table('ishikawa_causes_blocks', array('submissionid' => backup::VAR_PARENTID));
            $consequences->set_source_table('ishikawa_consequences_blocks', array('submissionid' => backup::VAR_PARENTID));
            $connections->set_source_table('ishikawa_connections', array('submissionid' => backup::VAR_PARENTID));
            $grades->set_source_table('ishikawa_grades', array('ishikawaid' => backup::VAR_PARENTID));
        }

        // Define id annotations
        $submissions->annotate_ids('user', 'userid');

        // Return the root element (ishikawa), wrapped into standard activity structure
        return $this->prepare_activity_structure($ishikawa);
    }
}
