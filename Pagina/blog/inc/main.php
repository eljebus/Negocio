<?php

if (!defined('INIT_INSTANCE')) die('Access restricted');

hook('init_main');

// If member access level is commenter, redirect him to personal options
if ($member_db[UDB_ACL] == ACL_LEVEL_COMMENTER)
    relocation($config_http_script_dir."/index.php?mod=options&action=personal");

// ----------------------------------------
if (REQ('action','GET') == 'permissions')
{
    $errors = cn_selfcheck();

    if (empty($errors))
    {
        msg('info', lang('Everything is OK'), lang('All the files are writable'), '#GOBACK');
    }
    else
    {
        msg('info', lang('Permissions error'), proc_tpl('main/perms'));
    }
}

// Check previous versions
$data_folder_exists = (is_dir(SERVDIR.'/data')) ? 1 : 0;

// ----------------------------------------
echoheader("home", lang("Welcome"));

if (!is_readable(SERVDIR."/cdata/archives"))
    die_stat(false, lang("Cannot open directory `archives` for reading, check if it exists or is properly CHMOD'ed"));

if (!is_readable(SERVDIR."/cdata/news.txt"))
    die_stat(false, lang("Cannot open file news.txt for reading, check if it exists or is properly CHMOD'ed"));

if (!is_readable(SERVDIR."/cdata/comments.txt"))
    die_stat(false, lang("Cannot open file comments.txt for reading, check if it exists or is properly CHMOD'ed"));

// Some Stats
$count_postponed_news   = 0;
$count_unapproved_news  = 0;
$todaynews              = 0;
$count_comments         = 0;
$count_my_news          = 0;
$count_new_news         = 0;

$news_db = file(SERVDIR."/cdata/news.txt");
foreach ($news_db as $line)
{
    $item_db = explode("|", $line);
    $itemdate = date("d/m/y", $item_db[0]);
    if ($itemdate == date("d/m/y", time() + $config_date_adjust*60))
    {
        $todaynews++;
        if ( $item_db[1] == $member_db[UDB_NAME]) $count_my_news++;
        if (($item_db[0] > $member_db[UDB_LAST]) and ($member_db[UDB_LAST] != '')) $count_new_news++;
    }
}

$stats_news             = count( $news_db );
$stats_users            = count( file(SERVDIR."/cdata/users.db.php") ) - 1;
$count_postponed_news   = count( file(SERVDIR."/cdata/postponed_news.txt") );

if ($count_postponed_news > 0)      ResynchronizePostponed();
if ($config_auto_archive == "yes")  ResynchronizeAutoArchive();
$count_unapproved_news = count( file(SERVDIR."/cdata/unapproved_news.txt") );

$stats_archives = 0;
$handle = opendir(SERVDIR."/cdata/archives");
while (false !== ($file = readdir($handle)))
{
    if( preg_match("/.news.arch/", $file) ) $stats_archives++;
}
closedir($handle);

// Count Comments
$all_comments = file(SERVDIR."/cdata/comments.txt");

foreach($all_comments as $news_comments)
{
    $single_news_comments   = explode("|>|", $news_comments);
    $individual_comments    = explode("||", $single_news_comments[1]);
    $count_comments        += count($individual_comments) - 1;
}

if($todaynews != 1) $s = "s";

if($member_db[UDB_ACL] != ACL_LEVEL_COMMENTER)
{
    srand( (double)microtime() * 1000000 );
    
    if ($stats_users > 1)
    {
        $rand_msg[] = ', '.str_replace( array('%1','%2'), array($count_new_news, date("r", $member_db[UDB_LAST])), lang('we have <b>%1</b> new articles since your last login (@ %2)'));
    }

    if ($todaynews == 0)
    {
        $rand_msg[] = ', '.lang("we don't have new articles today");
        $rand_msg[] = ", ".lang("we don't have new articles today, the first one can be yours.");
    }
    elseif($count_my_news == 0)
    {
        if  ($todaynews == 1)
             $rand_msg[] = ", ".str_replace(array('%1','%2'),       array($todaynews, $s), lang("today we have <b>%1</b> new article%2 but it is not yours"));
        else $rand_msg[] = ", ".str_replace(array('%1','%2','%3'),  array($todaynews, $s, $count_my_news), lang("today we have <b>%1</b> new article%2 but <b>%3</b> of them are yours"));
    }
    elseif($count_my_news == $todaynews)
    {
        if($count_my_news == 1)
        {
            $rand_msg[] = ", ".str_replace(array('%1','%2'), array($todaynews, $s), lang("today we have <b>%1</b> new article%2 and you wrote it"));
        }
        else
        {
            $rand_msg[] = ", ".str_replace(array('%1','%2'),        array($todaynews, $s), lang("today we have <b>%1</b> new article%2 and you wrote all of them"));
            $rand_msg[] = ", ".str_replace(array('%1','%2'),        array($todaynews, $s), lang("today we have <b>%1</b> new article%2 and all are yours"));
            $rand_msg[] = ", ".str_replace(array('%1','%2','%3'),   array($todaynews, $s, $PHP_SELF.'?mod=addnews&action=addnews'), lang('today we have <b>%1</b> new article%2, want to <a href="%3"><b>add</b></a> some more?'));
        }
    }
    else
    {
        if ($count_my_news == 1)
             $rand_msg[] = ", ".str_replace(array('%1','%2'), array($todaynews, $s), lang('today we have <b>%1</b> new article%2, <b>1</b> of them is yours'));
        else $rand_msg[] = ", ".str_replace(array('%1','%2','%3'), array($todaynews, $s, $count_my_news), lang('today we have <b>%1</b> new article%2, <b>%3</b> of them are yours'));
    }

    $rand_msg[] = ", ".str_replace('%1', $PHP_SELF.'?mod=addnews&action=addnews', lang('are you in a mood of <a href="%1"><b>adding</b></a> some news?'));
    $rand_msg[] = ", ".str_replace(array('%1','%2','%3'), array($todaynews, $s, $stats_news), lang('today we have <b>%1</b> new article%2, from total <b>%3</b>'));

    if ($member_db[UDB_LAST])
        $rand_msg[] = ", ".lang('your last login was on').' '.date("d M Y H:i:s", $member_db[UDB_LAST]);

    $rand_msg[] = "";
}

