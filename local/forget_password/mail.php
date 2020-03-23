<?php

if ($CFG->supportemail and empty($CFG->noemailever)) {
    // Function email_to_user is not usable because email_to_user tries to write to the logs table,
    // and this will get caught in an infinite loop, if disk is full.
    $site = get_site();
    $subject = 'Insert into log failed at your moodle site ' . $site->fullname;
    $message = "Insert into log table failed at " . date('l dS \of F Y h:i:s A') .
        ".\n It is possible that your disk is full.\n\n";
    $message .= "The failed query parameters are:\n\n" . var_export($log, true);

    $lasttime = get_config('admin', 'lastloginserterrormail');
    if (empty($lasttime) || time() - $lasttime > 60 * 60 * 24) { // Limit to 1 email per day.
        // Using email directly rather than messaging as they may not be able to log in to access a message.
        mail($CFG->supportemail, $subject, $message);
        set_config('lastloginserterrormail', time(), 'admin');
    }
}