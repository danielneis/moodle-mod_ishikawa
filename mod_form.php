<?php
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_ishikawa_mod_form extends moodleform_mod {

    function definition() {
        global $CFG;
        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('diagramname', 'ishikawa'), array('size'=>'64'));
        $mform->addRule('name', null, 'required', null, 'client');


        $mform->addElement('htmleditor', 'description', get_string('description', 'assignment'));
        $mform->setType('description', PARAM_RAW);
        $mform->setHelpButton('description', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');
        $mform->addRule('description', get_string('required'), 'required', null, 'client');

        $mform->addElement('modgrade', 'grade', get_string('grade'));
        $mform->setDefault('grade', 100);

        $mform->addElement('date_time_selector', 'timeavailable', get_string('availabledate', 'assignment'), array('optional'=>true));
        $mform->setDefault('timeavailable', time());
        $mform->addElement('date_time_selector', 'timedue', get_string('duedate', 'assignment'), array('optional'=>true));
        $mform->setDefault('timedue', time()+7*24*3600);

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'preventlate', get_string('preventlate', 'assignment'), $ynoptions);
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
