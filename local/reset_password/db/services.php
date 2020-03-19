<?php

$functions = array(
    'local_reset_password_get_new_password_by_user' => array(
        'classname'     => 'local_password_reset_external',
        'methodname'    => 'get_new_password_by_user',
        'classpath'     => 'local/leapwebservices/externallib.php',
        'description'   => 'Set new password.',
        'type'          => 'read',
        'capabilities'  => 'moodle/user:viewalldetails',
    ),
);

$services = array(
    'Leap' => array(
        'functions' => array (
            'local_reset_password',
        ),
        'restrictedusers'   => 1,
        'enabled'           => 1,
    )
);
