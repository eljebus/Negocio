<?php

require_once 'core/init.php';
include ('core/loadenv.php');

// Save path for htaccess
$w = fopen(SERVDIR.'/cdata/htpath.php', 'w'); fwrite($w, '<'.'?php $ht_path = "'.dirname(__FILE__).'"; ?>'); fclose($w);

$imod = isset($imod) && $imod ? $imod : false;
$allowed_modules = hook('expand_allowed_modules', array
(
    'userlist'
));

if (in_array($imod, $allowed_modules))
    include ("core/features/$imod.php");

hook('expand_code_shows');

?>