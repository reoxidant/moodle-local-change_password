<?php
/// Define variables used in page
$site = get_site();

//set context login
$PAGE->set_context($context_sys);
$PAGE->set_pagelayout('login');

$PAGE->navbar->ignore_active();
$loginsite = get_string("loginsite", "local_forget_password");
$PAGE->navbar->add($loginsite);

//user context
$mform = new forget_password_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/login/index.php');
} else if ($data = $mform->get_data()) {
    $strpasswordchanged = get_string('emailpasswordconfirmmaybesent', 'local_forget_password');
    if(empty($token)){
        $user_field = $DB->get_record('user', array('username' => $data->username), 'email');
        core_login_user_password_reset($data->username, $user_field->email);
    }

    $PAGE->set_title("$site->fullname: $loginsite");
    $PAGE->set_heading("$site->fullname");
    $PAGE->requires->css('/local/forget_password/css/styles.css');
    echo $OUTPUT->header();

    notice($strpasswordchanged, new moodle_url('/login/index.php'));

    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
if (get_user_preferences('auth_forcepasswordchange')) {
    echo $OUTPUT->notification(get_string('forcepasswordchangenotice'));
}
$mform->display();
echo $OUTPUT->footer();
