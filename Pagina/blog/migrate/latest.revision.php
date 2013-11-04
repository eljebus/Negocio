<?php

// Updater to latest revision
function remove_if_exists($path)
{
    $path = SERVDIR.'/'.$path;
    if (file_exists($path)) unlink($path);
}

// REVISION 67: Remove thumbs, db.fulltext.php deprecated
remove_if_exists('skins/images/Thumbs.db');
remove_if_exists('cdata/db.fulltext.php');