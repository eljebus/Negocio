<?PHP

if (!defined('INIT_INSTANCE')) die('Access restricted');

if ($member_db[UDB_ACL] != ACL_LEVEL_ADMIN)
    msg("error", lang("Access Denied"), lang("You don't have permission for this section"));

$success = false;

// Sanitize backup name
$backup = preg_replace('/[^a-z0-9_\. ]/i', '-', trim($backup));

// ********************************************************************************
// Archive
// ********************************************************************************
if ($action == "archive")
{

    // ***************************
    // Un-Archive
    // ***************************
    if ($subaction == "unarchive" and !empty($aid))
    {
        if(!$handle = opendir(SERVDIR."/cdata/archives"))
            die_stat(false, lang("Unable to open directory")." ".SERVDIR."/cdata/archive");

        while (false !== ($file = readdir($handle)))
        {
            if ($file == "$aid.news.arch")
            {
                $newsfile = fopen(SERVDIR."/cdata/news.txt", 'a');
                $newsarch = file(SERVDIR."/cdata/archives/$file");
                foreach ($newsarch as $newsline) fwrite($newsfile, $newsline);

                fclose($newsfile);
                unlink(SERVDIR."/cdata/archives/$file");
            }
            elseif ($file == "$aid.comments.arch")
            {
                $commfile = fopen(SERVDIR."/cdata/comments.txt", 'a');
                $commarch = file(SERVDIR."/cdata/archives/$file");
                foreach ($commarch as $commline) fwrite($commfile,$commline);
                fclose($commfile);
                unlink(SERVDIR."/cdata/archives/$file");
            }
            elseif ($file == "$aid.count.arch")
            {
                unlink(SERVDIR."/cdata/archives/$file");
            }
        }
        closedir($handle);
    }

    // ***************************
    // Show Archives
    // ***************************
    $CSRF = CSRFMake();
    echoheader("archives", lang("Archives"), make_breadcrumbs('main/options=options/Archives'));

    if(!$handle = opendir(SERVDIR."/cdata/archives"))
        die_stat(false, lang("Cannot open directory")." ".SERVDIR."/cdata/archives ");

    while (false !== ($file = readdir($handle)))
    {
        if ($file != "." and $file != ".." and !is_dir(SERVDIR."/cdata/archives/$file") and substr($file, -9) == 'news.arch')
        {
            $file_arr           = explode(".", $file);
            $id                 = $file_arr[0];
            $news_lines         = file(SERVDIR."/cdata/archives/$file");
            $creation_date      = date("d F Y", $file_arr[0]);
            $count              = count($news_lines);
            $last               = $count - 1;
            $first_news_arr     = explode("|", $news_lines[$last]);
            $last_news_arr      = explode("|", $news_lines[0]);
            $first_timestamp    = $first_news_arr[0];
            $last_timestamp     = $last_news_arr[0];

            if ($creation_date)
            {
                $duration = (date("d M Y", intval($first_timestamp)) ." - ". date("d M Y", intval($last_timestamp)) );
                $inc .= "<tr><td>&nbsp;</td> <td>$creation_date</td> <td>$duration</td> <td align='center'>$count</td>";
                $inc .= "<td align='center'>
                             <a title='Edit the news in this archive' href=\"$PHP_SELF?mod=editnews&action=list&source=$id\">[edit]</a>
                             <a title='restore news from this archive to active news' href=\"$PHP_SELF?mod=tools&action=archive&subaction=unarchive&aid=$id\">[unarchive]</a>
                             <a title='Delete this archive' onclick=\"javascript:confirmdelete('$id', '$count');\" href=\"#\">[delete]</a></td> </tr>";
            }
        }
    }
    closedir($handle);

    if ($count == 0) $inc .= "<tr><td align=center colspan=6><br>".lang('There are no archives')."</td></tr>";

    echo proc_tpl('tools/archives/index', array('inclusion' => $inc, 'CSRF' => $CSRF));
    echofooter();

}
// ********************************************************************************
// Make Archive
// ********************************************************************************
elseif ($action == "doarchive")
{
    CSRFCheck();

    // Check archives
    if (filesize(SERVDIR."/cdata/news.txt") == 0)     msg("error", lang('Error!'), lang("Sorry but there are no news to be archived"), "#GOBACK");

    $arch_name = time() + ($config_date_adjust*60);
    if (!copy(SERVDIR."/cdata/news.txt", SERVDIR."/cdata/archives/$arch_name.news.arch"))
        msg("error", lang('Error!'), lang("Cannot create file")." ./cdata/archives/$arch_name.news.arch", "#GOBACK");

    if (!copy(SERVDIR."/cdata/comments.txt", SERVDIR."/cdata/archives/$arch_name.comments.arch"))
        msg("error", lang('Error!'), lang("Cannot create file")." ./cdata/archives/$arch_name.comments.arch", "#GOBACK");

    $handle = fopen(SERVDIR."/cdata/news.txt","w");
    fclose($handle);
    
    $handle = fopen(SERVDIR."/cdata/comments.txt","w");
    fclose($handle);

    msg("archives", lang("Archive Saved"), "&nbsp&nbsp; ".lang('All active news were successfully added to archives file with name')." <b>$arch_name.news.arch</b>", "#GOBACK");
}
// ********************************************************************************
// Delete Archive
// ********************************************************************************
elseif ($action == "dodeletearchive")
{
    CSRFCheck();

    $success = 0;
    if(!$handle = opendir(SERVDIR."/cdata/archives"))
        die_stat(lang("Cannot open directory")." ".SERVDIR."/cdata/archive ");

    while (false !== ($file = readdir($handle)))
    {
        if ($file == "$archive.news.arch" or $file == "$archive.comments.arch" or $file == "$archive.count.arch")
        {
            unlink(SERVDIR."/cdata/archives/$file");
            $success++;
        }
    }
    closedir($handle);

    if ($success > 1)
        msg("info", lang("Archive Deleted"), lang("The archive was successfully deleted"), "#GOBACK");

    elseif ($success > 0)
        msg("error", lang('Error!'), lang("Either the comments part, or the news part, or the count part of the archive was not deleted"), "#GOBACK");

    else
        msg("error", lang('Error!'), lang("The archive you specified was not deleted, it is not on the server or you don't have permissions to delete it"), "#GOBACK");

}
// ********************************************************************************
// Backup News and archives
// ********************************************************************************
elseif ($action == "backup")
{
    $count = 0;
    $CSRF = CSRFMake();
    echoheader("options", "Backup", make_breadcrumbs('main/options=options/Backup'));

    if (!is_dir(SERVDIR."/cdata/backup"))
        die_stat(false, lang("Cannot open directory")." ".SERVDIR."/cdata/backup ");

    $handle = opendir(SERVDIR."/cdata/backup");
    while (false !== ($file = readdir($handle)))
    {
        if ($file != "." and $file != ".." and is_dir(SERVDIR."/cdata/backup/$file"))
        {
            $archives_count = 0;

            $rd = SERVDIR."/cdata/backup/$file/archives";
            if (is_dir($rd))
            {
                $archives_handle = opendir($rd);
                while (false !== ($arch = readdir($archives_handle))) if(substr($arch, -9) == 'news.arch') $archives_count++;
                closedir($archives_handle);

                $news_count = count(file(SERVDIR."/cdata/backup/$file/news.txt"));
                $inc .= "<tr> <td>&nbsp;</td> <td>$file</td> <td align='center'>&nbsp;$news_count</td> <td align='center'>&nbsp;$archives_count</td>";
                $inc .= "<td>
                            <a onclick=\"confirmdelete('$file'); return(false)\" href=\"$PHP_SELF?mod=tools&action=dodeletebackup&backup=$file&csrf_code=$CSRF\">[delete]</a>
                            <a onclick=\"confirmrestore('$file'); return(false)\" href=\"$PHP_SELF?mod=tools&action=dorestorebackup&backup=$file&csrf_code=$CSRF\">[restore]</a></td> </tr>";

                $count++;
            }
        }
    }
    closedir($handle);

    if ($count == 0) $inc .= "<tr><td colspan=5><p align=center><br>".lang("There are no backups")."</p></td></tr>";
    echo proc_tpl('tools/backup/backup', array('inclusion' => $inc, 'CSRF' => $CSRF));

    echofooter();
}

