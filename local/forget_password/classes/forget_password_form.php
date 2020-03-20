<?php

require_once("$CFG->libdir/formslib.php");

class forget_password_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $mform->setDisableShortforms(true);
        $mform->addElement('header', 'forgetpassword', get_string('forgetpassword', 'local_forget_password'), '');

        // Include the username in the form so browsers will recognise that a password is being set.
        $mform->addElement('text', 'username', '', 'style="display: none;"');
        $mform->setType('username', PARAM_RAW);
        // Token gives authority to change password.
        $mform->addElement('hidden', 'token', '');
        $mform->setType('token', PARAM_ALPHANUM);

        // Visible elements.
        $mform->addElement('static', 'username2', get_string('username'));

        $policies = array();
        if ($policies) {
            $mform->addElement('static', 'passwordpolicyinfo', '', implode('<br />', $policies));
        }
        $mform->addElement('password', 'password', get_string('newpassword'));
        $mform->addRule('password', get_string('required'), 'required', null, 'client');
        $mform->setType('password', PARAM_RAW);

        $strpasswordagain = get_string('newpassword') . ' (' . get_string('again') . ')';
        $mform->addElement('password', 'password2', $strpasswordagain);
        $mform->addRule('password2', get_string('required'), 'required', null, 'client');
        $mform->setType('password2', PARAM_RAW);

        // Hook for plugins to extend form definition.
        $user = $this->_customdata;
        core_login_extend_set_password_form($mform, $user);

        $this->add_action_buttons(true);
    }
    //Custom validation should be added here
    function validation($password, $password_confirm) {
        $user = $this->_customdata;

        $errors = parent::validation($password, $password_confirm);

        // Extend validation for any form extensions from plugins.
        $errors = array_merge($errors, core_login_validate_extend_set_password_form($password, $password_confirm));

        // Ignore submitted username.
        if ($data['password'] !== $data['password2']) {
            $errors['password'] = get_string('passwordsdiffer', 'local_forget_password');
            $errors['password2'] = get_string('passwordsdiffer', 'local_forget_password');
            return $errors;
        }

        $errmsg = ''; // Prevents eclipse warnings.
        if (!check_password_policy($data['password'], $errmsg, $user)) {
            $errors['password'] = $errmsg;
            $errors['password2'] = $errmsg;
            return $errors;
        }

        if (user_is_previously_used_password($user->id, $data['password'])) {
            $errors['password'] = get_string('errorpasswordreused', 'local_forget_password');
            $errors['password2'] = get_string('errorpasswordreused', 'local_forget_password');
        }

        return $errors;
    }
}

