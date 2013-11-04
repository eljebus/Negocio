<?php

if (!defined('INIT_INSTANCE')) die('Access restricted');

$CNpass             = isset($_COOKIE['CNpass']) && $_COOKIE['CNpass'] ? $_COOKIE['CNpass'] : false;
$captcha_enabled    = $CNpass ? false : true;

// ---------------------------------------------------------------------------------------------------------------------
do
{
    // plugin tells us: he is fork, stop
    if ( hook('fork_shows_inc', false) ) break;

    // Used if we want to display some error to the user and halt the rest of the script
    $user_query      = cute_query_string($QUERY_STRING, array( "comm_start_from", "start_from", "archive", "subaction", "id", "ucat"));
    $user_post_query = cute_query_string($QUERY_STRING, array( "comm_start_from", "start_from", "archive", "subaction", "id", "ucat"), "post");

    // Define Categories
    $cat = array();
    $cat_lines = file(SERVDIR."/cdata/category.db.php");
    foreach ($cat_lines as $single_line)
    {
        $cat_arr                        = explode("|", $single_line);
        $cat[ $cat_arr[CAT_ID] ]        = $cat_arr[CAT_NAME];
        $cat_icon[ $cat_arr[CAT_ID] ]   = $cat_arr[CAT_ICON];
    }

    // Define Users
    $all_users = file(SERVDIR."/cdata/users.db.php");
    unset ($all_users[UDB_ID]);

    foreach ($all_users as $user)
    {
        $user_arr = user_decode($user);

        // nick exists?
        if ($user_arr[UDB_NICK])
        {
            $my_names[$user_arr[UDB_NAME]]     = ($user_arr[UDB_CBYEMAIL] != 1 and $user_arr[UDB_EMAIL])? '<a href="mailto:'.hesc($user_arr[UDB_EMAIL]).'">'.hesc($user_arr[UDB_NICK]).'</a>' : hesc($user_arr[UDB_NICK]);
            $name_to_nick[$user_arr[UDB_NAME]] = $user_arr[UDB_NICK];
        }
        else
        {
            $my_names[$user_arr[UDB_NAME]]     = ($user_arr[UDB_CBYEMAIL] != 1 and $user_arr[UDB_EMAIL])? '<a href="mailto:'.hesc($user_arr[UDB_EMAIL]).'">'.hesc($user_arr[UDB_NAME]).'</a>' : hesc($user_arr[UDB_NAME]);
            $name_to_nick[$user_arr[UDB_NAME]] = $user_arr[UDB_NAME];
        }

        $my_mails[ $user_arr[UDB_NAME] ]   = ($user_arr[UDB_CBYEMAIL] == 1)? "" : $user_arr[UDB_EMAIL];
        $my_passwords[$user_arr[UDB_NAME]] = $user_arr[UDB_PASS];
        $my_users[] = $user_arr[UDB_NAME];

    }

    ResynchronizePostponed();
    if ($config_auto_archive == "yes") ResynchronizeAutoArchive();
    hook('resync_routines');

    // Add Comment -----------------------------------------------------------------------------------------------------
    if ($allow_add_comment)
    {
        $break = include (SERVDIR.'/core/com/allow_add_comment.php');
        if ($break === FALSE) { $CN_HALT = TRUE; break; }
    }

    // Show Full Story -------------------------------------------------------------------------------------------------
    if ($allow_full_story)
    {
        $break = include (SERVDIR.'/core/com/allow_full_story.php');
        if ($break === FALSE) { $CN_HALT = TRUE; break; }
    }

    // Show Comments ---------------------------------------------------------------------------------------------------
    if ($allow_comments)
    {
        $break = include (SERVDIR.'/core/com/allow_comments.php');
        if ($break === FALSE) { $CN_HALT = TRUE; break; }
    }

    // Active News -----------------------------------------------------------------------------------------------------
    if ($allow_active_news)
    {
        $break = include (SERVDIR.'/core/com/allow_active_news.php');
        if ($break === FALSE) { $CN_HALT = TRUE; break; }
    }
}
while (FALSE);

// ---------------------------------------------------------------------------------------------------------------------
if ((!isset($count_cute_news_includes) or !$count_cute_news_includes) and $template != 'rss')
{
    /// Removing the "Powered By..." line is NOT allowed by the CuteNews License, only registered users are alowed to do so.
    if (!file_exists(SERVDIR."/cdata/reg.php"))
    {
         echo base64_decode('PGRpdiBzdHlsZT0ibWFyZ2luLXRvcDoxNXB4O3dpZHRoOjEwMCU7dGV4dC1hbGlnbjpjZW50ZXI7Zm9udDo5cHggVmVyZGFuYTsiPlBvd2VyZWQgYnkgPGEgaHJlZj0iaHR0cDovL2N1dGVwaHAuY29tLyIgdGl0bGU9IkN1dGVOZXdzIC0gUEhQIE5ld3MgTWFuYWdlbWVudCBTeXN0ZW0iPkN1dGVOZXdzPC9hPjwvZGl2Pg==');
    }
    else
    {
        include(SERVDIR."/cdata/reg.php");
        if ( !preg_match('/\\A(\\w{6})-\\w{6}-\\w{6}\\z/', $reg_site_key, $mmbrid))
        {
            echo base64_decode('PGRpdiBzdHlsZT0ibWFyZ2luLXRvcDoxNXB4O3dpZHRoOjEwMCU7dGV4dC1hbGlnbjpjZW50ZXI7Zm9udDo5cHggVmVyZGFuYTsiPkNvbnRlbnQgTWFuYWdlbWVudCBQb3dlcmVkIGJ5IDxhIGhyZWY9Imh0dHA6Ly9jdXRlcGhwLmNvbS8iIHRpdGxlPSJDdXRlTmV3cyAtIFBIUCBOZXdzIE1hbmFnZW1lbnQgU3lzdGVtIj5DdXRlTmV3czwvYT48L2Rpdj4=');
        }
    }
}

$count_cute_news_includes++;