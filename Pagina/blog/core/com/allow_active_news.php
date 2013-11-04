<?php

    if (!defined('INIT_INSTANCE')) die('Access restricted');

    $in_use         = 0;
    $used_archives  = array();
    $all_news       = file($news_file);

    if ($reverse == true) $all_news = array_reverse($all_news);
    if ($orderby == 'R')  shuffle($all_news);
    elseif ($orderby) $all_news = quicksort($all_news, $orderby);

    // Search last comments
    if ( !empty($sortbylastcom) )
    {
        $garnews = array();
        foreach ($all_news as $nl) { list ($id) = explode('|', $nl, 2); $garnews[$id] = $nl; }
        $all_news = array();

        $all_comments = file($comm_file);
        $all_comments = preg_replace('~^(\d+)\|>\|((\d+)\|.*?\|.*?\|.*?\|.*?\|.*?\|)*~im', '\\3.\\1', $all_comments);
        arsort($all_comments);
        foreach ($all_comments as $pm) if ( $nl = rtrim($garnews[ (int)(substr($pm, strpos($pm, '.') + 1)) ]) ) $all_news[] = $nl;
    }

    $count_all = 0;
    $all_news = hook('news_reorder', $all_news);

    if (isset($category) and $category)
    {
        foreach ($all_news as $news_line)
        {
            $news_arr = explode("|", $news_line);
            $is_in_cat = false;
            if (strstr($news_arr[NEW_CAT], ','))
            {
                // if the article is in multiple categories
                $this_cats_arr = spsep($news_arr[NEW_CAT]);
                foreach ($this_cats_arr as $this_single_cat)
                {
                    if (isset($requested_cats[$this_single_cat]) && isset($requested_cats[$this_single_cat])) $is_in_cat = TRUE;
                }
            }
            elseif (isset($requested_cats[$news_arr[NEW_CAT]]) && isset($requested_cats[$news_arr[NEW_CAT]])) $is_in_cat = TRUE;

            if ($is_in_cat) $count_all++; else continue;
        }
    }
    else $count_all = count($all_news);

    $i              = 0;
    $showed         = 0;
    $repeat         = true;
    $url_archive    = $archive;

    while ($repeat)
    {
        foreach ($all_news as $news_line)
        {
            $is_in_cat = false;
            $news_arr = explode("|", $news_line);

            // Prospected news not showing
            if ($news_arr[NEW_ID] > time() + $config_date_adjust*60) continue;

            if (strstr($news_arr[NEW_CAT], ','))
            {
                // if the article is in multiple categories
                $this_cats_arr = spsep($news_arr[NEW_CAT]);
                foreach ($this_cats_arr as $this_single_cat)
                {
                    if (isset($requested_cats[$this_single_cat]) && isset($requested_cats[$this_single_cat])) $is_in_cat = true;
                }

            }
            elseif (isset($requested_cats[$news_arr[NEW_CAT]]) && isset($requested_cats[$news_arr[NEW_CAT]])) $is_in_cat = true;

            // if User_By, show news only for this user
            if ( !empty($user_by) && $user_by != $news_arr[NEW_USER]) { $count_all--; continue; }

            if (!$is_in_cat and isset($category) and $category) continue;
            if ($start_from)
            {
                if ($i < $start_from)
                {
                    $i++;
                    continue;
                }
                elseif ($showed == $number) break;
            }

            // Basic replacements
            $output      = template_replacer_news($news_arr, $template_active);
            $output      = hook('replace_activenews', $output);
            $output      = UTF8ToEntities($output);
            echo $output;

            $i++;
            $showed++;

            // Includes for bottom of activenews
            echo hook('additional_include_activenews');

            if ($number and $number == $i) break;
        }

        // External archive $archive is already used
        $archives_arr = array();
        $used_archives[$archive] = true;

        // Archives Loop [IF $only_active = false]
        if ($i < $number and empty($only_active))
        {
            // get archives ids
            if (!$handle = opendir(SERVDIR . "/cdata/archives")) die_stat(false, '<div class="cutenews-warning">'.lang('cannot open directory').' '.SERVDIR.'/cdata/archives</div>');
            while (false !== ($file = readdir($handle)))
            {
                if ($file != "." and $file != ".." and substr($file, -9) == 'news.arch')
                {
                    list($archid) = explode(".", $file);
                    if (empty($used_archives[$archid])) $archives_arr[$archid] = $archid;
                }
            }
            closedir($handle);

            // get max archive id to show
            if (count($archives_arr) > 0)
                $in_use = max($archives_arr);
            else $in_use = false;

            if ( $in_use )
            {
                $archive                = $in_use;
                $all_news               = file(SERVDIR."/cdata/archives/$in_use.news.arch");
                $used_archives[$in_use] = true;
            }
            else $repeat = false;

        }
        else $repeat = false;
    }

    // Triggerred by $config_disable_pagination = TRUE
    if ($config_disable_pagination == 0)
    {
        // << Previous & Next >>
        $prev_next_msg = $template_prev_next;

        //----------------------------------
        // Previous link
        //----------------------------------
        if ($start_from)
        {
            $prev = $start_from - $number;
            $URL = $PHP_SELF . build_uri('start_from,ucat,archive,subaction,id:comm_start_from', array($prev, $ucat, $url_archive, $subaction, $id));
            $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", '<a href="'.RWU('newspage', $URL).'">\\1</a> ', $prev_next_msg);
        }
        else
        {
            $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "\\1", $prev_next_msg);
            $no_prev = true;
        }

        //----------------------------------
        // Pages
        //----------------------------------
        $pages = '';

        if ($number)
        {
            $pages_count        = ceil($count_all / $number);
            $pages_start_from   = 0;

            for($j=1; $j<= $pages_count; $j++)
            {
                if ( $pages_start_from != $start_from)
                {
                    $URL = $PHP_SELF . build_uri('start_from,ucat,archive,subaction,id:comm_start_from', array($pages_start_from,$ucat,$url_archive,$subaction,$id));
                    $pages .= '<a href="'.RWU('newspage', $URL).'">'.$j.'</a> ';
                }
                else $pages .= '<strong>'.$j.'</strong> ';
                $pages_start_from += $number;
            }
        }
        else
        {
            $no_next = true;
            $no_prev = true;
        }

        $prev_next_msg = str_replace("{pages}", $pages, $prev_next_msg);

        //----------------------------------
        // Next link  (typo here ... typo there... typos everywhere !)
        //----------------------------------
        if ($number < $count_all and $i < $count_all)
        {
            $URL = $PHP_SELF . build_uri('start_from,ucat,archive,subaction,id:comm_start_from', array($i, $ucat, $url_archive, $subaction, $id));
            $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", '<a href="'.RWU('newspage', $URL).'">\\1</a>', $prev_next_msg);
        }
        else
        {
            $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "\\1", $prev_next_msg);
            $no_next = TRUE;
        }

        if (!$no_prev or !$no_next) echo $prev_next_msg;

    }
?>
