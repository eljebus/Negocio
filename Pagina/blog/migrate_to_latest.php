<?php
/***************************************************************************
CuteNews CutePHP.com
Copyright (с) 2012-2013 Cutenews Team
 ****************************************************************************/
/**
 Migrate script from UTF8 http://www.korn19.ch/coding/utf8-cutenews/ to http://cutephp.com
 * (data/ to cdata/)
 */

if (!isset($_POST['oldpath']))
{
?>
<div style="width: 500px; background: none repeat scroll 0px 0px rgb(255, 255, 255); height: 200px; position: fixed; left: 50%; top: 50%; margin-top: -100px; margin-left: -250px; text-align: center; border-radius: 20px 20px 20px 20px; font-family: Verdana,Arial,Helvetica,sans-serif; border: 1px solid #A7A6B4;">
    <h2 style="font-size: 20px; margin: 16px 0 0 0;">Migration to the latest CuteNews</h2>
    <hr style="border: 2px dashed #A0A0A0; border-top: none;">
    <form style="position: relative; top: 20px;" method="POST">
        <p>Enter the path to the old project (ex: /var/www/cutenews):</p>
        <input type="text" name="oldpath">
        <input type="submit" value="Migrate">
        <p style="font-size: 14px; font-style: italic; margin: 8px 0px 4px;"><span style="color: red;">Notice:</span> It also works with cutenews 1.4.7 to 1.5.2.</p>
        <p style="font-size: 14px; font-style: italic; margin: 4px 0px;">Create a backup, just in case.</p>
    </form>
</div>

<?php
}
else
{
    include 'core/init.php';
    include 'core/loadenv.php';


    define('SERVDIR', dirname(__FILE__));
    define('OLDDIR', trim($_POST['oldpath']));

    function migrate_news($path)
    {
        //changed fields in news include: archive, backup, postponed, unapproved
        $news_old = file(OLDDIR.$path);
        $new_path = preg_replace('#^/data/#i', '', $path);
        $nf = fopen(SERVDIR.'/cdata/'.$new_path, 'w');
        foreach($news_old as $news_line)
        {
            $news_part = explode('|', $news_line);
            $news_id = $news_part[0];
            $news_user = $news_part[1];
            $news_title = $news_part[2];
            $news_short = $news_part[3];
            $news_full = $news_part[4];
            $news_avatar = $news_part[5];
            $news_category = $news_part[6];
            $news_rate = '';
            $news_mf = '';
            $news_opt = '';
            fwrite($nf, "$news_id|$news_user|$news_title|$news_short|$news_full|$news_avatar|$news_category|$news_rate|$news_mf|$news_opt|\n");
        }
        fclose($nf);
    }

    function make_config_arr_from_file($configfile)
    {
        include($configfile);
        $config_arr = get_defined_vars();
        array_shift($config_arr);
        return $config_arr;
    }

    function migrate_config()
    {
        $config_old = make_config_arr_from_file(OLDDIR.'/data/config.php');
        $config_new = make_config_arr_from_file(SERVDIR.'/cdata/config.php');

        $nf = fopen(SERVDIR.'/cdata/config.php', 'w+');
        fwrite($nf, "<?php \r\n\r\n//System Configurations (Auto Generated file)\r\n");
        foreach($config_new as $opt => $val)
        {
            if($opt == 'config_ban_attempts')
                $config_new[$opt] = (isset($config_old['config_login_ban'])) ? $config_old['config_login_ban'] : '';
            if(array_key_exists($opt, $config_old))
                $config_new[$opt] = $config_old[$opt];
            fwrite($nf, '$'.$opt.' = "'.$config_new[$opt]."\";\r\n");
        }
        fwrite($nf, '?>');
        fclose($nf);
    }


    function migrate_templates($path)
    {
        $template = file_get_contents(OLDDIR.$path);
        $rep_pos = strpos($template, '$template_comment');
        preg_match('|\$template_comment[^$]+|', $template, $replaceable);
        $rep_len = strlen($replaceable[0]);
        $replaced = str_replace('{author-name}', '{author}', $replaceable[0]);
        $new_template = substr($template, 0, $rep_pos).$replaced.substr($template, $rep_pos + $rep_len);
        file_put_contents(SERVDIR.'/cdata/'.pathinfo($path, PATHINFO_BASENAME), $new_template);
    }


    $fail = array();
    if (!is_dir(SERVDIR.'/cdata'))
        die("Not found `cdata` folder <a href='index.php'>Back</a>");

    $data_dir = array();
    if (is_dir(OLDDIR.'/data'))
        $data_dir = read_dir(OLDDIR.'/data', array(), true, OLDDIR);
    else
        $fail[] = array('Folder not found', OLDDIR.'/data');

    if (!is_dir(SERVDIR.'/uploads'))
    {
        if (!mkdir(SERVDIR.'/uploads', 0777)) $fail[] = array('Cannot create the folder', SERVDIR.'/uploads');
        $x = fopen(SERVDIR.'/uploads/index.html', 'w');
        fwrite($x, 'Access denied');
        fclose($x);
    }

    if (!is_dir(SERVDIR.'/skins'))
    {
        if (!mkdir(SERVDIR.'/skins', 0777))
            $fail[] = array('Cannot create the folder', SERVDIR.'/skins');
        else
            if (!mkdir(SERVDIR.'/skins/emoticons', 0777))
                $fail[] = array('Cannot create the folder', SERVDIR.'/skins/emoticons');
    }

    foreach ($data_dir as $fn)
    {
        if (preg_match('#news\.(?:txt|arch)$#i', $fn) > 0)
        {
            migrate_news($fn);
            continue;
        }

        if (stripos($fn, '/data/upimages') !== false)
        {
            $dest = SERVDIR.str_replace('/data/upimages/', '/uploads/', $fn);
            if (!copy(OLDDIR.$fn, $dest))
                $fail[] = array('Cannot copy the file', OLDDIR.$fn, $dest);
            continue;
        }

        if (stripos($fn, '/data/emoticons') !== false)
        {
            $dest = SERVDIR.str_replace('/data/emoticons/', '/skins/emoticons/', $fn);
            if (!copy(OLDDIR.$fn, $dest))
                $fail[] = array('Cannot copy the file', OLDDIR.$fn, $dest);
            continue;
        }

        if (stripos($fn, '/data/config.php') !== false)
        {
            migrate_config();
            continue;
        }

        if (pathinfo($fn, PATHINFO_EXTENSION) === 'tpl')
        {
            migrate_templates($fn);
            continue;
        }

        if (stripos($fn, '.htaccess') !== false) continue;

        $path = SERVDIR.'/cdata';
        foreach (explode('/',  str_ireplace('/data/', '', $fn)) as $dc)
        {
            $path .= '/'.$dc;
            if (strpos($dc, '.') === false)
            {
                if (!is_dir($path) && !mkdir($path, 0777))
                    $fail[] = array('Cannot create the folder', $path);
                else
                    chmod($path, 0777);
            }
            else
            {
                if (!copy(OLDDIR.$fn, $path))
                    $fail[] = array('Cannot copy the file', OLDDIR.$fn, $path);

                if (!chmod($path, 0666))
                    $fail[] = array('Cannot change file mode', $path);
            }
        }
    }

//migrate skins
    $skins_dir = array();
    if (is_dir(OLDDIR.'/skins'))
        $skins_dir = read_dir(OLDDIR.'/skins', array(), true, OLDDIR);
    else
        $fail[] = array('Folder not found', OLDDIR.'/skins');

    foreach ($skins_dir as $resourse)
    {
        if (stripos($resourse, '/skins/images/') !== false
            || preg_match('/(?<!default|compact|simple)\.skin\.php$/i', $resourse) > 0)
        {
            if (!copy(OLDDIR.$resourse, SERVDIR.$resourse))
                $fail[] = array('Cannot copy the file', OLDDIR.$resourse, SERVDIR.$resourse);
        }
    }

    // Place .htaccess to cdata section
    $w = fopen(SERVDIR.'/cdata/.htaccess', 'w');
    fwrite($w, "Deny From All");
    chmod (SERVDIR.'/cdata/.htaccess', 0644);
    fclose($w);

    $found_problems = proc_tpl('install/problemlist');
    msg('info', lang('Migration success'), lang("Congrats! You migrated to Cutenews ".VERSION). " | <a href='index.php'>Login</a> ".$found_problems);
}
