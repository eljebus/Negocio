<?php

if (!defined('INIT_INSTANCE')) die('Access restricted');

/* Order Start: put chars in whatever order you want

    u = usernames
    n = nicknames
    r = registration dates
    a = avatar links
    p = post counts
    l = access levels
    e = emails
*/

$user_flags = strtolower($user_flags);
$date_format = "F, d Y @ H:i a";

$th = '<table border=0 cellspacing=0 cellpadding=3><tr>';
if (strpos($user_flags,'u') !== false) $th .= '<td><b>Username</b></td>';
if (strpos($user_flags,'n') !== false) $th .= '<td><b>Nickname</b></td>';
if (strpos($user_flags,'r') !== false) $th .= '<td><b>Registration Date</b></td>';
if (strpos($user_flags,'a') !== false) $th .= '<td><b>Avatar</b></td>';
if (strpos($user_flags,'p') !== false) $th .= '<td><b>Posts</b></td>';
if (strpos($user_flags,'l') !== false) $th .= '<td><b>Access Level</b></td>';
if (strpos($user_flags,'e') !== false) $th .= '<td><b>EMail</b></td>';
$th .= '</tr>';

$all_users = file(SERVDIR."/cdata/users.db.php");
unset($all_users[0]);

// Get all $_GET parameters for future build_uri
// Sorting

// Show users
$i = 0;
foreach ($all_users as $user_line)
{
    $i++;
    $bg = " align='center'";
    if($i%2 == 0 && isset($bgcolor) && $bgcolor != "") $bg .= " bgcolor='$bgcolor'";

    $user_arr = user_decode($user_line);
    $user_joined = date($date_format, $user_arr[UDB_ID]);

    if ($user_arr[UDB_AVATAR] != "")
        $user_av = '<a target="_blank" href="'.$user_arr[UDB_AVATAR].'">[click]</a>';
    else $user_av = "[none]";

    switch ($user_arr[UDB_ACL])
    {
        case 1: $user_level = "administrator"; break;
        case 2: $user_level = "editor"; break;
        case 3: $user_level = "journalist"; break;
        case 4: $user_level = "commenter"; break;
        case 5: $user_level = "banned"; break;
        default: $user_level = "undefined";
    }

    if  ($user_arr[UDB_CBYEMAIL] == "0")
        $user_email = '<a href="mailto:'.$user_arr[UDB_EMAIL].'">[send mail]</a>';
    else $user_email = "[hidden]";

    if (strpos($user_flags,'u') !== false) $th .= '<td'.$bg.'>'.$user_arr[UDB_NAME].'</td>';
    if (strpos($user_flags,'n') !== false) $th .= '<td'.$bg.'>'.$user_arr[UDB_NICK].'</td>';
    if (strpos($user_flags,'r') !== false) $th .= '<td'.$bg.'>'.$user_joined.'</td>';
    if (strpos($user_flags,'a') !== false) $th .= '<td'.$bg.'>'.$user_av.'</td>';
    if (strpos($user_flags,'p') !== false) $th .= '<td'.$bg.'>'.$user_arr[UDB_COUNT].'</td>';
    if (strpos($user_flags,'l') !== false) $th .= '<td'.$bg.'>'.$user_level.'</td>';
    if (strpos($user_flags,'e') !== false) $th .= '<td'.$bg.'>'.$user_email.'</td>';
}

$th .= '</table>';
echo $th;

// Field  $sortby [by]