$warn = false;

//----------------------------------
// Deprecated data
//----------------------------------
if (file_exists(SERVDIR.'/cdata/db.users.php'))
    $warn .= proc_tpl('main/deprecated', array('deprecate_message' => lang('File cdata/db.users.php is deprecated. Delete <b>db.users.php</b> file from cdata')));

//----------------------------------
// Notify user if the news were auto-archived
//----------------------------------

//get last auto-archive date
$ladb_content = file(SERVDIR."/cdata/auto_archive.db.php");

list ($last_archived['year'], $last_archived['month']) = explode("|", $ladb_content[0] );
$last_login_year = date('Y', (int)$member_db[UDB_LAST]);
$last_login_month = date('n', (int)$member_db[UDB_LAST]);

if ((int)$last_login_month < (int)$last_archived['month'] and $last_login_year <= $last_archived['year'])
    $warn .= proc_tpl('main/auto_archive', array('date' => date("d M Y H:i:s", $member_db[UDB_LAST])));

//----------------------------------
// First Login
//----------------------------------
if ($enter_without_login)
    $warn .= proc_tpl('main/firstlogin', array());

//----------------------------------
// Do we have enough free space ?
//----------------------------------
if (function_exists('disk_free_space')) $dfs = disk_free_space( SERVDIR ); else $dfs = 0;
if ($dfs and $dfs < 1024 * 10) $warn .= proc_tpl('main/disk_space', array('freespace' => formatsize($dfs)));

//----------------------------------
// Is our PHP version old ?
//----------------------------------
if ($phpversion and $phpversion < '4.1.0') $warn .= proc_tpl('main/php_old', array('phpversion' => $phpversion));

//----------------------------------
// Are we using SafeSkin ?
//----------------------------------
if ($using_safe_skin)
    $warn .= proc_tpl('main/safe_skin', array('config_skin' => $config_skin));

// Greet script
echo str_replace(array('{member}','{greet}', '{warn}'),
                 array($member_db[UDB_NAME], $rand_msg[rand(0, count($rand_msg)-1)], $warn),
                 proc_tpl('main/greet'));

$filesize   = array(
                    '/cdata/news.txt' => 'News size',
                    '/cdata/users.db.php' => 'Users size',
                    '/cdata/cache/error_dump.log' => 'Error dump size',
);

// Common statistics
$fs = 0;
foreach ($filesize as $i => $v)
{
    if  (file_exists(SERVDIR . $i))
         $fs_t = filesize(SERVDIR . $i);
    else $fs_t = 0;

    $msgs['fs'][] = array($v, formatsize( $fs_t, $v ));
    $fs += $fs_t;
}

if (function_exists('disk_free_space') && function_exists('disk_total_space'))
{
    $msgs['fs'][] = array('Free disk space', formatsize( disk_free_space(SERVDIR) ) );
    $factor = (int)(100 * (1 - disk_free_space('/') / disk_total_space('/')));
    if ($factor > 100) $factor = 100;
    if ($factor < 0) $factor = 0;
}
else $factor = false;

$msgs['fs'][] = array("<a title='".lang('View all Active News (Edit News)')."' href='$PHP_SELF?mod=editnews&action=list'>".lang('Active News')."</a>", $stats_news);
$msgs['fs'][] = array(lang("Active Comments"), $count_comments);
$msgs['fs'][] = array("<a title='".lang('View all Postponed Articles')."' href='$PHP_SELF?mod=editnews&action=list&source=postponed'>".lang('Postponed News')."</a>", $count_postponed_news);
$msgs['fs'][] = array("<a title='".lang('View all Unapproved Articles')."' href='$PHP_SELF?mod=editnews&action=list&source=unapproved'>".lang('Unapproved News')."</a>", $count_unapproved_news);
$msgs['fs'][] = array("<a title='".lang('View all Archives (Archive Manager)')."' href='$PHP_SELF?mod=tools&action=archive'>".lang('Archives')."</a>", $stats_archives);
$msgs['fs'][] = array("<a title='".lang('View all Users (Add/Edit Users)')."' href='$PHP_SELF?mod=editusers&action=list'>".lang('Users')."</a>", $stats_users);

echo proc_tpl('main/syscheck', array('fs' => $msgs['fs'], 'free' => $factor));
echofooter();

hook('destroy_main');
?>
