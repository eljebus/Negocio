<?php

    // check headers information
    if (isset($_REQUEST['trace'])) { echo $_SERVER['HTTP_ACCEPT_CHARSET']; exit(); }

    require_once ('core/init.php');
    include ('core/loadenv.php');

    // plugin tells us: he is fork, stop
    if ( hook('fork_router', false) ) return;

    $mod = isset($_GET['mod']) && $_GET['mod'] ? $_GET['mod'] : false;

    if ($mod == 'show_archives') include ('show_archives.php');
    elseif ($mod == 'shows')     include ('shows.php');
    else                         include ('show_news.php');

    hook('router_file_after');
