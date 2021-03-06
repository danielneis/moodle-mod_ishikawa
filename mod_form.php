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
      
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_ishikawa_mod_form extends moodleform_mod {

    function definition() {
        global $CFG;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('diagramname', 'ishikawa'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->add_intro_editor(true, get_string('description', 'ishikawa'));

        $mform->addElement('modgrade', 'grade', get_string('grade'));
        $mform->setDefault('grade', 100);

        $mform->addElement('date_time_selector', 'timeavailable', get_string('availabledate', 'ishikawa'), array('optional'=>true));
        $mform->setDefault('timeavailable', time());
        $mform->addElement('date_time_selector', 'timedue', get_string('duedate', 'ishikawa'), array('optional'=>true));
        $mform->setDefault('timedue', time()+7*24*3600);

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'preventlate', get_string('preventlate', 'ishikawa'), $ynoptions);
        $mform->setDefault('preventlate', 0);

        $mform->addElement('text', 'maxchar', get_string('diagrammaxchar', 'ishikawa'), array('size'=>'5'));
        $mform->addRule('maxchar', null, 'required', null, 'client');
        $mform->setDefault('maxchar', 500);

        $updating = optional_param('update', 0, PARAM_INT);
        if ($updating) {
            $mform->addElement('text', 'maxlines', get_string('diagrammaxlines', 'ishikawa'), array('size'=>'5', 'disabled' => 'disabled'));
        } else {
            $mform->addElement('text', 'maxlines', get_string('diagrammaxlines', 'ishikawa'), array('size'=>'5'));
            $mform->addRule('maxlines', null, 'required', null, 'client');
        }
        $mform->setDefault('maxlines', 3);

        if ($updating) {
            $mform->addElement('text', 'maxcolumns', get_string('diagrammaxcolumns', 'ishikawa'), array('size'=>'5', 'disabled' => 'disabled'));
        } else {
            $mform->addElement('text', 'maxcolumns', get_string('diagrammaxcolumns', 'ishikawa'), array('size'=>'5'));
            $mform->addRule('maxcolumns', null, 'required', null, 'client');
        }
        $mform->setDefault('maxcolumns', 4);

        $features = new stdClass;
        $features->groups = true;
        $features->groupings = true;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);

        $this->add_action_buttons();
    }
}

?>
