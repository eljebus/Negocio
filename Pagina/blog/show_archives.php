<?PHP

    $NotHeaders = true;
    require_once('core/init.php');
    include ('core/loadenv.php');

    // plugin tells us: he is fork, stop
    if ( hook('fork_archives', false) ) return;

    // Check including
    $Uri = '//'.dirname( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
    if (strpos($config_http_script_dir, $Uri) !== false && strpos($PHP_SELF, 'show_archives.php') !== false) die_stat(403, 'Wrong including show_archives.php! Check manual to get more information about this issue.');

    // Prepare requested categories
    if (preg_match('/[a-z]/i', $category))
    {
        die_stat(false, "<b>Error</b>!<br>CuteNews has detected that you use \$category = \"".htmlspecialchars($category)."\"; but you can call the categories only with their <b>ID</b> numbers and not with names<br>example:<br><blockquote>&lt;?PHP<br>\$category = \"1\";<br>include(\"path/to/show_archives.php\");<br>?&gt;</blockquote>");
    }

    hook('show_archives_init');

    $category = preg_replace("/ /", "", $category);
    $tmp_cats_arr = spsep($category);
    foreach ($tmp_cats_arr as $key => $value)
    {
        if ($value != "") $requested_cats[$value] = true;
    }

    if (empty($archive))
    {
        $news_file = SERVDIR."/cdata/news.txt";
        $comm_file = SERVDIR."/cdata/comments.txt";
    }
    elseif (is_numeric($archive))
    {
        $news_file = SERVDIR."/cdata/archives/$archive.news.arch";
        $comm_file = SERVDIR."/cdata/archives/$archive.comments.arch";
    }
    else
    {
        die_stat(false, "Archive variable is invalid");
    }

    if ($subaction == "" or !isset($subaction))
    {
        $user_query = cute_query_string($QUERY_STRING, array("start_from", "archive", "subaction", "id", "ucat"));

        if(!$handle = opendir(SERVDIR."/cdata/archives"))
            die_stat(false, "Cannot open directory ".SERVDIR."/cdata/archives ");

        while (false !== ($file = readdir($handle)))
        {
            $file_arr = explode(".",$file);
            if($file != "." and $file != ".." and $file_arr[1] == "news")
                $arch_arr[] = $file_arr[0];

        }
        closedir($handle);

        if(is_array($arch_arr))
        {
            $arch_arr = array_reverse($arch_arr);
            foreach($arch_arr as $arch_file)
            {
                $news_lines         = file(SERVDIR."/cdata/archives/$arch_file.news.arch");
                $count              = count($news_lines);
                $last               = $count-1;
                $first_news_arr     = explode("|", $news_lines[$last]);
                $last_news_arr      = explode("|", $news_lines[0]);
                $first_timestamp    = $first_news_arr[0];
                $last_timestamp     = $last_news_arr[0];
                $arch_url = RWU( 'archread', $PHP_SELF . build_uri('subaction,archive', array('list-archive', $arch_file)) );
                echo "<a href=\"$arch_url&$user_query\">".date("d M Y",$first_timestamp) ." - ". date("d M Y",$last_timestamp).", (<b>$count</b>)</a><br />";
            }
        }
    }
    else
    {
        if ($CN_HALT != true and $static != true and ($subaction == "showcomments" or $subaction == "showfull" or $subaction == "addcomment") and ((!isset($category) or $category == "") or $requested_cats[$ucat] == true) )
        {
            if ($subaction == "addcomment")
            {
                $allow_add_comment  = true;
                $allow_comments     = true;
            }

            if ($subaction == "showcomments")
                $allow_comments = true;

            if (($subaction == "showcomments" or $allow_comments == true) and $config_show_full_with_comments == "yes")
                $allow_full_story = true;

            if ($subaction == "showfull")
                $allow_full_story = true;

            if ($subaction == "showfull" and $config_show_comments_with_full == "yes")
                $allow_comments = true;
        }
        else
        {
            if ($config_reverse_active == "yes")
                $reverse = true;

            $allow_active_news = true;
        }
        
        require( SERVDIR.'/inc/shows.inc.php');

    }
    unset($template, $requested_cats, $reverse, $in_use, $archive, $archives_arr, $number, $no_prev, $no_next, $i, $showed, $prev, $used_archives);
    unset($PHP_SELF, $QUERY_STRING, $user, $user_member);

?>
<!-- News Powered by CuteNews: http://cutephp.com/ -->
