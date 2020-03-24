<?php
require_once('../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/webservice/lib.php');
require_once($CFG->dirroot . '/local/forget_password/lib.php');
require_once($CFG->dirroot . '/local/forget_password/classes/set_new_password_form.php');

// Token is correct, and unexpired.
$mform = new set_new_password_form(null, $user);
$data = $mform->get_data();
if (empty($data)) {
    // User hasn't submitted form, they got here directly from email link.
    // Next, display the form.
    $setdata = new stdClass();
    $setdata->username = $user->username;
    $setdata->username2 = $user->username;
    $setdata->token = $user->token;
    $mform->set_data($setdata);
    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('setpasswordinstructions'), 'generalbox boxwidthnormal boxaligncenter');
    $mform->display();
    echo $OUTPUT->footer();
    return;
} else {
    // User has submitted form.
    // Delete this token so it can't be used again.
    $DB->delete_records('user_password_resets', array('id' => $user->tokenid));
    $userauth = get_auth_plugin($user->auth);
    var_dump($data);
    if (!$userauth->user_update_password($user, $data->newpassword_log1)) {
        print_error('errorpasswordupdate', 'auth');
    }
    user_add_password_history($user->id, $data->newpassword_log1);
    if (!empty($CFG->passwordchangelogout)) {
        \core\session\manager::kill_user_sessions($user->id, session_id());
    }
    // Reset login lockout (if present) before a new password is set.
    login_unlock_account($user);
    // Clear any requirement to change passwords.
    unset_user_preference('auth_forcepasswordchange', $user);
    unset_user_preference('create_password', $user);

    if (!empty($user->lang)) {
        // Unset previous session language - use user preference instead.
        unset($SESSION->lang);
    }

    $urltogo = core_login_get_return_url();
    unset($SESSION->wantsurl);
    // Plugins can perform post set password actions once data has been validated.
//    core_login_post_set_password_requests($data, $user);

    redirect($urltogo, get_string('passwordset'), 1);
}