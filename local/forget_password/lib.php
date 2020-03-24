<?php

require_once($CFG->libdir . '/moodlelib.php');

function my_check_password_policy($password, &$errmsg, $data = null)
{
    global $CFG;
    $CFG->maxpasswordlength = 30;

    if (!empty($CFG->passwordpolicy)) {
        $errmsg = '';

        //не менее 8 символов
        if (core_text::strlen($password) < $CFG->minpasswordlength) {
            $errmsg .= '<div>' . get_string('errorminpasswordlength', 'local_forget_password', $CFG->minpasswordlength) . '</div>';
        }
        //не больше 30 символов
        if (core_text::strlen($password) >= $CFG->maxpasswordlength) {
            $errmsg .= '<div>' . get_string('errormaxpasswordlength', 'local_forget_password', $CFG->maxpasswordlength) . '</div>';
        }
        //использовать имя своей учетной записи в пароле, не более чем два символа подряд из username или Firstname
        if (substr($password, 0, 3) == substr(is_object($data) ? $data->username : $data['username'], 0, 3) || substr($password, 0, 3) == mb_substr(is_object($data) ? $data->firstname : $data['firstName'], 0, 3)) {
            $errmsg .= '<div>' . get_string('errormatchpasswordandusername', 'local_forget_password', $CFG->minpasswordlength) . '</div>';
        }
        //Проверка на использование верних и нижних регистров
        if (preg_match_all('/[[:digit:]]/u', $password, $matches) < $CFG->minpassworddigits) {
            $errmsg .= '<div>' . get_string('errorminpassworddigits', 'local_forget_password', $CFG->minpassworddigits) . '</div>';
        }
        if (preg_match_all('/[[:lower:]]/u', $password, $matches) < $CFG->minpasswordlower) {
            $errmsg .= '<div>' . get_string('errorminpasswordlower', 'local_forget_password', $CFG->minpasswordlower) . '</div>';
        }
        if (preg_match_all('/[[:upper:]]/u', $password, $matches) < $CFG->minpasswordupper) {
            $errmsg .= '<div>' . get_string('errorminpasswordupper', 'local_forget_password', $CFG->minpasswordupper) . '</div>';
        }
        //В пароле не должны быть только буквы, а так-же не буквенные символы.
        if (preg_match_all('/[^[:upper:][:lower:][:digit:]]/u', $password, $matches) < $CFG->minpasswordnonalphanum) {
            $errmsg .= '<div>' . get_string('errorminpasswordnonalphanum', 'local_forget_password', $CFG->minpasswordnonalphanum) . '</div>';
        }
        //проверка на одинаковые символы
        if (!check_consecutive_identical_characters($password, $CFG->maxconsecutiveidentchars)) {
            $errmsg .= '<div>' . get_string('errormaxconsecutiveidentchars', 'local_forget_password', $CFG->maxconsecutiveidentchars) . '</div>';
        }
    }

    if ($errmsg == '') {
        return true;
    } else {
        return false;
    }
}

