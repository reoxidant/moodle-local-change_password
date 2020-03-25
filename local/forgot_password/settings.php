<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    require_once($CFG->dirroot . '/local/forgot_password/lib.php');

    $settings = new admin_settingpage('forgot_password', get_string('pluginname', 'forgot_password'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_forgot_password/ws_user';
    $title = get_string('ws_user', 'local_forgot_password');
    $setting = new admin_setting_configtext($name, $title, null, null);
    $settings->add($setting);

    $name = 'local_forgot_password/ws_pass';
    $title = get_string('ws_pass', 'local_forgot_password');
    $setting = new admin_setting_configpasswordunmask($name, $title, null, null);
    $settings->add($setting);

    $name = 'local_forgot_password/ws_timeout';
    $title = get_string('ws_timeout', 'local_forgot_password');
    $default = '1';
    $setting = new admin_setting_configtext($name, $title, null, $default, PARAM_INT);
    $settings->add($setting);
}
