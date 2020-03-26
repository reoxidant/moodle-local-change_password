<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/change_password/classes/set_new_password_form.php');
require_once($CFG->dirroot . '/local/change_password/classes/change_password_form.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/local/change_password/lib.php');

$id = optional_param('id', SITEID, PARAM_INT); // current course
$token = optional_param('token', false, PARAM_ALPHANUM);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

//system context
$context_sys = context_system::instance();

if(!has_capability('local/change_password:change', $context_sys)){
    print_error('password_access_exception');
}

$PAGE->set_url('/local/change_password/view.php', array('id' => $id));
$PAGE->set_context($context_sys);
$PAGE->set_heading($COURSE->fullname);

$strparticipants = get_string('participants', 'local_change_password');

// Fetch the token from the session, if present, and unset the session var immediately.
$tokeninsession = false;
if (!empty($SESSION->password_reset_token)) {
    $token = $SESSION->password_reset_token;
    unset($SESSION->password_reset_token);
    $tokeninsession = true;
}

if (!empty($token)) {
    if (!$tokeninsession && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $SESSION->password_reset_token = $token;
        redirect($CFG->wwwroot . '/local/change_password/view.php');
    } else {
        core_login_token_update_password($token);
    }
}

if (!isloggedin() OR isguestuser()) {
    require_once('view_login.php');
} else {
    //user context
    $PAGE->set_context(context_user::instance($USER->id));
    $PAGE->set_pagelayout('admin');
    $PAGE->set_course($course);
    // do not require change own password cap if change forced
    if (!get_user_preferences('auth_forcepasswordchange', false)) {
        require_capability('moodle/user:changeownpassword', $context_sys);
    }

    // do not allow "Logged in as" users to change any passwords
    if (\core\session\manager::is_loggedinas()) {
        print_error('cannotcallscript');
    }

    //Don't Allow User MNet
    if (is_mnet_remote_user($USER)) {
        $message = get_string('usercannotchangepassword', 'local_change_password');
        if ($idprovider = $DB->get_record('mnet_host', array('id' => $USER->mnethostid))) {
            $message .= get_string('userchangepasswordlink', 'local_change_password', $idprovider);
        }
        print_error('userchangepasswordlink', 'mnet', '', $message);
    }

    // load the appropriate auth plugin
    $userauth = get_auth_plugin($USER->auth);

    //check user allows
    if (!$userauth->can_change_password()) {
        print_error('nopasswordchange', 'auth');
    }

    if ($changeurl = $userauth->change_password_url()) {
        // this internal scrip not used
        redirect($changeurl);
    }

    $mform = new change_password_form();
    $mform->set_data(array('id' => $course->id));

    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot . '/user/preferences.php?userid=' . $USER->id . '&amp;course=' . $course->id);
    } else if ($data = $mform->get_data()) {

        if (!$userauth->user_update_password($USER, $data->newpassword1)) {
            print_error('errorpasswordupdate', 'auth');
        }

        user_add_password_history($USER->id, $data->newpassword1);

        if (!empty($CFG->passwordchangelogout)) {
            \core\session\manager::kill_user_sessions($USER->id, session_id());
        }

        if (!empty($data->signoutofotherservices)) {
            webservice::delete_user_ws_tokens($USER->id);
        }

        // Reset login lockout - we want to prevent any accidental confusion here.
        login_unlock_account($USER);

        // register success changing password
        unset_user_preference('auth_forcepasswordchange', $USER);
        unset_user_preference('create_password', $USER);

        $strpasswordchanged = get_string('passwordchanged', 'local_change_password');

        // Plugins can perform post password change actions once data has been validated.
        core_login_post_change_password_requests($data);

        $fullname = fullname($USER, true);

        $PAGE->set_title($strpasswordchanged);
        $PAGE->set_heading(fullname($USER));
        echo $OUTPUT->header();

        notice($strpasswordchanged, new moodle_url($PAGE->url, array('return' => 1)));

        echo $OUTPUT->footer();
        exit;
    }

    $strchangepassword = get_string('changepassword', 'local_change_password');

    $fullname = fullname($USER, true);

    $PAGE->set_title($strchangepassword);
    $PAGE->set_heading($fullname);
    echo $OUTPUT->header();

    if (get_user_preferences('auth_forcepasswordchange')) {
        echo $OUTPUT->notification(get_string('forcepasswordchangenotice', 'local_change_password'));
    }
    $mform->display();
    echo $OUTPUT->footer();
}
?>