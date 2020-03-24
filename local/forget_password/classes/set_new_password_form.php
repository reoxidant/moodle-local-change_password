<?php
require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/login/lib.php');
require_once($CFG->dirroot . '/local/forget_password/lib.php');

class set_new_password_form extends moodleform
{
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $mform->setDisableShortforms(true);
        $mform->addElement('header', 'forgetpassword', get_string('pluginname', 'local_forget_password'), '');

        // Token gives authority to change password.
        $mform->addElement('hidden', 'token', '');
        $mform->setType('token', PARAM_ALPHANUM);

        // Visible elements.
        $mform->addElement('hidden', 'username', get_string('username'));
        $mform->setType('username', PARAM_RAW);
        $mform->addElement('static', 'username2', get_string('username'));

        $policies = array();
        if (!empty($CFG->passwordpolicy)) {
            $policies[] = print_password_policy();
        }
        if (!empty($CFG->passwordreuselimit) and $CFG->passwordreuselimit > 0) {
            $policies[] = get_string('informminpasswordreuselimit', 'auth', $CFG->passwordreuselimit);
        }
        if ($policies) {
            $mform->addElement('static', 'passwordpolicyinfo', '', implode('<br />', $policies));
        }

        $mform->addElement('password', 'newpassword_log1', get_string('newpassword', 'local_forget_password'));
        $mform->addRule('newpassword_log1', get_string('required', 'local_forget_password'), 'required', null, 'client');
        $mform->setType('newpassword_log1', PARAM_RAW);

        $mform->addElement('password', 'newpassword_log2', get_string('newpassword', 'local_forget_password') . ' (' . get_String('again', 'local_forget_password') . ')');
        $mform->addRule('newpassword_log2', get_string('required', 'local_forget_password'), 'required', null, 'client');
        $mform->setType('newpassword_log2', PARAM_RAW);

        // buttons
        if (get_user_preferences('auth_forcepasswordchange')) {
            $this->add_action_buttons(false);
        } else {
            $this->add_action_buttons(true);
        }
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        $user = $DB->get_record('user',  array('username' => $data['username']), 'id, firstname');

        $data['firstName'] = $user->firstname;

        // Ignore submitted username.
        if ($data['newpassword_log1'] <> $data['newpassword_log2']) {
            $errors['newpassword_log1'] = get_string('passwordsdiffer', 'local_forget_password');
            $errors['newpassword_log2'] = get_string('passwordsdiffer', 'local_forget_password');
            return $errors;
        }

        $errmsg = ''; // Prevents eclipse warnings.
        if (!my_check_password_policy($data['newpassword_log1'], $errmsg, $data)) {
            $errors['newpassword_log1'] = $errmsg;
            $errors['newpassword_log2'] = $errmsg;
            return $errors;
        }

        if (user_is_previously_used_password($user->id, $data['newpassword_log1'])) {
            $errors['newpassword_log1'] = get_string('errorpasswordreused', 'local_forget_password');
            $errors['newpassword_log2'] = get_string('errorpasswordreused', 'local_forget_password');
        }

        return $errors;
    }
}