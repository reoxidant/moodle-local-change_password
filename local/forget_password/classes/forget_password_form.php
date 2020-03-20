<?php

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/login/lib.php');
require_once($CFG->dirroot . '/local/forget_password/lib.php');

class forget_password_form extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $USER, $CFG;
        $mform = $this->_form;

        $mform->setDisableShortforms(true);
        $mform->addElement('header', 'forgetpassword', get_string('pluginname', 'local_forget_password'), '');

        // Include the username in the form so browsers will recognise that a password is being set.
        if (isloggedin() OR isguestuser()) {
            $mform->addElement('static', 'username', get_string('username', 'local_forget_password'), $USER->username);

            $purpose = user_edit_map_field_purpose($USER->id, 'password');
            $mform->addElement('password', 'password', get_string('oldpassword', 'local_forget_password'), $purpose);

            $mform->addRule('password', get_string('required', 'local_forget_password'), 'required', null, 'client');
            $mform->setType('password', PARAM_RAW);

            $mform->addElement('password', 'newpassword1', get_string('newpassword', 'local_forget_password'));
            $mform->addRule('newpassword1', get_string('required', 'local_forget_password'), 'required', null, 'client');
            $mform->setType('newpassword1', PARAM_RAW);

            $mform->addElement('password', 'newpassword2', get_string('newpassword', 'local_forget_password') . ' (' . get_String('again', 'local_forget_password') . ')');
            $mform->addRule('newpassword2', get_string('required', 'local_forget_password'), 'required', null, 'client');
            $mform->setType('newpassword2', PARAM_RAW);

            // hidden optional params
            $mform->addElement('hidden', 'id', 0);
            $mform->setType('id', PARAM_INT);

            // Hook for plugins to extend form definition.
            core_login_extend_set_password_form($mform, $USER);
        } else {
            $mform->addElement('text', 'email', get_string('email', 'local_forget_password'));
            $mform->setType('email', PARAM_RAW);
            $mform->addRule('email', get_string('required', 'local_forget_password'), 'required', null, 'client');

            $mform->addElement('password', 'newpassword_log1', get_string('newpassword', 'local_forget_password'));
            $mform->addRule('newpassword_log1', get_string('required', 'local_forget_password'), 'required', null, 'client');
            $mform->setType('newpassword_log1', PARAM_RAW);

            $mform->addElement('password', 'newpassword_log2', get_string('newpassword', 'local_forget_password') . ' (' . get_String('again', 'local_forget_password') . ')');
            $mform->addRule('newpassword_log2', get_string('required', 'local_forget_password'), 'required', null, 'client');
            $mform->setType('newpassword_log2', PARAM_RAW);
        }

        // buttons
        if (get_user_preferences('auth_forcepasswordchange')) {
            $this->add_action_buttons(false);
        } else {
            $this->add_action_buttons(true);
        }
    }

    /// perform extra password change validation
    function validation($data, $files)
    {
        GLOBAL $USER, $DB;
        $errors = parent::validation($data, $files);
        $reason = null;

        // Extend validation for any form extensions from plugins.
        if (isloggedin() OR isguestuser()) {
            $errors = array_merge($errors, core_login_validate_extend_set_password_form($data, $USER));

            // ignore submitted username
            if (!$user = authenticate_user_login($USER->username, $data['password'], true, $reason, false)) {
                $errors['password'] = get_string('invalidlogin', 'local_forget_password');
                return $errors;
            }

            // Ignore submitted username.
            if ($data['password'] <> $data['password2']) {
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

        } else {
            // ignore submitted username
/*            if (!$user = authenticate_user_login($data['username'], $data['newpassword_log1'], true, $reason, false)) {
                $errors['username'] = get_string('invalidloginoremail', 'local_forget_password');
                return $errors;
            }*/
            //TODO Надо вынуть из бд поле email и имя пользователя и закинуть в $data['username']
            if($findebt_fieldid = $DB->get_record('user_info_field', array('shortname' => 'hasfindebt'), 'id')){
                var_dump($findebt_fieldid);
                die();
                if($data['fields'] = $DB->get_record('user_info_data', array('fieldid' => $findebt_fieldid->id, 'userid' => $userid), 'data')){
                    if(!isset($data->data))
                        return false;
                }
            }

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

            if (user_is_previously_used_password($data->id, $data['newpassword_log1'])) {
                $errors['newpassword_log1'] = get_string('errorpasswordreused', 'local_forget_password');
                $errors['newpassword_log2'] = get_string('errorpasswordreused', 'local_forget_password');
            }
        }
        return $errors;
    }
}

