<?php

    $NotHeaders = true;
    require_once ('core/init.php');
    include ('core/loadenv.php');

    // plugin tells us: he is fork, stop
    if ( hook('fork_search', false) ) return;

    // Check including
    $Uri = '//'.dirname( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
    if (strpos($config_http_script_dir, $Uri) !== false && strpos($PHP_SELF, 'search.php') !== false)
        die_stat(403, 'Wrong including search.php! Check manual to get more information about this issue.');

    // Autodate
    if ( empty($from_date_day) )   $from_date_day = intval(date('d'));
    if ( empty($from_date_month) ) $from_date_month = date('m');
    if ( empty($from_date_year) )  $from_date_year = 2003;

    if ( empty($to_date_day) )     $to_date_day = intval(date('d', time() + 3600*24));
    if ( empty($to_date_month) )   $to_date_month = date('m');
    if ( empty($to_date_year) )    $to_date_year = date('Y');

    $files_arch = array();

    // check for bad _GET and _POST
    $user_post_query    = cute_query_string($QUERY_STRING, array("archives", "start_from", "archive", "subaction", "id", "cnshow", "ucat", "dosearch", "story", "title", "user", "from_date_day", "from_date_month", "from_date_year", "to_date_day", "to_date_month", "to_date_year"), "post");

    $date_from  = mktime(0, 0, 0, intval($from_date_month), intval($from_date_day), intval($from_date_year));
    $date_to    = mktime(0, 0, 0, intval($to_date_month), intval($to_date_day), intval($to_date_year));

    if ( empty($search_form_hide) || isset($search_form_hide) && empty($dosearch) )
    {
        // Make parameters -----------------------------------------------------------------------------------------------------
        list($day_from, $month_from, $year_from) = make_postponed_date($date_from);
        list($day_to,   $month_to,   $year_to)   = make_postponed_date($date_to);

        $selected_search_arch = empty($archives) ? false : "checked='checked'";

        $story  = htmlspecialchars( urldecode($story) );
        $title  = htmlspecialchars( urldecode($title) );
        $author = htmlspecialchars( urldecode($author) );

        $hide = ($title or $author or !empty($archives) ) ? false: true;
        echo proc_tpl('search');
    }

    // Do Search -------------------------------------------------------------------------------------------------------
    if ($dosearch == "yes")
    {
        $mc_start = microtime(true);

        // In active news anyway
        $listing = array( time() => '/cdata/news.txt' );

        // Also, search in archive if present (sort it)
        if ( !empty($archives) )
        {
            $dir = read_dir(SERVDIR.'/cdata/archives');
            foreach ($dir as $vs)
                if (preg_match('~(\d+)\.news\.arch$~i', $vs, $c))
                    $listing[ $c[1] ] = $vs;

        }
        krsort($listing);

        // Init searching
        $preg_story  = '[^\|]*';
        $preg_author = '[^\|]*';
        $preg_title  = '[^\|]*';

        if ( !empty($user) )  $preg_author = '.*?('.preg_replace('/\s/', '|', preg_sanitize($user)).')[^\|]*';
        if ( !empty($title) ) $preg_title  = '.*?('.preg_replace('/\s/', '|', preg_sanitize($title)).')[^\|]*';
        if ( !empty($story) ) $preg_story  = '.*?('.preg_replace('/\s/', '|', preg_sanitize($story)).')[^\|]*';

        // Search in files
        $found = array();
        foreach ($listing as $id => $newsfile)
        {
            // Old archives do not touch
            if ($id && ($id < $date_from) ) break;

            $news = join('', file(SERVDIR . $newsfile));
            $strs = '~^\d+\|'.$preg_author.'\|'.$preg_title.'\|'.$preg_story.'\|.*$~im';

            if ( preg_match_all($strs, $news, $c, PREG_SET_ORDER) )
            {
                foreach ($c as $a => $b)
                {
                    $item = explode("|", $b[0]);
                    if ($item[NEW_ID] < $date_from or $item[NEW_ID] > $date_to)
                        continue;

                    // Actually in story?
                    if (!preg_match("~$preg_story~i", $item[NEW_SHORT]) and !preg_match("~$preg_story~i", $item[NEW_FULL]))
                        continue;

                    // Actual search result?
                    if (preg_match("/$preg_author/", $item[1]) == 0 ||
                        preg_match("/$preg_title/", $item[2]) == 0 ||
                        preg_match("/$preg_story/", $item[3]) == 0)
                        continue;

                    $found[] = array
                    (
                        'id' => $item[NEW_ID],
                        'src' => $newsfile,
                        'title' => $item[NEW_TITLE],
                        'cat' => $item[NEW_CAT]
                    );
                }
            }
        }

        if (count($found))
        {
            $itemid = 0;

            // Show results
            foreach ($found as $i => $resline)
            {
                $itemid++;
                if ($start_from > $itemid) continue;

                $id    = $resline['id'];
                $title = $resline['title'];
                $title = $config_useutf8? UTF8ToEntities( $title ) : $title;
                $ucat  = $resline['cat'];
                $archive = 0;

                if (preg_match('~(\d+)\.news\.arch$~i', $resline['src'], $arc)) $archive = $arc[1];

                $URL = $PHP_SELF . build_uri('subaction,id,archive,ucat', array('showfull'));
                $url = RWU( 'readmore', $URL );
                echo "<div class='cutenews_search_item'>$itemid <b><a href='$url'>$title</a></b> (". date("d F, Y", $id) .")</div>";
            }

            echo "<p class='cutenews_founded'><b>".lang('News articles found')." [". count($found)."]</b> ";
            echo str_replace(array('%1','%2'), array( date("d F Y", $date_from), date("d F Y", $date_to)), lang("from <b>%1</b> to <b>%2</b></p>", 'search'));
        }
        else echo "<div class='cutenews_not_match'>".lang('There are no news articles matching your search criteria')."</div>";

        echo '<div class="cutenews_search_results"><i>'.lang('Search performed for').' '.round(microtime(true) - $mc_start, 4).' s.</i></div>';
    }

    // if user wants to search
    elseif ( ($misc == "search") and ($subaction == "showfull" or $subaction == "showcomments" or $_POST["subaction"] == "addcomment" or $subaction == "addcomment"))
    {
        require_once(SERVDIR."/show_news.php");
        unset ($action, $subaction);
    }

    unset($search_form_hide, $dosearch);