// ********************************************************************************
// Delete Backup
// ********************************************************************************
elseif ($action == "dodeletebackup")
{
    CSRFCheck();
    function listdir($dir)
    {

        $current_dir = opendir($dir);
        if ($current_dir)
        {
            while($entryname = readdir($current_dir))
            {
                if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!=".."))
                {
                    listdir("${dir}/${entryname}");
                }
                elseif($entryname != "." and $entryname!="..")
                {
                    unlink("${dir}/${entryname}");
                }
            }
            closedir($current_dir);
            rmdir($dir);
        }

    }
    
    listdir(SERVDIR."/cdata/backup/$backup");

    msg("info", lang("Backup Deleted"), lang("The backup was successfully deleted"), "#GOBACK");
}
// ********************************************************************************
// Do restore backup
// ********************************************************************************
elseif($action == "dorestorebackup")
{
    CSRFCheck();

    // Check files
    if (!copy(SERVDIR."/cdata/backup/$backup/news.txt", SERVDIR."/cdata/news.txt"))
        msg("error", lang('Error!'), "./cdata/backup/$backup/news.txt", "#GOBACK");

    $dirp = opendir(SERVDIR."/cdata/backup/$backup/archives");
    if ($dirp)
    {
        while($entryname = readdir($dirp))
        {
            if (!is_dir(SERVDIR."/cdata/backup/$backup/archives/$entryname") and $entryname!="." and $entryname!="..")
            {
               if(!copy(SERVDIR."/cdata/backup/$backup/archives/$entryname", SERVDIR."/cdata/archives/$entryname"))
                   msg("error", lang('Error!'), lang("Cannot copy")." ./cdata/backup/$backup/archives/$entryname", "#GOBACK");
            }
        }
    }

    msg("info", lang("Backup Restored"), lang("The backup was successfully restored"), "#GOBACK");
}
// ********************************************************************************
// Make The Backup
// ********************************************************************************
elseif($action == "dobackup")
{
    CSRFCheck();

    // Check files
    if (filesize(SERVDIR."/cdata/news.txt") == 0)
        msg("error", lang('Error!'), lang("The news file is empty and cannot be backed-up"), "#GOBACK");

    if (is_readable(SERVDIR."/cdata/backup/$backup"))
        msg("error", lang('Error!'), lang("A backup with this name already exist"), "#GOBACK");

    if (!is_readable(SERVDIR."/cdata/backup"))
        mkdir(SERVDIR."/backup", 0777);

    if (!is_writable(SERVDIR."/cdata/backup"))
        msg("error", lang('Error!'), lang("The directory ./cdata/backup is not writable, please chmod it"), "#GOBACK");

    mkdir(SERVDIR."/cdata/backup/$backup", 0777);
    mkdir(SERVDIR."/cdata/backup/$backup/archives", 0777);

    if (!copy(SERVDIR."/cdata/news.txt", SERVDIR."/cdata/backup/$backup/news.txt"))
        die_stat(false, lang("Cannot copy news.txt file to")." ./cdata/backup/$backup :(");

    if(!copy(SERVDIR."/cdata/comments.txt",  SERVDIR."/cdata/backup/$backup/comments.txt"))
        die_stat(false, lang("Cannot copy comments.txt file to")." ./cdata/backup/$backup :(");

    if(!$handle = opendir(SERVDIR."/cdata/archives"))
        die_stat(false, lang("Cannot create file"));

    while(false !== ($file = readdir($handle)))
    {
        if($file != "." and $file != "..")
        {
            if(!copy(SERVDIR."/cdata/archives/$file", SERVDIR."/cdata/backup/$backup/archives/$file"))
                die_stat(false, lang("Cannot copy archive file to")." ./cdata/backup/$backup/archives/$file :(");
        }
    }
    closedir($handle);

    msg("info", lang("Backup"), lang("All news and archives were successfully backed up under directory")." './cdata/backup/$backup'", "#GOBACK");
}
elseif ($action == 'userlog')
{
    echoheader("options", lang("User log"), make_breadcrumbs('main/options/='.lang('User log')));

    list($year_s, $month_s, $day_s, $hour_s) = GET('year_s,month_s,day_s,hour_s', 'GET');
    list($year_e, $month_e, $day_e, $hour_e) = GET('year_e,month_e,day_e,hour_e', 'GET');
    list($per) = GET('per', 'GET');

    // make default date filter
    $year_s     = $year_s?  intval($year_s) : date('Y');
    $month_s    = $month_s? intval($month_s) : date('m');
    $day_s      = $day_s?   intval($day_s) : date('d');
    $hour_s     = $hour_s?  intval($hour_s) : 0;
    $year_e     = $year_e?  intval($year_e) : date('Y');
    $month_e    = $month_e? intval($month_e) : date('m');
    $day_e      = $day_e?   intval($day_e) : date('d');
    $hour_e     = $hour_e?  intval($hour_e) : 23;
    $per        = $per?     intval($per) : 25;

    $h_year_s = $year_s; $h_month_s = $month_s; $h_day_s = $day_s; $h_hour_s = $hour_s;
    $h_year_e = $year_e; $h_month_e = $month_e; $h_day_e = $day_e; $h_hour_e = $hour_e;

    // set limits
    if ($year_s < 2003) $year_s = 2003;
    if ($month_s < 1) $month_s = 1;
    if ($day_s < 1) $day_s = 1;
    if ($hour_s < 0) $hour_s = 0;
    if ($year_e < 2003) $year_e = 2003;
    if ($month_e < 1) $month_e = 1;
    if ($day_e < 0) $day_e = 0;
    if ($hour_e < 1) $hour_e = 1;

    if ($year_s > 9999) $year_s = 9999;
    if ($month_s > 12) $month_s = 12;
    if ($day_s > 31) $day_s = 31;
    if ($hour_s > 23) $hour_s = 23;
    if ($year_e > 9999) $year_e = 9999;
    if ($month_e > 12) $month_e = 12;
    if ($day_e > 31) $day_e = 31;
    if ($hour_e > 23) $hour_e = 23;

    if ($per < 0) $per = 25;

    // make request files
    $from_time  = mktime($hour_s, 0, 0, $month_s, $day_s, $year_s);
    $to_time    = mktime($hour_e, 59, 59, $month_e, $day_e, $year_e);
    $scan       = array();

    for ($time = $from_time; $time <= $to_time; $time += 3600*24*7)
        $scan[($fx = date('Y', $time).'_'.date('m', $time))] =  SERVDIR.'/cdata/log/log_'.$fx.'.php';

    // scan input files
    $logs = array();
    $count = 0;
    foreach ($scan as $v)
    {
        if (file_exists($v) && is_readable($v))
        {
            $lg = fopen($v, 'r');
            while (!feof($lg))
            {
                list ($time, $sarr) = explode('|', fgets($lg), 2);
                if ($from_time <= $time && $time <= $to_time)
                {
                    $pack = unserialize($sarr);
                    $pack['time'] = format_date($pack['time'], 'since');
                    $pack['bg'] = $count%2? '#FFFFFF' : '#F0F4FF';
                    $logs[ $time.'|'.mt_rand() ] = $pack;
                    $count++;
                }
            }
            fclose($lg);
        }
    }

    // Paginate (array slice)
    krsort($logs);

    $logs = array_slice($logs, $cr*$per, $per);

    // retrieve pagination
    $pages = pagination($count, $per, $cr);
    foreach ($pages as $i => $v)
    {
        $pages[$i]['link'] = $PHP_SELF . build_uri( 'cr,mod,action,year_s,month_s,day_s,hour_s,year_e,month_e,day_e,hour_e,per', array(intval($v['id']) ) );
        $pages[$i]['id']++;
        if ($v['pt'] == 0) $pages[$i]['id'] = '...';
        if ($v['cr'])
        {
            $pages[$i]['LB'] = '<b>';
            $pages[$i]['RB'] = '</b>';
        }
        else
        {
            $pages[$i]['LB'] = $pages[$i]['RB'] = '';
        }
    }

    // in case of incorrect input
    $message = false;
    if ($h_year_s != $year_s or $h_month_s != $month_s or $h_day_s != $h_day_s or $h_day_s != $h_day_s or $h_hour_s != $hour_s or
        $h_year_e != $year_e or $h_month_e != $month_e or $h_day_e != $h_day_e or $h_day_e != $h_day_e or $h_hour_e != $hour_e)
        $message = lang('Incorrect numbers! Date adjusted to limits');

    // show filter
    echo proc_tpl('tools/userlog/index');
    echofooter();
}
elseif ($action == 'replaces')
{
    if ($do == 'replace') CSRFCheck();
    $CSRF = CSRFMake();

    echoheader('options', lang('Replace words'), make_breadcrumbs('main/options/='.lang('Word Replacement')));

    $result = false;
    if ($do == 'replace')
    {
        $fx = fopen(SERVDIR.'/cdata/replaces.php', 'w');
        fwrite ($fx, "<?php die(); ?>\n". str_replace("\r", "", $replaces));
        fclose($fx);
        $result = 'Data successfully saved';
    }

    // -------------------
    $replaces = file(SERVDIR.'/cdata/replaces.php');
    unset($replaces[0]);
    
    echo proc_tpl('tools/replace/index', array('replaces' => implode('', $replaces), 'result' => $result, 'CSRF' => $CSRF));
    echofooter();
}
elseif ($action == 'xfields')
{
    if ($do == 'submit')
    {
        CSRFCheck();

        // load post data
        list($name, $vis, $add_name, $add_vis) = GET('name,vis,add_name,add_vis');

        // check name
        if (preg_match('/^[a-z0-9_]+$/i', $add_name) == false && !empty($add_name))
            msg('error', lang('Error!'), lang('Name may consist only letters and digits!'), '#GOBACK');

        if (!empty($add_name) && empty($add_vis))
            msg('error', lang('Error!'), lang('Please enter field "name for admin panel"'), '#GOBACK');

        // set optional flag and refresh vis name
        if (!is_array($name)) $name = array();
        foreach ($name as $v)
        {
            if ( isset($optional[$v]) && $optional[$v] == 'Y')
                 $cfg['more_fields'][$v] = '&'.$vis[$v];
            else $cfg['more_fields'][$v] = $vis[$v];
        }

        // delete from config
        foreach ($name as $v)
            if ( isset($remove[$v]) && $remove[$v] == 'Y')
                unset($cfg['more_fields'][$v]);

        // add new field
        if ($add_name && $add_vis) $cfg['more_fields'][$add_name] = $add_vis;

        // save
        $fx = fopen(SERVDIR.'/cdata/conf.php', 'w');
        fwrite($fx, "<?php die(); ?>\n" . serialize($cfg) );
        fclose($fx);

        msg('info', lang('Saved'), lang('Config successfully saved'), false, make_breadcrumbs('main/options/tools:xfields=More fields', true));
    }

    $CSRF = CSRFMake();
    echoheader('options', lang('Additional fields'), make_breadcrumbs('main/options/tools:xfields='.lang('Additional fields'), false));
    
    $xfields = array();
    foreach ($cfg['more_fields'] as $i => $v)
    {
        if ( substr($v, 0, 1) == '&' )
             $xfields[] = array( $i, substr($v, 1), 'checked="checked"' );
        else $xfields[] = array( $i, $v, '' );
    }

    echo proc_tpl('tools/xfields/index', array('xfields' => $xfields, 'CSRF' => $CSRF));
    echofooter();
}
elseif ($action == 'language')
{
    if ( !empty($_REQUEST['language']) )
    {
        CSRFCheck();

        $lx = fopen(SERVDIR.'/cdata/language.php', 'w');
        fwrite($lx, "<?php\n");

        foreach ($_REQUEST['language'] as $ks => $vs)
        {
            fwrite($lx, '$lang["'.$ks.'"] = "'.str_replace('"', '\"', $vs).'";'."\n");
        }
        fclose($lx);

        // update new language file
        include (SERVDIR.'/cdata/language.php');
    }

    $CSRF = CSRFMake();
    echoheader('options', lang('Customize your language'), make_breadcrumbs('main/options/tools:language='.lang('Language'), false));

    $langprepared = array();
    foreach ($lang as $i => $v) $langprepared[] = array($i, $v,ucfirst($i));

    echo proc_tpl('tools/lang/index', array("lang" => $langprepared, 'CSRF' => $CSRF));
    echofooter();
}
elseif ($action == 'plugins')
{
    $error = false;
    $urlpath = $_POST['urlpath'];

    if ($do == 'upload')
    {
        CSRFCheck();
        if (!empty($_FILES['file']) && $_FILES['file']['name'])
        {
            if ( !move_uploaded_file($_FILES['file']['tmp_name'], SERVDIR.'/cdata/plugins/'.$_FILES['file']['name']) )
            {
                $error = lang('File not uploaded');
            }
            $urlpath = false;
        }
        elseif ($urlpath && (preg_match('~(\w+)\.plg$~i', $urlpath, $filename)))
        {
            $r = fopen($urlpath, 'r');
            ob_start(); fpassthru($r); $file = ob_get_clean();

            $w = fopen(SERVDIR.'/cdata/plugins/'.$filename[1].'.php', 'w');
            fwrite($w, $file);
            fclose($w);
        }
        else $error = lang('File empty');
    }
    elseif ($do == 'uninstall')
    {
        CSRFCheck();
        unlink(SERVDIR.'/cdata/plugins/'.$name.'.php');
        if(is_dir($name))
            del_dir(SERVDIR.'/cdata/plugins/'.$name);
    }

    $CSRF = CSRFMake();
    echoheader('home', lang("Install plugins"), make_breadcrumbs('main=main/options:options=options/tools:plugins=Plugins', true));

    $list = array();
    foreach (read_dir(SERVDIR.'/cdata/plugins', array(), false) as $plugin)
    {
        if (preg_match('~\.php$~i', $plugin))
        {
            $r = fopen(SERVDIR.$plugin, 'r');
            $description = '-';
            fgets($r);
            if (preg_match('~// description: (.*)~i', fgets($r), $match))
                $description = $match[1];

            fclose($r);

            $list[] = array
            (
                'name' => str_replace( array('/cdata/plugins/', '.php'), '', $plugin),
                'path' => $plugin,
                'desc' => $description
            );
        }
    }

    echo proc_tpl('plugins/list');

    echofooter();
}
elseif ($action == 'rewrite')
{
    if ($subaction == 'save')
    {
        $w = fopen(SERVDIR.'/cdata/conf_rw.php', 'w');
        flock($w, LOCK_EX);
        fwrite($w, '<'."?php\n");
        foreach ($_REQUEST as $i => $v)
            if (substr($i, 0, 5) == 'conf_')
                fwrite( $w, '$conf_rw_'.substr($i, 5).' = "'.str_replace('"', '\"', $v) . "\";\n" );
        flock($w, LOCK_UN);
        fclose($w);

        $saved_ok = getpart('saved_ok');
    }

    // Read data from datatable
    if (file_exists(SERVDIR.'/cdata/conf_rw.php'))
        include ( SERVDIR.'/cdata/conf_rw.php' );

    // Default values -----------------
    set_default_val_for_rewrite();

    hook('insert_additional_rewrites');

    // Try to update htaccess
    if ($update_htaccess == 'Y')
    {
        $w = fopen($conf_rw_htaccess, 'w');
        flock($w, LOCK_EX);
        fwrite($w, "RewriteEngine ON\n");
        fwrite($w, "RewriteCond %{REQUEST_FILENAME} !-d\n");
        fwrite($w, "RewriteCond %{REQUEST_FILENAME} !-f\n");
        fwrite($w, "RewriteRule ^(.*)\$ /cn_friendly_url.php?rew=\$1&%{QUERY_STRING}[L]\n");
        flock($w, LOCK_UN);
        fclose($w);
    }

    // view template
    echoheader('home', lang('URL Rewrite Manager'), make_breadcrumbs('main=main/options:options=options/tools:rewrite=Rewrite Manager', true));
    echo proc_tpl('tools/rewrites/index');
    echofooter();
}

hook('tools_additional_actions');