<?php

    if (!defined('INIT_INSTANCE')) die('Access restricted');

    $comm_per_page          = $config_comments_per_page;
    $template_comment       = stripslashes($template_comment);
    $total_comments         = 0;
    $showed_comments        = 0;
    $comment_number         = 0;
    $showed                 = 0;

    $all_comments = file( $comm_file );
    foreach ($all_comments as $comment_line)
    {
        $comment_line       = trim($comment_line);
        $comment_line_arr   = explode("|>|", $comment_line);

        if ($id == $comment_line_arr[COM_ID])
        {
            $individual_comments = explode("||", $comment_line_arr[COM_USER]);
            $total_comments = count($individual_comments) - 1;

            $iteration = 0;
            if ($config_reverse_comments == "yes")
            {
                $iteration = count($individual_comments) + 1;
                $individual_comments = array_reverse($individual_comments);
            }

            foreach ($individual_comments as $comment)
            {
                $iteration = ($config_reverse_comments == "yes") ? $iteration-1 : $iteration + 1;

                $comment_arr = explode("|", $comment);
                if ($comment_arr[COM_ID] != "")
                {
                    if (isset($comm_start_from) and $comm_start_from)
                    {
                        if ($comment_number < $comm_start_from)
                        {
                            $comment_number++;
                            continue;
                        }
                        elseif ($showed_comments == $comm_per_page) break;
                    }

                    $comment_number ++;
                    $comment_arr[COM_TEXT] = stripslashes(rtrim($comment_arr[COM_TEXT]));

                    if ($comment_arr[COM_MAIL] != "none")
                    {
                        $mail_or_url = false;
                        if ( check_email($comment_arr[COM_MAIL]) )
                        {
                            $url_target = "";
                            $mail_or_url = "mailto:";
                        }
                        else
                        {
                            $url_target = 'target="_blank"';
                            $mail_or_url = "";
                            if (substr($comment_arr[COM_MAIL],0,3) == "www") $mail_or_url = "http://";
                        }
                        $output = str_replace("{author}", "<a $url_target href=\"$mail_or_url".stripslashes($comment_arr[COM_MAIL])."\">".stripslashes(UTF8ToEntities($comment_arr[1]))."</a>", $template_comment);

                    }
                    else
                    {
                        $output = str_replace("{author}", UTF8ToEntities($comment_arr[COM_USER]), $template_comment);
                    }

                    $comment_arr[COM_TEXT] = preg_replace("/\b((http(s?):\/\/)|(www\.))([\w\.]+)([&-~\%\/\w+\.-?]+)\b/i", "<a href=\"http$3://$4$5$6\" target=\"_blank\">$2$4$5$6</a>", $comment_arr[COM_TEXT]);
                    $comment_arr[COM_TEXT] = preg_replace("/([\w\.]+)(@)([-\w\.]+)/i", "<a href=\"mailto:$0\">$0</a>", $comment_arr[COM_TEXT]);

                    $output         = str_replace("{mail}", $comment_arr[2], $output);
                    $output         = str_replace("{date}", date($config_timestamp_comment, $comment_arr[0]),$output);
                    $output         = embedateformat($news_arr[0], $output);

                    $output         = str_replace("{comment-id}", $comment_arr[0], $output);
                    $output         = str_replace("{comment}", "<a name=\"".$comment_arr[0]."\"></a>".UTF8ToEntities($comment_arr[4]), $output);
                    $output         = str_replace("{comment-iteration}", $iteration ,$output);

                    $output         = replace_comment("show", $output);

                    echo $output;

                    $showed_comments++;
                    if ($comm_per_page != 0 and $comm_per_page == $showed_comments) break;
                }
            }
        }
    }

    //----------------------------------
    // Prepare the Comment Pagination
    //----------------------------------

    $prev_next_msg = $template_comments_prev_next;

    // Previous link
    if ($comm_start_from)
    {
        $prev = $comm_start_from - $comm_per_page;
//        $URL = $PHP_SELF . build_uri('subaction,comm_start_from,id,ucat', array('showcomments', $prev));
        if ($archive)
            $URL = RWU( 'archcommpage', $PHP_SELF . build_uri('subaction,comm_start_from,title,archive,id,ucat', array('showcomments', $prev, titleToUrl($news_arr[NEW_TITLE]))) );
        else
            $URL = RWU( 'commpage', $PHP_SELF . build_uri('subaction,comm_start_from,title,id,ucat', array('showcomments', $prev,titleToUrl($news_arr[NEW_TITLE]))) );
        $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"{$URL}\">\\1</a>", $prev_next_msg);
    }
    else
    {
        $prev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "\\1", $prev_next_msg);
        $no_prev = TRUE;
    }

    // Pages
    if ($comm_per_page)
    {
        $pages_count        = ceil($total_comments / $comm_per_page);
        $pages_start_from   = 0;
        $pages              = "";

        for ($j=1; $j <= $pages_count; $j++)
        {
            if ($pages_start_from != $comm_start_from)
            {
//                $URL = $PHP_SELF . build_uri('subaction,comm_start_from,archive,id,ucat', array('showcomments', $pages_start_from));
                if ($archive)
                    $URL = RWU( 'archcommpage', $PHP_SELF . build_uri('subaction,comm_start_from,title,archive,id,ucat', array('showcomments', $pages_start_from,titleToUrl($news_arr[NEW_TITLE]))) );
                else
                    $URL = RWU( 'commpage', $PHP_SELF . build_uri('subaction,comm_start_from,title,id,ucat', array('showcomments', $pages_start_from,titleToUrl($news_arr[NEW_TITLE]))) );
                $pages .= '<a href="'.$URL.'">'.$j.'</a> ';
            }
            else
            {
                $pages .= ' <strong>'.$j.'</strong> ';
            }

            $pages_start_from += $comm_per_page;
        }

        $prev_next_msg = str_replace("{pages}", $pages, $prev_next_msg);
    }

    // Next link
    if ($comm_per_page < $total_comments and $comment_number < $total_comments)
    {
//        $URL = $PHP_SELF . build_uri('subaction,comm_start_from,archive,id,ucat', array('showcomments', $comment_number));
        if($archive)
            $URL = RWU( 'archcommpage', $PHP_SELF . build_uri('subaction,comm_start_from,title,archive,id,ucat', array('showcomments', $comment_number,titleToUrl($news_arr[NEW_TITLE]))) );
        else
            $URL = RWU( 'commpage', $PHP_SELF . build_uri('subaction,comm_start_from,title,id,ucat', array('showcomments', $comment_number,titleToUrl($news_arr[NEW_TITLE]))) );
        $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"$URL\">\\1</a>", $prev_next_msg);
    }
    else
    {
        $prev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "\\1", $prev_next_msg);
        $no_next = true;
    }

    if (empty($no_prev) or empty($no_next)) echo $prev_next_msg;

    $username = $usermail = false;
    $template_form = str_replace("{config_http_script_dir}", $config_http_script_dir, $template_form);

    //----------------------------------
    // Check if the remember script exists
    //----------------------------------
    if ( !empty($_SESS['user']) )
    {
        $captcha_enabled = false;
        $member_db = user_search($_SESS['user']);
    }

    $template_form = str_replace('{username}', (isset($member_db[UDB_NAME]) ? $member_db[UDB_NAME] : false), $template_form);
    $template_form = str_replace('{usermail}', (isset($member_db[UDB_EMAIL]) ? $member_db[UDB_EMAIL] : false), $template_form);

    // Remember and Forget for unregistered only
    $remember_user = '';
    $remember_form = getpart('remember_me');

    if ($member_db)
    {
        $remember_form = getpart('logged_as_member');
        $remember_user = getpart('logger_as_membersp', htmlspecialchars( $member_db[UDB_NAME] ), htmlspecialchars($member_db[UDB_EMAIL]));
    }
    elseif ($_COOKIE['CNname'])
    {
        $remember_form = getpart('forget_me');
    }

    $gduse         = function_exists('imagecreatetruecolor')? 0 : 1;
    $captcha_form  = $config_use_captcha && $captcha_enabled ? proc_tpl('captcha_comments') : false;
    $smilies_form  = proc_tpl('remember_js') . insertSmilies('short', false);
    $template_form = str_replace("{smilies}", $smilies_form, $template_form);
    $template_form = str_replace('{remember_me}', $remember_form, $template_form);
    $template_form = hook('comment_template_form', $template_form);
    $remember_js   = read_tpl('remember') . $remember_user;

    echo proc_tpl('comment_form');

    return TRUE;