<?php

require_once($CFG->libdir.'/moodlelib.php');

function my_check_password_policy($password, &$errmsg, $data = null) {
    global $CFG;

    if (!empty($CFG->passwordpolicy)) {
        $errmsg = '';

        //не менее 8 символов
        if (core_text::strlen($password) < $CFG->minpasswordlength) {
            $errmsg .= '<div>'. get_string('errorminpasswordlength', 'local_forget_password', $CFG->minpasswordlength) .'</div>';
        }
        //использовать имя своей учетной записи в пароле, не более чем два символа подряд из username или Firstname
        if (substr($password, 0, 3) == substr($data['username'], 0, 3) || substr($password, 0, 3) == mb_substr($data['firstName'], 0, 3)) {
            $errmsg .= '<div>'. get_string('errormatchpasswordandusername', 'local_forget_password', $CFG->minpasswordlength) .'</div>';
        }
        //Проверка на использование верних и нижних регистров
        if (preg_match_all('/[[:digit:]]/u', $password, $matches) < $CFG->minpassworddigits) {
            $errmsg .= '<div>'. get_string('errorminpassworddigits', 'local_forget_password', $CFG->minpassworddigits) .'</div>';
        }
        if (preg_match_all('/[[:lower:]]/u', $password, $matches) < $CFG->minpasswordlower) {
            $errmsg .= '<div>'. get_string('errorminpasswordlower', 'local_forget_password', $CFG->minpasswordlower) .'</div>';
        }
        if (preg_match_all('/[[:upper:]]/u', $password, $matches) < $CFG->minpasswordupper) {
            $errmsg .= '<div>'. get_string('errorminpasswordupper', 'local_forget_password', $CFG->minpasswordupper) .'</div>';
        }
        //В пароле не должны быть только буквы, а так-же не буквенные символы.
        if (preg_match_all('/[^[:upper:][:lower:][:digit:]]/u', $password, $matches) < $CFG->minpasswordnonalphanum) {
            $errmsg .= '<div>'. get_string('errorminpasswordnonalphanum', 'local_forget_password', $CFG->minpasswordnonalphanum) .'</div>';
        }
        //проверка на одинаковые символы
        if (!check_consecutive_identical_characters($password, $CFG->maxconsecutiveidentchars)) {
            $errmsg .= '<div>'. get_string('errormaxconsecutiveidentchars', 'local_forget_password', $CFG->maxconsecutiveidentchars) .'</div>';
        }
    }

    if ($errmsg == '') {
        return true;
    } else {
        return false;
    }
}