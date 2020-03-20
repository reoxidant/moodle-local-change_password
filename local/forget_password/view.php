<?php
require_once('../../config.php');

if (!isloggedin() OR isguestuser()) {
    require_login();
    die;
}

$context_sys = context_system::instance();
if(!has_capability('local/student:view', $context_sys)){
    redirect($CFG->wwwroot);
    die;
}
require_once("classes/forget_password_form.php");

$PAGE->set_url('/local/forget_password/view.php');

$PAGE->set_context($context_sys);
$PAGE->set_pagelayout('standard');
$title = get_string('pluginname', 'local_forget_password');
$PAGE->navbar->add($title);
$PAGE->set_heading($title);
$PAGE->set_title($title);
$PAGE->set_cacheable(false);
$PAGE->requires->css('/local/forget_password/styles.css');

$mform = new forget_password_form();

$forget_password = null; //error
if ($fromform = $mform->get_data())
    $forget_password = forget_password_form::validation($fromform->password, $fromform->password_confirm); // если что-то вернул, значит ошибка

//test
$event = \core\event\user_password_updated::create_from_user(array($user));
$this->assertEventContextNotUsed($event);
$this->assertEquals($user->id, $event->relateduserid);
$this->assertSame($context, $event->get_context());
$this->assertEventLegacyLogData(null, $event);
$this->assertFalse($event->other['forgottenreset']);
$event->trigger();

echo $OUTPUT->header();

if($forget_password != null)//error
    \core\notification::add(get_string('criticalerror', 'local_forget_password'));

echo html_writer::start_tag('div', array(
    'id' => 'forget_container'
)),

html_writer::start_tag('div', array(
    'class' => 'forget_info alert alert-info alert-block fade in'
)),

html_writer::start_tag('b'),
fullname($USER),
html_writer::end_tag('b'),

html_writer::end_tag('div'),

html_writer::start_tag('div', array(
    'class' => 'forget_form'
));

$mform->display();

echo html_writer::end_tag('div'),

html_writer::end_tag('div');

echo $OUTPUT->footer();

?>