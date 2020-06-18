<?php

$string['pluginname'] = "Password Recovery Web Service";
$string['username'] = 'Username';
$string['criticalerror'] = 'Critical Error! Wrong password.';
$string['passwordsdiffer'] = 'These passwords do not match';
$string['newpassword'] = 'New password';
$string['required'] = 'Required';
$string['oldpassword'] = 'Current password';
$string['again'] = 'again';
$string['invalidloginoremail'] = 'Invalid login or Email address, please try again';
$string['errorpasswordreused'] = 'This password has been used before, and is not permitted to be reused';
$string['loginsite'] = 'Log in to the site';
$string['errormatchpasswordandusername'] = 'You can\'t use your account name in your password';
$string['errorminpassworddigits'] = 'Passwords must have at least {$a} digit(s).';
$string['email'] = 'E-mail';
$string['usernameisnotundefined'] = 'Username is not undefined';
$string['errormatchpasswordandusername'] = 'You can\'t use your account name in your password';
$string['errorminpasswordlength'] = 'Passwords must be at least {$a} characters long.';
$string['errormaxpasswordlength'] = 'Password must be contain no more than {$a} characters long';
$string['errorminpasswordlower'] = 'Passwords must have at least {$a} lower case letter(s).';
$string['errorminpasswordupper'] = 'Passwords must have at least {$a} upper case letter(s).';
$string['errorminpasswordnonalphanum'] = 'Passwords must have at least {$a} non-alphanumeric character(s) such as as *, -, or #.';
$string['errormaxconsecutiveidentchars'] = 'Passwords must have at most {$a} consecutive identical characters.';
$string['emailpasswordconfirmmaybesent'] = '<p>If you supplied a correct username or email address then an email should have been sent to you.</p>
   <p>It contains easy instructions to confirm and complete this password change.
If you continue to have difficulty, please contact the site administrator.</p>';
$string['invalidpassword'] = 'Invalid password, please try again';
$string['mustchangepassword'] = 'The new password must be different than the current one';
$string['informminpasswordreuselimit'] = 'Passwords can be reused after {$a} changes';
$string['noresetrecord'] = 'There is no record of that reset request. Please initiate a new password reset request.';
$string['resetrecordexpired'] = 'The password reset link you used is more than {$a} minutes old and has expired. Please initiate a new password reset.';
$string['forgotteninvalidurl'] = 'Invalid password reset URL';
$string['cannotresetguestpwd'] = 'You cannot reset the guest password';
$string['emailresetconfirmation'] = 'Hi {$a->firstname},

A password reset was requested for your account \'{$a->username}\' at {$a->sitename}.

To confirm this request, and set a new password for your account, please
go to the following web address:

{$a->link}
(This link is valid for {$a->resetminutes} minutes from the time this reset was first requested)

If this password reset was not requested by you, no action is needed.

If you need help, please contact the site administrator,
{$a->admin}';
$string['emailresetconfirmationsubject'] = '{$a}: Password reset request';
$string['emailresetconfirmsent'] = 'An email has been sent to your address at <b>{$a}</b>.
<br />It contains easy instructions to confirm and complete this password change.
If you continue to have difficulty, contact the site administrator.';
$string['passwordforgotteninstructions2'] = 'To reset your password, submit your username or your email address below. If we can find you in the database, an email will be sent to your email address, with instructions how to get access again.';
$string['passwordforgotten'] = 'Forgotten password';
$string['login'] = 'Log in to your account';
$string['participants'] = 'Participants';
$string['usercannotchangepassword'] = 'You cannot change your password here since you are a remote user.';
$string['passwordchanged'] = 'Password has been changed';
$string['changepassword'] = 'Change password';
$string['forcepasswordchangenotice'] = 'You must change your password to proceed.';
$string['setpasswordinstructions'] = 'Please enter your new password below, then save changes.';
$string['passwordset'] = 'Your password has been set.';
$string['userchangepasswordlink'] = '<br /> You may be able to change your password at your <a href="{$a->wwwroot}/login/change_password.php">{$a->description}</a> provider.';
$string['change_password:change'] = 'Can to change password LDAP';
$string['ws_user'] = 'User connecting to 1C';
$string['ws_pass'] = 'Password for connecting to 1C';
$string['ws_timeout'] = 'Connection timeout to 1C (in seconds)';
$string['maxpasswordlength'] = 'Maximum number of characters in the password';
$string['minpasswordlength'] = 'Minimum number of characters in the password';
$string['minpasswordnonalphanum'] = 'Non-alphanumeric characters';
$string['maxconsecutiveidentchars'] = 'Consecutive identical characters';
$string['minpasswordupper'] = 'Uppercase letters';
$string['minpasswordlower'] = 'Lowercase letters';
$string['minpassworddigits'] = 'Digits';
$string['password_access_exception'] = 'You can\'t to change password LDAP';
