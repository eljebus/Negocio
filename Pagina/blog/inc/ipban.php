<?php

if (!defined('INIT_INSTANCE')) die('Access restricted');

if ($member_db[UDB_ACL] != ACL_LEVEL_ADMIN)
    msg("error", "Access Denied", "You don't have permission for this section");

// ********************************************************************************
// Add IP
// ********************************************************************************
if ($action == "add" or $action == "quickadd")
{
    if (!empty($add_ip)) user_addban($add_ip);

    // from editcomments 
    if ($action == "quickadd")
        die_stat(false, str_replace('%1', $add_ip, lang('The IP %1 is now banned from commenting')));
}
// ********************************************************************************
// Remove IP
// ********************************************************************************
elseif($action == "remove")
{
    if (empty($remove_ip)) msg("error", lang('Error!'), lang("The IP or nick cannot be blank"), '#GOBACK');
    user_remove_ban($remove_ip);
}

// ********************************************************************************
// List all IP
// ********************************************************************************
echoheader("options", lang("Blocking IP / Nickname"), make_breadcrumbs('main/options=options/Block IP or nickname'));

$c      = 0;
$iplist = array();

// read all lines
$ips = fopen(SERVDIR.'/cdata/ipban.db.php', 'r');
while (!feof($ips))
{
    $dip = explode('|', fgets($ips));
    if (empty($dip[0])) continue;
    if (substr($dip[0], 0, 2) == '<'.'?') continue;

    $e = $dip[2] ? format_date($dip[2], 'since-short') : 'never';
    $iplist[] = array('ip' => $dip[0], 'bg' => $c++%2? 'bgcolor="#F7F8FF"' : '', 'times' => $dip[1], 'expire' => $e );
}
fclose($ips);

echo proc_tpl('ipban/index');
echofooter();
?>