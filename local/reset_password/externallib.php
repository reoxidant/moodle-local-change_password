<?php

require_once($CFG->libdir.'/externallib.php');

class local_reset_password_external extends external_api{
    public static function get_new_password_by_user(){
        $params = self::validate_parameters(self::get_assignments_by_username_parameters(), array('username' => $username));
    }
    public static function get_new_password_by_user_returns(){
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'new_password' => new external_value(PARAM_INT, 'new_password'),
                )
            )
        );
    }
}