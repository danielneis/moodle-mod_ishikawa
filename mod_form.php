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

        $mform->addElement('text', 'maxchar', get_string('diagrammaxchar', 'ishikawa'), array('size'=>'5'));
        $mform->addRule('maxchar', null, 'required', null, 'client');
        $mform->setDefault('maxchar', 500);

        $mform->addElement('date_time_selector', 'timeavailable', get_string('availabledate', 'assignment'), array('optional'=>true));
        $mform->setDefault('timeavailable', time());
        $mform->addElement('date_time_selector', 'timedue', get_string('duedate', 'assignment'), array('optional'=>true));
        $mform->setDefault('timedue', time()+7*24*3600);

        $features = new stdClass;
        $features->groups = false;
        $features->groupings = false;
        $features->groupmembersonly = false;
        $this->standard_coursemodule_elements($features);

        $this->add_action_buttons();
    }
}

?>
