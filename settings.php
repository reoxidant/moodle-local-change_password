<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    require_once($CFG->dirroot . '/local/change_password/lib.php');

    $settings = new admin_settingpage('change_password', get_string('pluginname', 'local_change_password'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_change_password/ws_user';
    $title = get_string('ws_user', 'local_change_password');
    $setting = new admin_setting_configtext($name, $title, null, null);
    $settings->add($setting);

    $name = 'local_change_password/ws_pass';
    $title = get_string('ws_pass', 'local_change_password');
    $setting = new admin_setting_configpasswordunmask($name, $title, null, null);
    $settings->add($setting);

    $name = 'local_change_password/ws_timeout';
    $title = get_string('ws_timeout', 'local_change_password');
    $default = '1';
    $setting = new admin_setting_configtext($name, $title, null, $default, PARAM_INT);
    $settings->add($setting);

    $name = 'local_change_password/minpasswordlength';
    $title = get_string('minpasswordlength', 'local_change_password');
    $default = '8';
    $setting = new admin_setting_configtext($name, $title, null, $default, PARAM_INT);
    $settings->add($setting);

    $name = 'local_change_password/maxpasswordlength';
    $title = get_string('maxpasswordlength', 'local_change_password');
    $default = '30';
    $setting = new admin_setting_configtext($name, $title, null, $default, PARAM_INT);
    $settings->add($setting);

    $name = 'local_change_password/minpasswordnonalphanum';
    $title = get_string('minpasswordnonalphanum', 'local_change_password');
    $default = '1';
    $setting = new admin_setting_configtext($name, $title, null, $default, PARAM_INT);
    $settings->add($setting);

    $name = 'local_change_password/maxconsecutiveidentchars';
    $title = get_string('maxconsecutiveidentchars', 'local_change_password');
    $default = '0';
    $setting = new admin_setting_configtext($name, $title, null, $default, PARAM_INT);
    $settings->add($setting);


}
