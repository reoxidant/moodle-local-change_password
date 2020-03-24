<?php
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

// setup text strings
$strforgotten = get_string('passwordforgotten');
$strlogin = get_string('login');

$PAGE->navbar->add($strlogin, get_login_url());
$PAGE->navbar->add($strforgotten);
$PAGE->set_title($strforgotten);
$PAGE->set_heading($COURSE->fullname);

//user context
$mform = new forget_password_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/login/index.php');
} else if ($data = $mform->get_data()) {
    $strpasswordchanged = get_string('emailpasswordconfirmmaybesent', 'local_forget_password');
    if (empty($token)) {
        $user_field = $DB->get_record('user', array('username' => $data->username), 'email');
        core_login_user_password_reset($data->username, $user_field->email);
    }

    $PAGE->set_title($strforgotten);
    $PAGE->set_heading($COURSE->fullname);
    $PAGE->requires->css('/local/forget_password/css/styles.css');
    echo $OUTPUT->header();

    notice($strpasswordchanged, new moodle_url('/login/index.php'));

    echo $OUTPUT->footer();
    exit;
}

//echo $OUTPUT->header();
//echo $OUTPUT->box(get_string('passwordforgotteninstructions2'), 'generalbox boxwidthnormal boxaligncenter');
//$mform->display();
//echo $OUTPUT->footer();
