<?php

    if (!defined('INIT_INSTANCE')) die('Access restricted');

    // User is authorized
    if ( !empty($_SESS['user']))
    {
        $member_db = user_search($_SESS['user']);
        if ($member_db[UDB_ACL] == ACL_LEVEL_ADMIN) $captcha_enabled = false;
    }

    // Get only POST or COOKIE vars
    $id         = intval($id);

    $name       = isset($_POST['name']) ? $_POST['name'] : '';
    $name       = ($name == '' && isset($_COOKIE['CNname']) && $_COOKIE['CNname'] ) ? $_COOKIE['CNname'] : $name;
    $mail       = isset($_POST['mail']) ? trim($_POST['mail']) : '';

    $captcha    = isset($_POST['captcha']) ? $_POST['captcha'] : '';

    // Logged user
    if ($member_db[UDB_NAME])  $name = $member_db[UDB_NAME];
    if ($member_db[UDB_EMAIL]) $mail = $member_db[UDB_EMAIL];

    //----------------------------------
    // Check the lenght of comment, include name + mail
    //----------------------------------
    if( strlen($name) > 50 )
    {
        echo getpart('align_center', array( lang('Your name is too long!') ));
        return FALSE;
    }
    elseif( strlen($mail) > 50)
    {
        echo getpart('align_center', array( lang('Your e-mail is too long!') ));
        return FALSE;
    }
    elseif ( strlen($comments) > $config_comment_max_long and $config_comment_max_long != "" and $config_comment_max_long != "0")
    {
        echo getpart('align_center', array( lang('Your comment is too long!') ));
        return FALSE;
    }

    // Check URL in comment
    $pattern = "/[.]+(aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs".
        "|mobi|mil|museum|name|net|org|pro|root|tel|travel|ac".
        "|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az".
        "|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bw|by".
        "|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx".
        "|cy|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj".
        "|fk|fm|fo|fr|ga|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr".
        "|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|htm|html|php|il|im|in|io|iq".
        "|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kr|kw|ky|kz|la".
        "|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mk|ml|mm".
        "|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|asp|cgi".
        "|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|pg|ph|pk".
        "|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd".
        "|se|sg|sh|si|sk|sl|sm|sn|sr|st|sv|sy|sz|tc|td|tf|tg|th".
        "|tj|tk|tl|tm|tn|to|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va".
        "|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)/i";

    if ( preg_match($pattern, $comments) && (preg_match("/www./i", $comments) || preg_match("/http/i", $comments)) )
    {
        echo getpart('align_center', array( lang("Your not allowed to put URL's in the comments field.") ));;
        return FALSE;
    }

    //----------------------------------
    // Check if IP is blocked or wrong
    //----------------------------------
    $is_ban = (user_getban($ip) or user_getban($name)) ? true : false;

    // user really banned
    if ($is_ban)
    {
        echo '<div class="blocking_posting_comment">'.lang('Sorry but you have been blocked from posting comments').'</div>';
        return FALSE;
    }

    //----------------------------------
    // Flood Protection
    //----------------------------------
    if ( $config_flood_time != 0 and $config_flood_time != "" )
    {
        if (flooder($ip, $id) == true)
        {
            echo '<div class="blocking_posting_comment">'.str_replace('%1', $config_flood_time, lang('Flood protection activated! You have to wait %1 seconds after your last comment before posting again at this article')).'</div>';
            return FALSE;
        }
    }

    //----------------------------------
    // Check if the name is protected
    //----------------------------------
    $user_member = user_search($name);

    // In case if enter another name
    if ($CNname && $CNpass && $CNname != $name or $name && $_SESS['user'] && $_SESS['user'] != $name)
    {
        echo proc_tpl('remember');
        echo getpart('forget_me_script');

        $refer = $_SERVER['HTTP_REFERER'];
        echo proc_tpl('wrong_user');
        return FALSE;
    }

    if ( $name && empty($user_member) == false )
    {
        $is_member = true;

        // Check stored password in cookies
        if ($CNpass and $user_member[UDB_PASS] == $CNpass) $password = true;

        if (!empty($_SESS['user']) && $_SESS['user'] == $name)
        {
            $is_member = true;
        }
        elseif (empty($password))
        {
            $comments   = preg_replace( array("'\"'", "'\''", "''"), array("&quot;", "&#039;", ""), $comments);
            $name       = replace_comment("add", preg_replace("/\n/", "", $name));
            $mail       = replace_comment("add", preg_replace("/\n/", "", $mail));
            $remcheck   = ($CNremember == '1')? ' checked="checked" ' : '';
            echo proc_tpl('enter_passcode');
            return FALSE;
        }
        else
        {
            $gen = hash_generate($password);

            // password ok?
            if (in_array($user_member[UDB_PASS], $gen) || ($CNpass && $user_member[UDB_PASS] == $CNpass))
            {
                // if check remember password -> echo this script
                if (empty($CNrememberPass) == false)
                {
                    $name = htmlspecialchars($name);
                    if (empty($mail)) $mail = htmlspecialchars($user_member[UDB_EMAIL]);
                    echo read_tpl('remember').'<script type="text/javascript">CNRememberPass("'.$user_member[UDB_PASS].'", "'.$name.'", "'.$mail.'")</script>';
                }

                // hide email
                $mail = $user_member[UDB_CBYEMAIL] ? false : $user_member[UDB_EMAIL];
                $captcha_enabled = false;
            }
            else
            {
                echo '<div class="blocking_posting_comment">'.lang('Wrong password!').' <a href="javascript:document.location = \''.$_SERVER['HTTP_REFERER'].'\'">'.lang('Refresh').'</a></div>';
                add_to_log($name, lang('Wrong password (posting comment with exist username)'));
                return FALSE;
            }
        }
    }
    else $is_member = false;

    // ---------------------------------
    // Converting to UTF8 [Try]
    // ---------------------------------
    if ($config_useutf8 == "1" && function_exists('iconv'))
    {
        list($hac) = spsep($config_default_charset);
        $name      = iconv($hac, 'utf-8', $name);
        $comments  = iconv($hac, 'utf-8', $comments);
    }

    // Captcha test (if not disabled force)
    if ($captcha != $_SESS['CSW'] && $config_use_captcha && $captcha_enabled)
    {
        echo '<div class="blocking_posting_comment">'.lang('Wrong captcha').'! <a href="javascript:location.reload(true)">'.lang('Refresh').'</a></div>';
        add_to_log($ip, 'Attack to captcha');
        return FALSE;
    }

    //----------------------------------
    // Check if only members can comment
    //----------------------------------
    if ($config_only_registered_comment == "yes" and !$is_member)
    {
        echo '<div class="blocking_posting_comment">'.lang('Sorry but only registered users can post comments, and').' "'.htmlspecialchars($name).'" '.lang('is not recognized as valid member').'.</div>';
        return FALSE;
    }

    //----------------------------------
    // Wrap the long words
    //----------------------------------
    if ($config_auto_wrap > 1)
    {
        $comments_arr = explode("\n", $comments);
        foreach ($comments_arr as $line)
        {
            $wraped_comm .= preg_replace("([^ \/\/]{".$config_auto_wrap."})","\\1\n", $line) ."\n";
        }

        if(strlen($name) > $config_auto_wrap)
            $name = substr($name, 0, $config_auto_wrap)." ...";

        $comments = $wraped_comm;
    }

    //----------------------------------
    // Do some validation check 4 name, mail..
    //----------------------------------
    $comments   = replace_comment("add", $comments);
    $name       = replace_comment("add", preg_replace("/\n/", "",$name));
    $mail       = replace_comment("add", preg_replace("/\n/", "",$mail));

    if (trim($name) == false)
    {
        echo '<div class="blocking_posting_comment">'.lang('You must enter name').'.<br /><a href="javascript:history.go(-1)">'.lang('go back').'</a></div>';
        return FALSE;
    }

    if (trim($mail) == false) $mail = "none";
    else
    {
        $ok = false;

        if (preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/i", $mail))
            $ok = true;

        elseif ($config_allow_url_instead_mail == "yes" and preg_match("/((http(s?):\/\/)|(www\.))([\w\.]+)([\/\w+\.-?]+)/i", $mail))
            $ok = true;

        elseif ($config_allow_url_instead_mail != "yes")
        {
            echo '<div class="blocking_posting_comment">'.lang('This is not a valid e-mail').'<br /><a href="javascript:history.go(-1)">'.lang('go back').'</a></div>';
            return FALSE;
        }
        else
        {
            echo '<div class="blocking_posting_comment">'.lang('This is not a valid e-mail or site URL').'<br /><a href="javascript:history.go(-1)">'.lang('go back').'</a></div>';
            return FALSE;
        }
    }

    if (empty($comments))
    {
        echo '<div class="blocking_posting_comment">'.lang('Sorry but the comment cannot be blank').'<br /><a href="javascript:history.go(-1)">'.lang('go back').'</a></div>';
        return FALSE;
    }

    $time = time() + $config_date_adjust*60;

    //----------------------------------
    // Hook comment checker
    // if hook return TRUE, exit
    //----------------------------------
    if ( hook('add_comment_checker', FALSE) ) return FALSE;

    //----------------------------------
    // Add The Comment ... Go Go GO!
    //----------------------------------
    $old_comments = file($comm_file);
    $new_comments = fopen($comm_file, "w");
    if (!$new_comments) die_stat(503, lang('System error. Try again'));
    flock ($new_comments, LOCK_EX);

    $found = FALSE;
    foreach ($old_comments as $old_comments_line)
    {
        $old_comments_arr = explode("|>|", $old_comments_line);
        if($old_comments_arr[0] == $id)
        {
            $old_comments_arr[1] = trim($old_comments_arr[1]);
            fwrite($new_comments, "$old_comments_arr[0]|>|$old_comments_arr[1]$time|$name|$mail|$ip|$comments||\n");
            $found = TRUE;
        }
        else
        {
            // if we do not have the news ID in the comments.txt we are not doing anything (see comment below) (must make sure the news ID is valid)
            fwrite($new_comments, $old_comments_line);
        }
    }

    // If id news for comment not found, add new id
    if(!$found)
    {
        fwrite($new_comments, "$id|>|$time|$name|$mail|$ip|$comments||\n");
    }

    flock ($new_comments, LOCK_UN);
    fclose($new_comments);

    //----------------------------------
    // Sign this comment in the Flood Protection
    //----------------------------------
    if ($config_flood_time != "0" and $config_flood_time != "" )
    {
        $flood_file = fopen(SERVDIR."/cdata/flood.db.php", "a");
        flock ($flood_file, LOCK_EX);
        fwrite($flood_file, time()."|$ip|$id|\n");
        flock ($flood_file, LOCK_UN);
        fclose($flood_file);
    }

    // checkout
    hook('comment_added');

    //----------------------------------
    // Notify for New Comment ?
    //----------------------------------
    if ($config_notify_comment == "yes" and $config_notify_status == "active")
    {
        $date    = date($config_timestamp_active, time() + $config_date_adjust*60);
        $url     = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $subject = lang("CuteNews - New Comment Added");
        $message = lang("New Comment was added by %1 on %3 at %4\n\n%2 ", $name, $comments, $date, $url);

        send_mail($config_notify_email, $subject, $message);
    }

    $URL = RWU( 'readcomm', $PHP_SELF . build_uri('subaction,id,ucat,archive,start_from:comm_start_from,title', array('showcomments', $id ,$ucat, $archive, $start_from, titleToUrl($news_arr[NEW_TITLE])), false));
    echo '<script type="text/javascript">window.location="'.$URL.'";</script>';

    // ------------ ALL OK ----------------
    return TRUE;