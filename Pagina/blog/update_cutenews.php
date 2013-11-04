<?php

    define('SERVDIR', dirname(__FILE__));

    $error_msg = "You don't have permission for this section.";
    if (isset($_COOKIE) && !empty($_COOKIE['update']))
    {
        $update_temp = SERVDIR.'/cdata/update_temp.php';
        if (file_exists($update_temp))
        {
            include $update_temp;
            if ($update_key != $_COOKIE['update'])
                create_html_template($error_msg);
        }
        else
            create_html_template($error_msg);
    }
    else
        create_html_template($error_msg);


    // Takes content from remote addrs
    function cwget($url)
    {
        if ( ini_get('allow_url_fopen') )
        {
            if (substr(PHP_VERSION, 0, 5) >= '4.3.0')
            {
                $context = stream_context_create
                (
                    array('http' => array('method' => 'GET',
                        'user_agent'  => $_SERVER['HTTP_USER_AGENT'],
                        'ignore_errors' => true
                    ) )
                );

                $r = fopen( $url, 'r', false, $context );
            }
            else
            {
                $r = fopen( $url, 'r' );
            }

            ob_start();
            fpassthru($r);
            $rd = ob_get_clean();

            return $rd;
        }
        else if (function_exists('curl_init'))
        {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);
            return $result;
        }
        else
        {
//            echo 'use FTP.';
        }

        return false;
    }

    function delete_temp_update_data()
    {
        global $update_file, $update_file_source, $update_temp;

        if (file_exists($update_file))
            unlink($update_file);

        if (file_exists($update_file_source))
            unlink($update_file_source);

        if (file_exists($update_temp))
            unlink($update_temp);

        setcookie('update', '', time()-3600);
    }

    function create_html_template($text, $arr=null, $is_fails=false)
    {
        $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<title>Update status / CuteNews</title>';
        $html .= '</head>';
        $html .= '<body style="width: 800px; margin: 8px auto; font-family: Verdana,Arial,Helvetica,sans-serif; font-size: 12px;">';
        $html .= '<table width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #A7A6B4;border-radius: 0.8em 0.8em 0.8em 0.8em;">';
        $html .= '<tr><td>&nbsp</td></tr>';
        $html .= '<tr><td  style="border-bottom: 1px solid black;border-top: 1px solid black;"><table cellpadding=5 cellspacing=4 border="0">';
        $html .= '<tr><td><a class="nav" href="index.php?mod=main">Home</a></td><td>|</td>';
        $html .= '<td><a class="nav" href="index.php?mod=options&action=options">Options</a></td></tr></table></td></tr>';
        $html .= '<tr><td style="padding:20px;">'.$text.'</td></tr>';
        if($is_fails)
        {
            $html .= '<tr><td style="padding:20px;"><table><tr bgcolor="#FFFFC0"><th>Problem</th><th>Source</th></tr>';
            foreach($arr as $info)
            {
                list($msg, $path) = $info;
                $html .= '<tr><td>'.$msg.'</td><td>'.$path.'</td></tr>';
            }
            $html .= '</table></tr></td>';
        }
        else
        {
            if($arr != null)
            {
                $html .= '<tr><td style="padding-left:20px;"> Files was updated: <br>';
                foreach($arr as $info)
                    $html .= $info.'<br>';
                $html .= '</td>';
            }
        }
        $html .= '<tr><td style="padding:20px;">Return to the <a href="index.php?mod=main">main page</a></td></tr>';
        $html .= '</table>';
        $html .= '</body>';
        $html .= '</html>';

        die($html);
    }

    if(isset($_POST['action']))
        $action = $_POST['action'];

    if (isset($action) && $action == 'do_update')
    {
        if(isset($_POST['last_version']))
            $last_version = $_POST['last_version'];

        require SERVDIR."/core/zip.class.php";

        $start = time();
        $update_file_source = SERVDIR.'/cutenews_update_file.zip';
        $update_file = SERVDIR.'/cdata/log/revision.file.log';
        $try_get = 0;

        // Every 3 hour reloading
        if (file_exists($update_file) && ((time() - filemtime($update_file)) > 3*3600)) $try_get = 1;
        if (!file_exists($update_file)) $try_get = 1;

        // local cache update file too old or not exists - try to get new file
        if ($try_get)
        {
            $statext = cwget('http://cutephp.com/latest/.revision.log');
            $stat    = strlen($statext);

            if ($stat)
            {
                $w = fopen($update_file, 'w');
                fwrite($w, $statext);
                fclose($w);
                chmod($update_file, 0664);
            }
        }
        else
        {
            $r = fopen($update_file, 'r');
            ob_start();
            fpassthru($r);
            $statext = ob_get_clean();
            $stat    = strlen($statext);
        }

        if ($stat)
        {
            $rev = explode("\n", $statext);
            list(,$revid) = explode('=', array_shift($rev));

            // check files
            if (!function_exists('md5'))
                create_html_template("Function `md5_file` not found: update php version'");

            $hashes = array();
            foreach ($rev as $files)
            {
                if (empty($files))
                    continue;

                list ($hash_rec, $file) = explode('|', $files, 2);
                $hashes[$file] = $hash_rec;
            }

            $dn = cwget('https://github.com/CuteNews/cute-news-repo/archive/master.zip');
            if ($dn)
            {
                $w = fopen($update_file_source, 'w');
                if(!$w) create_html_template('Cannot upload the update file '.$update_file_source);
                if(!fwrite($w, $dn)) create_html_template('Cannot write the update file '.$update_file_source);
                fclose($w);
            }
            else
                create_html_template('Cannot download the update file: https://github.com/CuteNews/cute-news-repo/archive/master.zip');

            $zipfile = new zipfile;
            $zipfile->read_zip($update_file_source);

            foreach($zipfile->files as $filea)
            {
                $path = SERVDIR;
                $temp = substr($filea['dir'], 31);
                $pathtofile = (!empty($temp))? $temp.'/'.$filea['name'] : $filea['name'];

                if(array_key_exists($pathtofile, $hashes))
                {
                    if (file_exists($path.'/'.$pathtofile))
                         $hash_my = md5_file($path.'/'.$pathtofile);
                    else $hash_my = false;

                    if ($hash_my != $hashes[$pathtofile])
                    {
                        foreach (explode('/',  $pathtofile) as $dc)
                        {
                            $path .= '/'.$dc;
                            if (strpos($dc, '.') === false)
                            {
                                if (!is_dir($path) && !mkdir($path, 0777)) $fail[] = array('Cannot create the folder', $path); else @chmod($path, 0777);
                            }
                            else
                            {
                                $w = fopen($path, 'w');
                                if(!$w) $fail[] = array('Cannot open the file ', $path);
                                if(!fwrite($w, $filea['data'])) $fail[] = array('Cannot update the file', $path);
                                fclose($w);
                            }
                        }
                        $updated_files[] = $path;
                    }
                }
            }
            delete_temp_update_data();
        }
        else
            create_html_template('Update broken! Cannot download the update file. Please download the latest version with <a href="https://github.com/CuteNews/cute-news-repo/archive/master.zip">github</a> and rewrite all files by FTP');
        $end = time();

        if (isset($fail))
            create_html_template("Update broken!", $fail, true);
        else
            create_html_template("Update success ".($end-$start)." sec. You have the latest version: $last_version<br>", $updated_files, false);
    }
    else
        create_html_template($error_msg);
?>