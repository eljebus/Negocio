<?php

if (!defined('INIT_INSTANCE')) die('Access restricted');

if ($member_db[UDB_ACL] > 3)
    msg("error", lang("Access Denied"), lang("You don't have permission to add news"));

// only show allowed categories
list ($allowed_cats, $cat_lines, $cat) = get_allowed_cats($member_db);

// ON/OFF CKEditor
$use_wysiwyg = 0;
if ( $config_use_wysiwyg == 'ckeditor' && is_dir(SERVDIR.'/core/ckeditor') )
{
    $implemented_ckeditor_filemanager = hook('implement_file_browser', "
        filebrowserBrowseUrl:      '{$PHP_SELF}?&mod=images&action=quick&wysiwyg=true',
        filebrowserImageBrowseUrl: '{$PHP_SELF}?&mod=images&action=quick&wysiwyg=true'");

    $use_wysiwyg = 1;
}

// ---------------------------------------------------------------------------------------------------------------------
if ($action == "addnews")
{
    $error_messages = false;
    $preview = (REQ('preview') == 'preview') ? 'preview' : false;

    // ********************************************************************************
    // Do add News to news.txt
    // ********************************************************************************
    if ($subaction == 'doaddnews')
    {
        // Definition
        $pack = $options = false;

        // Format our categories variable
        if ( is_array($category) )
        {
            // User has selected multiple categories
            $nice_category = array();
            $ccount = 0;

            foreach ($category as $ckey => $cvalue)
            {
                if ( !in_array($cvalue, $allowed_cats) and isset($cat[$cvalue]) )
                     msg('error', lang('Error!'), lang('Not allowed category'), '#GOBACK');

                $nice_category[] = $cvalue;
            }
            $nice_category = implode(',', $nice_category);
        }
        else
        {
            // Single or Not category
            // don't format $nice_cats because we have not selected any.
            if ( $category && !in_array($category, $allowed_cats) and isset($cat[$category]) )
                 msg('error', lang('Error!'), lang('Not allowed category'), '#GOBACK');

            $nice_category = $category;
        }

        // --------------------------------------------------------------
        if ($member_db[UDB_ACL] == ACL_LEVEL_JOURNALIST or $postpone_draft == "draft")
        {
            // if the user is Journalist, add the article as unapproved
            $decide_news_file       = SERVDIR."/cdata/unapproved_news.txt";
            $added_time             = time() + $config_date_adjust*60;
            $postpone               = false;
            $unapproved_status_msg  = lang("The article was marked as Unapproved!");
        }
        elseif ($postpone_draft == "postpone")
        {
            if ( !preg_match("~^[0-9]{1,}$~", $from_date_hour) or !preg_match("~^[0-9]{1,}$~", $from_date_minutes) )
                $error_messages .= getpart('addnews_err', array( lang("You want to add a postponed article, but the hour format is invalid.") ));

            $postpone          = true;
            $added_time        = mktime($from_date_hour, $from_date_minutes, 0, $from_date_month, $from_date_day, $from_date_year);
            $decide_news_file  = SERVDIR."/cdata/postponed_news.txt";
        }
        else
        {
            $postpone          = false;
            $added_time        = time() + $config_date_adjust*60;
            $decide_news_file  = SERVDIR."/cdata/news.txt";
        }

        // HTML saved if force or use wysiwig
        if ($if_use_html == "yes" || $use_wysiwyg)
        {
            $use_html = true;
            $options = edit_option($options, 'use_html', true);
        }

        $full_story  = replace_news("add", $full_story, $use_html);
        $short_story = replace_news("add", $short_story, $use_html);
        $title       = replace_news("add", $title, false); // HTML in title is not allowed

        // Check optional fields
        $optfields = array();
        foreach ($cfg['more_fields'] as $i => $v)
        {
            if ($v[0] == '&' && $_REQUEST[$i] == false)
                $optfields[] = substr($v, 1);
        }

        // Replace code ----------------------------------------------------------------------------------------------------
        if (count($optfields))
            $error_messages .= getpart('addnews_err', array( lang('Some fields cannot be blank').': '.implode(', ', $optfields) ));

        if (trim($title) == false)
            $error_messages .= getpart('addnews_err', array( lang("The title cannot be blank") ));

        if (trim($short_story) == false)
            $error_messages .= getpart('addnews_err', array( lang("The story cannot be blank") ));

        if ( $member_db[UDB_CBYEMAIL] == 1)
            $added_by_email = $member_db[UDB_EMAIL];
        else $added_by_email = "none";

        // avatar check
        if ($manual_avatar)
        {
            $check_result = check_avatar($manual_avatar);
            if ($check_result['is_loaded'] == false)
                $error_messages .= getpart('addnews_err', array( lang('Avatar not uploaded!').' '.$check_result['error_msg'] ));
            $manual_avatar = $check_result['path'];
        }

        if($config_use_avatar == 'yes')
        {
            if(!create_avatar_size_in_mf($_avatar_width, '_avatar_width', 'Avatar width'))
                $error_messages .= getpart('addnews_err', array( lang('Avatar width may consist only digits and % or px on the end') ));
            if(!create_avatar_size_in_mf($_avatar_height, '_avatar_height', 'Avatar height'))
                $error_messages .= getpart('addnews_err', array( lang('Avatar height may consist only digits and % or px on the end') ));
        }

        // Additional fields ---
        foreach ($cfg['more_fields'] as $i => $v) $pack = edit_option($pack, $i, $_REQUEST[$i]);

        // Preview tool
        $preview_hmtl = false;
        if (isset($preview) && $preview == 'preview')
        {
            $new[NEW_ID]        = time() + $config_date_adjust*60;
            $new[NEW_USER]      = $member_db[2];
            $new[NEW_TITLE]     = $title;
            $new[NEW_SHORT]     = $short_story;
            $new[NEW_FULL]      = $full_story;
            $new[NEW_AVATAR]    = $manual_avatar;
            $new[NEW_CAT]       = $nice_category;
            $new[NEW_MF]        = $pack;
            $new[NEW_OPT]       = $options;

            $preview_hmtl  = getpart('addnews_preview', array( lang('Preview active news'), template_replacer_news($new, $template_active) ));
            $preview_hmtl .= getpart('addnews_preview', array( lang('Preview full story'),  template_replacer_news($new, $template_full) ));
            $preview_hmtl = preg_replace('/<a .*?>(.*?)<\/a>/i', '<u>\\1</u>', $preview_hmtl);

            $error_messages = false;
        }

        // ---------------------------------------------------------------------------------------------------- SAVE ---
        if ($error_messages == false && $preview == false)
        {
            // Make unique time, just for draft/normal: not postponed
            if ($postpone == false)
            {
                $added_time = time() + $config_date_adjust*60;
                if ( file_exists (SERVDIR.'/cdata/newsid.txt') )
                    $added_time = join('', file(SERVDIR.'/cdata/newsid.txt'));

                if (time() + $config_date_adjust*60 == $added_time) $added_time++;
                else $added_time = time() + $config_date_adjust*60;

                $w = fopen(SERVDIR.'/cdata/newsid.txt', 'w');
                fwrite($w, $added_time);
                fclose($w);
            }

            // Save The News Article In Active_News_File
            $all_db         = file($decide_news_file);
            $news_file      = fopen($decide_news_file, "w");
            flock($news_file, LOCK_EX);

            $has_added = false;
            if($postpone)
                $added_time = check_postponed_date($added_time, $all_db);
            $add_line  = "$added_time|$member_db[2]|$title|$short_story|$full_story|$manual_avatar|$nice_category||$pack|$options|\n";
            foreach ($all_db as $line)
            {
                list ($ID) = explode("|", $line);

                // Add one
                if ($ID <= time() + $config_date_adjust*60 && $has_added == false)
                {
                    fwrite($news_file, $add_line);
                    $has_added = true;
                }
                fwrite($news_file, $line);
            }

            // In any case add this news
            if ($has_added == false) fwrite($news_file, $add_line);

            flock($news_file, LOCK_UN);
            fclose($news_file);

            // Add Blank Comment In The Active_Comments_File --- only for active/draft news
            if ($postpone_draft != "postpone")
            {
                $old_com_db = file(SERVDIR."/cdata/comments.txt");
                $new_com_db = fopen(SERVDIR."/cdata/comments.txt", "w");
                flock($new_com_db, LOCK_EX);
                fwrite($new_com_db, "$added_time|>|\n");
                foreach ($old_com_db as $line) fwrite($new_com_db, $line);
                flock($new_com_db, LOCK_UN);
                fclose($new_com_db);
            }

            // Increase By 1 The Number of Written News for Current User
            $member_db[UDB_COUNT]++;
            user_update($username, $member_db);

            // Do backup news (x2 disk space)
            if ($config_backup_news == 'yes')
                copy($decide_news_file, $decide_news_file.'.bak');

            // Notifications
            if ($member_db[UDB_ACL] == ACL_LEVEL_JOURNALIST)
            {
                //user is journalist and the article needs to be approved, Notify !!!
                if ($config_notify_unapproved == "yes" and $config_notify_status == "active")
                {
                    send_mail
                    (
                        $config_notify_email,
                        lang("CuteNews - Unapproved article was Added"),
                        str_replace( array('%1','%2'), array($member_db[UDB_NAME], $title), 'The user %1 (journalist) posted article %2 which needs first to be Approved')
                    );
                }
            }

            if  ($postpone)
            {
                 msg("info", lang("News added (Postponed)"), lang("The news item was successfully added to the database as postponed. It will be activated at").date(" Y-m-d H:i:s", $added_time), '#GOBACK');
            }
            else
            {
                $source = '';
                if (strpos($decide_news_file, 'unapproved')) $source = '&source=unapproved';
                if (strpos($decide_news_file, 'postponed'))  $source = '&source=postponed';

                relocation($PHP_SELF."?mod=editnews&action=editnews&id=$added_time&saved=add$source");
            }
        }
    }

    // --------------- show add news form -------------------
    $CSRF = CSRFMake();
    echoheader("addnews", lang("Add News"));
    
    $short_story_id = 'short_story';
    $full_story_id  = 'full_story';

    $_cat_html = false;
    $_multi_cat_html = false;
    $_dateD = false;
    $_dateM = false;
    $_dateY = false;
    $xfields = array();

    // init x-fields
    if ( !isset($cfg['more_fields']) )
    {
        $cfg['more_fields'] = array();

        $fx = fopen(SERVDIR.'/cdata/conf.php', 'w');
        fwrite($fx, "<?php die(); ?>\n" . serialize($cfg) );
        fclose($fx);
    }

    foreach ($cfg['more_fields'] as $i => $v)
    {
        $af = isset($_POST[$i]) ? $_POST[$i] : false;
        if ( $v[0] == '&' )
             $xfields[] = array( $i, substr($v,1), '<span style="color: red;">*</span> '. lang('required','news'), $af );
        else $xfields[] = array( $i, $v, '' ,$af );
        if($i == '_avatar_width')
        {
            list($name, $desc, $req, $value) = array_pop($xfields);
            $_avatar_width = $value;
        }
        if($i == '_avatar_height')
        {
            list($name, $desc, $req, $value) = array_pop($xfields);
            $_avatar_height = $value;
        }
    }

    if (count($cat_lines) > 0)
    {
        // old style
        foreach ($cat_lines as $single_line)
        {
            $cat_arr = explode("|", $single_line);
            $_cat_html .= '<option '.($category == $cat_arr[0]? ' selected ':'').' value="'.$cat_arr[0].'">'.$cat_arr[1].'</option>';
        }

        // new style
        $i = 0;
        foreach ($cat_lines as $single_line)
        {
            $i++;
            $cat_arr  = explode("|", $single_line);
            $cat_id   = $cat_arr[0];
            $cat_name = $cat_arr[1];
            $_multi_cat_html .= "<td style='font-size:10px;' valign=top><label for='cat".$cat_id."'><input ".($category == $cat_id? " checked ":'')." style='background-color:transparent;border:0px;' type=checkbox name='category[]' id='cat".$cat_id."' value='".$cat_id."'>".$cat_name."</label></td>";
            if ($i%4 == 0) $_multi_cat_html .= '<tr>';
        }
    }

    // ON/OFF CKEditor
    $tpl = $use_wysiwyg ? 'index_cke' : 'index';
    list($_dateD, $_dateM, $_dateY, $_dateH, $_dateI) = make_postponed_date();

    // Add hooks for modify ckeditor
    $CKEDITOR_Settings = hook('CKEDITOR_Settings', false);
    $CKEDITOR_SetsName = hook('CKEDITOR_SetsName', 'settings');

    // Edit news not replace fields
    $title       = htmlspecialchars( $_POST['title'] );
    $short_story = htmlspecialchars( $_POST['short_story'] );
    $full_story  = htmlspecialchars( $_POST['full_story'] );

    $UseAvatar   = ($config_use_avatar == 'yes') ? 1 : 0;
    $Using_HTML = $config_use_html;
    echo proc_tpl
    (
            'addnews/'.$tpl,
            array
            (
                'member_db8'             => $member_db[UDB_AVATAR],
                'cat_html'               => $_cat_html,
                'multi_cat_html'         => $_multi_cat_html,
                'insertsmiles'           => insertSmilies($short_story_id, 4, true, $use_wysiwyg),
                'insertsmiles_full'      => insertSmilies($full_story_id,  4, true, $use_wysiwyg),
                'dated'                  => $_dateD,
                'datem'                  => $_dateM,
                'datey'                  => $_dateY,
                'dateh'                  => $_dateH,
                'datei'                  => $_dateI,
            )
    );

    echofooter();
}