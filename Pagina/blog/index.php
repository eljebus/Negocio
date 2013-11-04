<?php
/***************************************************************************
 CuteNews CutePHP.com
 Copyright (с) 2012-2013 Cutenews Team
****************************************************************************/
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

include ('core/init.php');
include ('core/loadenv.php');

if ( $using_safe_skin )
     require_once(SERVDIR."/skins/base_skin/default.skin.php");
else require_once(SERVDIR."/skins/$config_skin.skin.php");

$PHP_SELF = "index.php";

// Deprecated functional checking
deprecated_check();

// Check if CuteNews is not installed
$fp = fopen(SERVDIR."/cdata/users.db.php", 'r'); fgets($fp); $user = trim(fgets($fp)); fclose($fp);
$enter_without_login = ($user == false) ? true : false;

hook('index_init');
b64dck();

if ($action == "logout")
{
    add_to_log( $_SESS['user'], 'logout');

    $_SESS['user'] = false;
    send_cookie(true);

    msg("info", lang("Logout"), lang("You are now logged out").", <a href=\"$PHP_SELF\">".lang('login')."</a>");
}

// sanitize
$is_loged_in = false;

// Check the User is Identified -------------------------------------------------------------------------------------
$result      = false;
$username    = isset($_POST['username']) ? $_POST['username'] : $_SESS['ix'];
$password    = $_POST['password'];

// User is banned
if ( $bandata = user_getban($ip, false))
{
     if ($bandata[1] > $config_ban_attempts + 1)
        msg('error', lang('Error!'), getpart('youban', format_date( $bandata[2], 'since-short')));
}

if ( empty($_SESS['user']))
{
    /* Login Authorization using COOKIES */
    if ($action == 'dologin')
    {
        // Do we have correct username and password ?
        $member_db      = user_search($username);
        $md5_hash       = $member_db[UDB_PASS];
        $cmd5_password  = hash_generate($password, $md5_hash);

        if ( in_array($member_db[UDB_PASS], $cmd5_password))
        {
            $_SESS['ix']    = $username;
            $_SESS['user']  = $username;

            if ($rememberme == 'yes') $_SESS['@'] = true;
            elseif (isset($_SESS['@'])) unset($_SESS['@']);

            add_to_log($username, 'login');
            user_remove_ban($ip);

            // Modify Last Login
            $member_db[UDB_LAST] = time();
            user_update($username, $member_db);

            $is_loged_in = true;
            send_cookie();
        }
        else
        {
            $_SESS['user'] = false;

            $bandata = user_addban($ip, time() + 3600);
            $result .= getpart('block_ban', $bandata[1], date('d-m-Y H:i:s', $bandata[2]) );

            add_to_log($username, lang('Wrong username/password'));
            $is_loged_in = false;
            send_cookie();
        }
    }
}
else
{
    // Check existence of user
    $member_db = user_search($_SESS['user']);
    if ($member_db)
    {
        $is_loged_in = true;
    }
    else
    {
        $_SESS['user'] = false;
        $is_loged_in = false;
        send_cookie();
    }
}

// ---------------------------------------------------------------------------------------------------------------------
// If User is Not Logged In, Display The Login Page

// First RUN
if ($enter_without_login )
{
    $is_loged_in = TRUE;

    // Initial
    $member_db = array
    (
        UDB_ID => time(),
        UDB_ACL => ACL_LEVEL_ADMIN,
        UDB_NAME => 'Administrator',
        UDB_PASS => md5('123456'),
        UDB_NICK => '',
        UDB_EMAIL => 'your-email@example.com',
        UDB_COUNT => 0,
        UDB_CBYEMAIL => 1,
        UDB_AVATAR => '',
        UDB_LAST => time(),
    );

    if (REQ('section') == 'main_area')
    {
        $ht = hash_generate(REQ('admin_passwd'));
        $member_db[UDB_NAME]  = REQ('admin_name');
        $member_db[UDB_EMAIL] = REQ('admin_email');
        $member_db[UDB_PASS]  = $ht[ count($ht)-1 ];

        if (REQ('admin_name')   == false) msg('error', lang('error'), lang('Enter name'), '#GOBACK');
        if (REQ('admin_email')  == false) msg('error', lang('error'), lang('Enter email'), '#GOBACK');
        if (REQ('admin_passwd') == false) msg('error', lang('error'), lang('Enter password'), '#GOBACK');

        // add user
        user_add($member_db);
        make_crypt_salt();

        // Run Once
        if (!file_exists(SERVDIR.'/cdata/installed.mark'))
        {
            fclose( fopen(SERVDIR.'/cdata/installed.mark', 'w') );
            relocation("http://www.cutephp.com/thanks.php?referer=".urlencode(base64_encode('http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'])));
        }
        else
        {
            msg('info', 'Notification', lang('You have successfully installed Cutenews! Refresh page to login.'));
        }
    }
}

if (empty($is_loged_in))
{
    echoheader("user", lang("Please Login"));
    echo proc_tpl('login_window',
                  array('lastusername'  => htmlspecialchars($username) ),
                  array('ALLOW_REG'     => ($config_allow_registration == "1")? 1:0 ) );

    echofooter();
}
elseif ($is_loged_in)
{

    // User banned
    if ( 'blocked' == user_getban($member_db[UDB_NAME], true) )
    {
        $_SESS['user'] = false;
        send_cookie();

        msg('error', lang('Error!'), lang('You\'re banned!'));
    }

    // ********************************************************************************
    // Include System Module
    // ********************************************************************************

                            //name of mod   //access
    $system_modules = array('addnews'       => 'user',
                            'editnews'      => 'user',
                            'main'          => 'user',
                            'options'       => 'user',
                            'images'        => 'user',
                            'editusers'     => 'admin',
                            'editcomments'  => 'admin',
                            'tools'         => 'admin',
                            'ipban'         => 'admin',
                            'about'         => 'user',
                            'categories'    => 'admin',
                            'massactions'   => 'user',
                            'help'          => 'user',
                            'debug'         => 'admin',
                            'wizards'       => 'admin',
                            'update'        => 'user',
                            'rating'        => 'user',
                            );

    list($system_modules, $mod, $stop) = hook('system_modules_expand', array($system_modules, $mod, false));

    // Plugin tells us: don't show anything, stop
    if ($stop == false)
    {
        if ($mod == false) require(SERVDIR."/inc/main.php");
        elseif( $system_modules[$mod] )
        {
            if ($mod == 'rating')
            {
                require (SERVDIR."/inc/ratings.php");
            }
            elseif ($member_db[UDB_ACL] == ACL_LEVEL_COMMENTER and $mod != 'options' and $mod != 'update')
            {
                relocation($config_http_script_dir."/index.php?mod=options&action=personal");
            }
            elseif( $system_modules[$mod] == "user")
            {
                require (SERVDIR."/inc/".$mod.".php");
            }
            elseif( $system_modules[$mod] == "admin" and $member_db[UDB_ACL] == ACL_LEVEL_ADMIN)
            {
                require (SERVDIR."/inc/".$mod.".php");
            }
            elseif( $system_modules[$mod] == "admin" and $member_db[UDB_ACL] != ACL_LEVEL_ADMIN)
            {
                msg("error", lang("Access denied"), "Only admin can access this module");
            }
            else
            {
                die("Module access must be set to <b>user</b> or <b>admin</b>");
            }
        }
        else
        {
            add_to_log($username, 'Module '.htmlspecialchars($mod).' not valid');
            die_stat(false, htmlspecialchars($mod)." is NOT a valid module");
        }
    }
}

exec_time();