function core_login_user_password_reset($username, $email)
{
    global $CFG, $DB;

    if (empty($username) && empty($email)) {
        print_error('cannotmailconfirm');
    }

    // Next find the user account in the database which the requesting user claims to own.
    if (!empty($username)) {
        // Username has been specified - load the user record based on that.
        $username = core_text::strtolower($username); // Mimic the login page process.
        $userparams = array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0, 'suspended' => 0);
        $user = $DB->get_record('user', $userparams);
    } else {
        // Try to load the user record based on email address.
        // this is tricky because
        // 1/ the email is not guaranteed to be unique - TODO: send email with all usernames to select the account for pw reset
        // 2/ mailbox may be case sensitive, the email domain is case insensitive - let's pretend it is all case-insensitive.

        $select = $DB->sql_like('email', ':email', false, true, false, '|') .
            " AND mnethostid = :mnethostid AND deleted=0 AND suspended=0";
        $params = array('email' => $DB->sql_like_escape($email, '|'), 'mnethostid' => $CFG->mnet_localhost_id);
        $user = $DB->get_record_select('user', $select, $params, '*', IGNORE_MULTIPLE);
    }

    // Target user details have now been identified, or we know that there is no such account.
    // Send email address to account's email address if appropriate.
    $pwresetstatus = PWRESET_STATUS_NOEMAILSENT;
    if ($user and !empty($user->confirmed)) {
        $systemcontext = context_system::instance();

        $userauth = get_auth_plugin($user->auth);
        if (!$userauth->can_reset_password() or !is_enabled_auth($user->auth)
            or !has_capability('moodle/user:changeownpassword', $systemcontext, $user->id)) {
            if (send_password_change_info($user)) {
                $pwresetstatus = PWRESET_STATUS_OTHEREMAILSENT;
            } else {
                print_error('cannotmailconfirm');
            }
        } else {
            // The account the requesting user claims to be is entitled to change their password.
            // Next, check if they have an existing password reset in progress.
            $resetinprogress = $DB->get_record('user_password_resets', array('userid' => $user->id));
            if (empty($resetinprogress)) {
                // Completely new reset request - common case.
                $resetrecord = core_login_generate_password_reset($user);
                $sendemail = true;
            } else if ($resetinprogress->timerequested < (time() - $CFG->pwresettime)) {
                // Preexisting, but expired request - delete old record & create new one.
                // Uncommon case - expired requests are cleaned up by cron.
                $DB->delete_records('user_password_resets', array('id' => $resetinprogress->id));
                $resetrecord = core_login_generate_password_reset($user);
                $sendemail = true;
            } else if (empty($resetinprogress->timererequested)) {
                // Preexisting, valid request. This is the first time user has re-requested the reset.
                // Re-sending the same email once can actually help in certain circumstances
                // eg by reducing the delay caused by greylisting.
                $resetinprogress->timererequested = time();
                $DB->update_record('user_password_resets', $resetinprogress);
                $resetrecord = $resetinprogress;
                $sendemail = true;
            } else {
                // Preexisting, valid request. User has already re-requested email.
                $pwresetstatus = PWRESET_STATUS_ALREADYSENT;
                $sendemail = false;
            }

            if ($sendemail) {
                $sendresult = send_password_to_user_email($user, $resetrecord);
                if ($sendresult) {
                    $pwresetstatus = PWRESET_STATUS_TOKENSENT;
                } else {
                    print_error('cannotmailconfirm');
                }
            }
        }
    }

    $url = $CFG->wwwroot . '/index.php';
    if (!empty($CFG->protectusernames)) {
        // Neither confirm, nor deny existance of any username or email address in database.
        // Print general (non-commital) message.
        $status = 'emailpasswordconfirmmaybesent';
        $notice = get_string($status);
    } else if (empty($user)) {
        // Protect usernames is off, and we couldn't find the user with details specified.
        // Print failure advice.
        $status = 'emailpasswordconfirmnotsent';
        $notice = get_string($status);
        $url = $CFG->wwwroot . '/forgot_password.php';
    } else if (empty($user->email)) {
        // User doesn't have an email set - can't send a password change confimation email.
        $status = 'emailpasswordconfirmnoemail';
        $notice = get_string($status);
    } else if ($pwresetstatus == PWRESET_STATUS_ALREADYSENT) {
        // User found, protectusernames is off, but user has already (re) requested a reset.
        // Don't send a 3rd reset email.
        $status = 'emailalreadysent';
        $notice = get_string($status);
    } else if ($pwresetstatus == PWRESET_STATUS_NOEMAILSENT) {
        // User found, protectusernames is off, but user is not confirmed.
        // Pretend we sent them an email.
        // This is a big usability problem - need to tell users why we didn't send them an email.
        // Obfuscate email address to protect privacy.
        $protectedemail = preg_replace('/([^@]*)@(.*)/', '******@$2', $user->email);
        $status = 'emailpasswordconfirmsent';
        $notice = get_string($status, '', $protectedemail);
    } else {
        // Confirm email sent. (Obfuscate email address to protect privacy).
        $protectedemail = preg_replace('/([^@]*)@(.*)/', '******@$2', $user->email);
        // This is a small usability problem - may be obfuscating the email address which the user has just supplied.
        $status = 'emailresetconfirmsent';
        $notice = get_string($status, '', $protectedemail);
    }
    return array($status, $notice, $url);
}

function core_login_token_update_password($token)
{
    global $DB, $CFG, $OUTPUT, $PAGE, $SESSION;
    require_once($CFG->dirroot . '/user/lib.php');
    $pwresettime = isset($CFG->pwresettime) ? $CFG->pwresettime : 1800;
    $sql = "SELECT u.*, upr.token, upr.timerequested, upr.id as tokenid
              FROM {user} u
              JOIN {user_password_resets} upr ON upr.userid = u.id
             WHERE upr.token = ?";

    $user = $DB->get_record_sql($sql, array($token));

    $forgotpasswordurl = "{$CFG->wwwroot}/login/forgot_password.php";
    if (empty($user) or ($user->timerequested < (time() - $pwresettime - DAYSECS))) {
        // There is no valid reset request record - not even a recently expired one.
        // (suspicious)
        // Direct the user to the forgot password page to request a password reset.
        echo $OUTPUT->header();
        notice(get_string('noresetrecord'), $forgotpasswordurl);
        die; // Never reached.
    }
    if ($user->timerequested < (time() - $pwresettime)) {
        // There is a reset record, but it's expired.
        // Direct the user to the forgot password page to request a password reset.
        $pwresetmins = floor($pwresettime / MINSECS);
        echo $OUTPUT->header();
        notice(get_string('resetrecordexpired', '', $pwresetmins), $forgotpasswordurl);
        die; // Never reached.
    }

    if ($user->auth === 'nologin' or !is_enabled_auth($user->auth)) {
        // Bad luck - user is not able to login, do not let them set password.
        echo $OUTPUT->header();
        print_error('forgotteninvalidurl');
        die; // Never reached.
    }

    // Check this isn't guest user.
    if (isguestuser($user)) {
        print_error('cannotresetguestpwd');
    }

    require_once('view_token_success.php');
}

function send_password_to_user_email($user, $resetrecord)
{
    global $CFG, $DB;
    $site = get_site();
    $supportuser = core_user::get_support_user();
    $pwresetmins = isset($CFG->pwresettime) ? floor($CFG->pwresettime / MINSECS) : 30;

    if ($email_user = $DB->get_record('user_info_data', array('userid' => $user->id), 'data')) {
        $user->email = $email_user->data;
    }

    $data = new stdClass();
    $data->firstname = $user->firstname;
    $data->lastname = $user->lastname;
    $data->username = $user->username;
    $data->sitename = format_string($site->fullname);
    $data->link = $CFG->wwwroot . '/local/forget_password/view.php?token=' . $resetrecord->token;

    $data->admin = generate_email_signoff();
    $data->resetminutes = $pwresetmins;

    $message = get_string('emailresetconfirmation', '', $data);
    $subject = get_string('emailresetconfirmationsubject', '', format_string($site->fullname));

    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user($user, $supportuser, $subject, $message);
}