<?php

    if (!defined('INIT_INSTANCE')) die('Access restricted');

    $all_active_news = file($news_file);
    foreach ($all_active_news as $active_news)
    {
        $news_arr = explode("|", $active_news);
        if ($news_arr[NEW_ID] == $id and (empty($category) or $is_in_category))
        {
            $found       = true;
            $output      = template_replacer_news($news_arr, $template_full);
            $output      = hook('replace_fullstory', $output);
            $output      = UTF8ToEntities($output);

            echo $output;
        }
    }

    // Article ID was not found, if we have not specified an archive -> try to find the article in some archive.
    // Auto-Find ID In archives
    //----------------------------------------------------------------------
    if (!$found)
    {
        echo '<a id="com_form"></a>';

        if (!$archive or $archive == '')
        {
            // Get all archives. (if any) and fit our lost id in the most propper archive.
            $lost_id        = $id;
            $all_archives   = false;
            $hope_archive   = false;

            if (!$handle = opendir(SERVDIR."/cdata/archives")) echo ("<!-- ".lang('cannot open directory')." ".SERVDIR."/cdata/archives --> ");

            while (false !== ($file = readdir($handle)))
            {
                if ($file != "." and $file != ".." and !is_dir(SERVDIR."/cdata/archives/$file") and substr($file, -9) == 'news.arch')
                {
                    $file_arr = explode(".", $file);
                    $all_archives[] = $file_arr[0];
                }
            }
            closedir($handle);

            if ($all_archives)
            {
                sort($all_archives);
                if (isset($all_archives[1]))
                {
                    foreach($all_archives as $this_archive) if ($this_archive > $lost_id) { $hope_archive = $this_archive; break; }
                }
                elseif ($all_archives[0] > $lost_id)
                {
                    $hope_archive = $all_archives[0];
                    return FALSE;
                }
            }
        }

        if ($hope_archive)
        {
            $URL = $PHP_SELF.build_uri('archive,start_from,ucat,subaction,id', array($hope_archive));
            echo '<div>'.lang('You are now being redirected to the article in our archives, if the redirection fails, please').' <a href="'.$URL.'">'.lang('click here').'</a></div>
                    <script type="text/javascript">window.location="'.str_replace('&amp;', '&', $URL).'";</script>';
        }
        else
        {
            echo '<div style="text-align: center;">'.lang('Cannot find an article with id').': <strong>'. (int)htmlspecialchars($id).'</strong></div>';
        }
        return FALSE;
    }

    return TRUE;