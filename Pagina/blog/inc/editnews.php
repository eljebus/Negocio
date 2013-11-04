<?PHP

if (!defined('INIT_INSTANCE')) die('Access restricted');

if ($member_db[UDB_ACL] > ACL_LEVEL_JOURNALIST)
    msg("error", "Access Denied", "You don't have permission to edit news");

// only show allowed categories
$source = preg_replace('~[^a-z0-9_\.]~i', '', $source);
list ($allowed_cats, $cat_lines, $cat) = get_allowed_cats($member_db);

$use_wysiwyg = 0;
if ( $config_use_wysiwyg == 'ckeditor' && is_dir(SERVDIR.'/core/ckeditor'))
{
    $implemented_ckeditor_filemanager = hook('implement_file_browser', "
        filebrowserBrowseUrl:      '{$PHP_SELF}?&mod=images&action=quick&wysiwyg=true',
        filebrowserImageBrowseUrl: '{$PHP_SELF}?&mod=images&action=quick&wysiwyg=true'");

    $use_wysiwyg = 1;
}

// ********************************************************************************
// List all news available for editing
// ********************************************************************************
if ($action == "list")
{
    $CSRF = CSRFMake();
    echoheader("editnews", lang("Edit News"));

    // How Many News to show on one page
    $all_db_tmp = array();
    $authors    = array();

    $news_per_page = intval($news_per_page);
    if ($news_per_page == false) $news_per_page = 21;

    // Choose only needed news items
    list($decide) = detect_source($source);
    if ($source == "postponed") ResynchronizePostponed();
    $all_db = file($decide);

    foreach ($all_db as $raw_line)
    {
        $raw_arr = explode("|", $raw_line);

        // Save author name
        $authors[ $raw_arr[NEW_USER] ]++;

        // Check access user for category
        if ( !empty($item_db[NEW_CAT]) )
             foreach (spsep($raw_arr[NEW_CAT]) as $all_this_cat)
                if ( !in_array($all_this_cat, $allowed_cats) and isset($cat[$all_this_cat]) )
                     continue;

        // If author is present, but author not match
        if ( $author && $raw_arr[NEW_USER] != $author) continue;

        // Skip category if present, and not exists
        if ( $category && !in_array($category, spsep($raw_arr[NEW_CAT])) ) continue;

        // If journalist, but not him article
        if ( $member_db[UDB_ACL] == ACL_LEVEL_JOURNALIST && $raw_arr[NEW_USER] != $member_db[UDB_NAME]) continue;

        $all_db_tmp[] = $raw_line;
    }
    $all_db = $all_db_tmp;

    // Prelist Entries
    if (empty($start_from)) $start_from = false;

    $ipos = 0;
    $flag = 1;
    $entries_showed = 0;

    // Sort news by...
    if (isset($_REQUEST['ord_title'])) $all_db = quicksort($all_db, NEW_TITLE.'/'.$_REQUEST['ord_title']);
    if (isset($_REQUEST['ord_date']))  $all_db = quicksort($all_db, NEW_ID.'/'.$_REQUEST['ord_date']);

    if (!empty($all_db))
    {
        $the_entry  = array();
        foreach ($all_db as $line)
        {
            // Skip $start_from news
            $ipos++;
            if ($ipos < $start_from) continue;

            $item_db    = explode("|", $line);
            $itemdate   = date("d/m/y", $item_db[0]);
            $bg         = $flag ? "#F7F6F4" : "#FFFFFF";
            $flag       = 1 - $flag;
            $entry_show = true;

            // Sanitize
            $title = $item_db[NEW_TITLE];
            $title = stripslashes( preg_replace(array("'\|'", "'\"'", "'\''"), array("I", "&quot;", "&#039;"), $title) );

            // If news title over 75 chars, truncate it
            $title = word_truncate($title, 75);

            // It's prospect article
            $prosrev = false;
            if ($item_db[NEW_ID] > (time() + $config_date_adjust*60) && $source != 'postponed') $prosrev = getpart('post_rev');

            // Enable Up/Down without sorting
            if ( !isset($_REQUEST['ord_title']) and !isset($_REQUEST['ord_date']) )
            {
                $up    = $PHP_SELF . build_uri('mod,action,direct,id,source,start_from,news_per_page,category,author',array('editnews','move','up',$item_db[0]));
                $down  = $PHP_SELF . build_uri('mod,action,direct,id,source,start_from,news_per_page,category,author',array('editnews','move','down',$item_db[0]));
                $ORDER = getpart('editnews_order', array($up, $down));
            }
            else $ORDER = '-';

            $the_oneln = array
            (
                'id'        => $item_db[NEW_ID],
                'title'     => str_replace(array('<','>'), array('&lt;', '&gt;'), $title),
                'bg'        => $bg,
                'source'    => $source,
                'pros'      => $prosrev,
                'order'     => $ORDER,
            );

            $count_comments = countComments($item_db[NEW_ID], $source);
            if  ($count_comments)
                 $the_oneln['comments'] = $count_comments;
            else $the_oneln['comments'] = "<span style='color:gray;'>".$count_comments."</span>";

            // Make category names
            if (empty($item_db[NEW_CAT]))
            {
                $the_oneln['category'] = "<span style='color: gray;'>---</span>";
            }
            elseif (strstr($item_db[NEW_CAT], ','))
            {
                $my_multy_cat_labels    = array();
                $all_this_cats_arr      = spsep($item_db[NEW_CAT]);

                foreach ($all_this_cats_arr as $this_single_cat) $my_multy_cat_labels[] = $cat[$this_single_cat];
                $my_multy_cat_labels    = join(', ', $my_multy_cat_labels);

                $the_oneln['category'] = "<span style='color:#7979FF;' title='$my_multy_cat_labels'>(".lang('multiple', 'editnews').")</span>";
            }
            else
            {
                $the_oneln['category'] = getpart('edn_link4cat', array( build_uri('mod,action,category,source', array('editnews','list',$item_db[NEW_CAT])), $cat[ $item_db[NEW_CAT] ] ));
            }

            $the_oneln['itemdate'] = $itemdate;
            $the_oneln['user'] = $item_db[NEW_USER];

            $the_entry[] = $the_oneln;
            $entries_showed++;

            if ($entries_showed >= $news_per_page) break;
        }

        // Make comments
        $dts         = 'mod,action,start_from,category,author,source,news_per_page';
        $title_ord   = make_order('ord_title', $dts, array('editnews', 'list', $start_from, $category, $author, $source, $news_per_page));
        $date_ord    = make_order('ord_date',  $dts, array('editnews', 'list', $start_from, $category, $author, $source, $news_per_page));
        $entries     = proc_tpl('editnews/list/line');
    }

    $all_count_news = count($all_db);
    $unapproved_selected = $postponed_selected = false;

    // Messages in top of editnews options bar
    if (isset($category) && $category)
        $cat_msg = lang("Category", 'editnews').": <b>".htmlspecialchars($cat[$category])."</b>;";

    if ($source == "postponed")
    {
        $source_msg = getpart('postponed_refresh');
        $postponed_selected = " selected ";
    }
    elseif ($source == "unapproved")
    {
        $source_msg = getpart("unapproved_title");
        $unapproved_selected = " selected ";
    }
    elseif ($source != "" )
    {
        $news_lines         = file(SERVDIR."/cdata/archives/$source.news.arch");
        $count              = count($news_lines);
        $last               = $count - 1;
        $first_news_arr     = explode("|", $news_lines[$last]);
        $last_news_arr      = explode("|", $news_lines[0]);
        $first_timestamp    = $first_news_arr[0];
        $last_timestamp     = $last_news_arr[0];
        $source_msg         = lang("Archive", 'editnews').": <b>". date("d M Y", intval($first_timestamp)) ." - ". date("d M Y", intval($last_timestamp)) ."</b>;";
    }

    if (!$handle = opendir(SERVDIR."/cdata/archives"))
        msg('error', lang('Error!'), lang("Cannot open directory cdata/archives"), "#GOBACK");

    // Source: archives
    $opt_source = false;
    while ( false !== ($file = readdir($handle)) )
    {
        if ($file != "." and $file != ".." and !is_dir(SERVDIR."/cdata/archives/$file") and substr($file, -9) == 'news.arch')
        {
            $src                = explode('.', $file);
            $info_file          = SERVDIR."/cdata/archives/" . substr($file, 0, -9) . 'count.arch';

            if ( !file_exists($info_file) )
            {
                $data  = file(SERVDIR."/cdata/archives/$file");
                $count = count( $data );

                $fx = fopen($info_file, 'w');
                fwrite($fx, $count."\n");               
                $ex = explode('|', $data[0]); fwrite($fx, $ex[0]."\n");
                $ex = explode('|', $data[$count-1]); fwrite($fx, $ex[0]."\n");
                fclose($fx);
            }

            $arch_info          = file( $info_file );
            $count              = (int)$arch_info[0];
            $first_timestamp    = (int)$arch_info[1];
            $last_timestamp     = (int)$arch_info[2];
            $arch_date          = date("d M Y", $first_timestamp) ." - ". date("d M Y",$last_timestamp);
            $opt_source        .= "<option ".(($source == $src[0]) ? "selected" : "").' value="'.htmlspecialchars($src[0]).'">'.lang('Archive').': '.$arch_date.' ('.$count.')</option>';
        }
    }
    closedir($handle);

    // Category list
    $opt_catlist = false;
    foreach ($cat_lines as $single_line)
    {
        $cat_arr = explode("|", $single_line);
        $ifselected   = "";
        $opt_catlist .= "<option ".(($category == $cat_arr[0])? 'selected' : '').' value="'.htmlspecialchars($cat_arr[0]).'">'.htmlspecialchars($cat_arr[1]).'</option>';
    }

    // If user is not journalist, show author
    $opt_author = false;
    if ($member_db[UDB_ACL] != ACL_LEVEL_JOURNALIST)
    {
        foreach ($authors as $author_name => $news)
            $opt_author .= "<option ".(($author == $author_name)? 'selected':'').' value="'.htmlspecialchars($author_name).'">'.htmlspecialchars($author_name).' ('.$news.')</option>';
    }

    // SHOW OPTION BAR -----------------
    echo proc_tpl('editnews/list/optbar');

    // show entries -----------------

    $npp_nav = $tmp = false;

    // Prev button
    if ($start_from > 0)
    {
        $previous = $start_from - $news_per_page;
        if ($previous < 0) $previous = 0;

        $uri = build_uri('mod,action,start_from,category,author,source,news_per_page,ord_title,ord_date', array('editnews','list',$previous));
        $npp_nav .= '<a href="'.$PHP_SELF.$uri.'">&lt;&lt; '.lang('Previous').'</a>';
        $tmp = true;
    }

    // Next button
    if (count($all_db) > $ipos)
    {
        if ($tmp) $npp_nav .= "&nbsp;&nbsp;||&nbsp;&nbsp;";
        $how_next = count($all_db) - $ipos;

        if ($how_next > $news_per_page) $how_next = $news_per_page;
        $URL = build_uri('mod,action,start_from,category,author,source,news_per_page,ord_title,ord_date', array('editnews','list', $ipos+1));
        $npp_nav .= '<a href="'.$PHP_SELF.$URL.'">'.lang('Next').' '.$how_next.' &gt;&gt;</a>';
    }

    // choose action
    $do_action = false;
    if ($entries_showed != 0)
    {
        if ($member_db[UDB_ACL] == ACL_LEVEL_ADMIN)
            $do_action .= '<option title="'.lang('make new archive with all selected news').'" value="mass_archive">'.lang('Send to Archive').'</option>';

        if ($source == "unapproved" and ($member_db[UDB_ACL] == ACL_LEVEL_ADMIN or $member_db[UDB_ACL] == ACL_LEVEL_EDITOR))
            $do_action .= '<option '.(( $source == "unapproved" )?  'selected' : '').' title="'.lang('approve selected news').'" value="mass_approve">'.lang('Approve News').'</option>';

        if ($member_db[UDB_ACL] == ACL_LEVEL_ADMIN)
        {
            $do_action .= '<option title="'.lang('Move all selected news to one category').'" value="mass_move_to_cat">'.lang('Change Category').'</option>';
            $do_action .= '<option title="'.lang('Change date published').'" value="mass_change_pubdate">'.lang('Change Date').'</option>';
        }
    }

    echo proc_tpl('editnews/list/entries');
    echofooter();
}
// *********************************************************************************************************************
// Edit News Article
// *********************************************************************************************************************
elseif ($action == "editnews")
{
    $error_messages = false;

    // Detect
    if (REQ('saved','GETPOST') == 'yes') $saved_yes = 1;
    elseif (REQ('saved','GETPOST') == 'add') $saved_new = 1;

    $preview = (REQ('preview') == 'preview') ? 'preview' : false;

    // Do Edit News
    if ($subaction == "doeditnews")
    {
        CSRFCheck();

        // default values
        $nice_category = '';
        $options = array();

        // Format our categories variable
        if (is_array($category))
        {
            // User has selected multiple categories
            $ccount = 0;
            $nice_category = '';
            foreach ($category as $ckey => $cvalue)
            {
                if ( !in_array($cvalue, $allowed_cats) and isset($cat[$cvalue]) )
                     msg('error', lang('Error!'), lang('Not allowed category'), '#GOBACK');

                if ( $ccount == 0 ) $nice_category = $cvalue;
                else $nice_category = $nice_category.','.$cvalue;
                $ccount++;
            }
        }
        else
        {
            // Not in a category: don't format $nice_cats because we have not selected any.
            if ( !in_array($category, $allowed_cats) and isset($cat[$category]) )
                 msg('error', lang('Error!'), lang('Not allowed category'), '#GOBACK');
        }

        // Check optional fields
        if ($ifdelete != 'yes')
        {
            $optfields = array();
            $more = false;

            if($config_use_avatar == 'yes')
            {
                if(!create_avatar_size_in_mf($_avatar_width, '_avatar_width', 'Avatar width'))
                    $error_messages .= getpart('addnews_err', array( lang('Avatar width may consist only digits and % or px on the end') ));
                if(!create_avatar_size_in_mf($_avatar_height, '_avatar_height', 'Avatar height'))
                    $error_messages .= getpart('addnews_err', array( lang('Avatar height may consist only digits and % or px on the end') ));
            }

            foreach ($cfg['more_fields'] as $i => $v)
            {
                if ($v[0] == '&' && $_REQUEST[$i] == false)
                     $optfields[] = substr($v, 1);
                else $more = edit_option($more, $i, $_REQUEST[$i]);
            }
        }

        if (count($optfields))
            $error_messages .= getpart('addnews_err', array( lang('Some fields cannot be blank').': '.implode(', ', $optfields) ));

        if (trim($title) == "" and $ifdelete != "yes")
            $error_messages .= getpart('addnews_err', array( lang("The title cannot be blank"), "#GOBACK") );

        if ($short_story == "" and $ifdelete != "yes")
            $error_messages .= getpart('addnews_err', array( lang("The story cannot be blank"), "#GOBACK") );

        // Some replaces
        $use_html       = ($if_use_html == "yes" || $use_wysiwyg)? 1 : 0;

        $short_story    = replace_news("add", $short_story, $use_html);
        $full_story     = replace_news("add", $full_story,  $use_html);
        $title          = stripslashes( preg_replace(array("'\|'", "'\n'", "''"), array("I", "<br />", ""), $title) );
        $avatar         = stripslashes( preg_replace(array("'\|'", "'\n'", "''"), array("I", "<br />", ""), $avatar) );

        // HTML saved if force or use wysiwig
        if ($if_use_html == "yes" || $use_wysiwyg)
        {
            $use_html = true;
            $options = edit_option($options, 'use_html', true);
        }

        // Check avatar
        if ($editavatar)
        {
            $check_result = check_avatar($editavatar);
            if ($check_result['is_loaded'] == false)
                $error_messages .= getpart('addnews_err', array( lang('Avatar not uploaded!').' '.$check_result['error_msg'], '#GOBACK'));
            $editavatar = $check_result['path'];
        }

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

        // *************************************************
        // EDIT ONLY IF ALL CORRECT!
        // *************************************************
        if ($error_messages == false && $preview == false)
        {
            // select news and comment files
            if ($source == "")
            {
                $news_file = SERVDIR."/cdata/news.txt";
                $com_file = SERVDIR."/cdata/comments.txt";
            }
            elseif ($source == "postponed")
            {
                $news_file = SERVDIR."/cdata/postponed_news.txt";
                $com_file = SERVDIR."/cdata/comments.txt";
            }
            elseif ($source == "unapproved")
            {
                $news_file = SERVDIR."/cdata/unapproved_news.txt";
                $com_file = SERVDIR."/cdata/comments.txt";
            }
            else
            {
                $news_file = SERVDIR."/cdata/archives/$source.news.arch";
                $com_file = SERVDIR."/cdata/archives/$source.comments.arch";
            }

            // write
            $old_db = file( $news_file );
            $new_db = fopen( $news_file, "w");
            foreach ($old_db as $old_db_line)
            {
                $old_db_arr = explode("|", $old_db_line);
                if ($id != $old_db_arr[0])
                {
                    fwrite($new_db, $old_db_line);
                }
                else
                {
                    $have_perm = 0;
                    if (($member_db[UDB_ACL] == ACL_LEVEL_ADMIN) or ($member_db[UDB_ACL] == ACL_LEVEL_EDITOR)) $have_perm = 1;

                    // Journalist can't edit other pages (with other name)
                    elseif ($member_db[UDB_ACL] == ACL_LEVEL_JOURNALIST and $old_db_arr[NEW_USER] == $member_db[UDB_NAME]) $have_perm = 1;

                    if ($have_perm)
                    {
                        if ($ifdelete != "yes")
                        {
                            // If save as postponed news
                            if($source == "postponed")
                            {
                                $postponed_time = mktime($from_date_hour, $from_date_minutes, 0, $from_date_month, $from_date_day, $from_date_year);
                                $id = check_postponed_date($postponed_time, $old_db);
                            }
                            else
                                $id = $old_db_arr[NEW_ID];

                            $old_db_arr[NEW_ID] = $id;

                            // Only for editor without wysiwyg
                            if  ($config_use_wysiwyg == 'no')
                                $old_db_arr[NEW_OPT] = edit_option($old_db_arr[NEW_OPT], 'use_html', ($if_use_html == 'yes') ? 1 : 0);
                            else $old_db_arr[NEW_OPT] = str_replace("\n", "", $old_db_arr[NEW_OPT]);

                            fwrite ($new_db, "$old_db_arr[0]|$old_db_arr[1]|$title|$short_story|$full_story|$editavatar|$nice_category|$old_db_arr[7]|$more|$old_db_arr[9]|\n");
                            $okchanges = true;
                        }
                        else
                        {
                            $okdeleted  = true;

                            // For postponed don't delete comment: it not exists
                            if ( $source != 'postponed' )
                            {
                                $all_file   = file($com_file);
                                $new_com    = fopen($com_file,"w");

                                foreach ($all_file as $line)
                                {
                                    $line_arr = explode("|>|", $line);
                                    if ( $line_arr[0] == $id )
                                        $okdelcom = true;
                                    else fwrite($new_com, $line);
                                }
                                fclose($new_com);
                            }
                            else $okdelcom = true;
                        }
                    }
                    else
                    {
                        fwrite($new_db, $old_db_line);
                        $no_permission = true;
                    }
                }
            }
            fclose($new_db);

            // Show messages
            if ($no_permission)
                msg("error", lang("No Access"), lang("You don't have access for this action"), '#GOBACK');

            if ($okdeleted)
            {
                if ( $okdelcom )
                    msg("info", lang("News Deleted"), lang("The news item successfully was deleted").'.<br />'.lang("If there were comments for this article they are also deleted."));
                else msg("info", lang("News Deleted"), lang("The news item successfully was deleted").'.<br />'.
                    lang("If there were comments for this article they are also deleted.").'<br /><span style="color:red;">'.
                    lang("But cannot delete comments of this article!")."</span>");
            }
            elseif ($okchanges)
            {
                if ($config_backup_news == 'yes')
                {
                    $from = fopen($news_file, "r");
                    $news_backup = fopen($news_file.'.bak', "w");
                    while (!feof($from)) fwrite($news_backup, fgets($from));
                    fclose($from);
                    fclose($news_backup);
                }

                // Journalist Edit --> make news unapproved
                if ($source != 'unapproved' && $member_db[UDB_ACL] == ACL_LEVEL_JOURNALIST)
                {
                    list($id) = GET('id');
                    relocation("$PHP_SELF?mod=massactions&id={$id}&selected_news[]={$id}&action=mass_unapprove&source=$source&csrf_code={$CSRF}&returnto=edit");
                }

                relocation("$PHP_SELF?mod=editnews&action=editnews&id=$id&source=$source&saved=yes");
            }
            else msg("error", lang('Error!'), lang("The news item cannot be found or there is an error with the news database file."), '#GOBACK');
        }
    }

    // **************************************************
    // View news if empty of OK with edit
    // **************************************************

    list ($news_file, $com_file) = detect_source($source);
    $all_db = file($news_file);

    // Check for exists news ID
    $found = FALSE;
    foreach ($all_db as $line)
    {
        $item_db = explode("|", $line);
        if ($id == $item_db[0])
        {
            $found = TRUE;
            break;
        }
    }

    if (!$found)
        msg("error", lang('Error!'), lang("The selected news item cannot be found"), '#GOBACK');

    // Check permission to edit news
    $have_perm = 0;
    if (($member_db[UDB_ACL] == ACL_LEVEL_ADMIN) or ($member_db[UDB_ACL] == ACL_LEVEL_EDITOR))
    {
        $have_perm = 1;
    }
    elseif ($member_db[UDB_ACL] == ACL_LEVEL_JOURNALIST and $item_db[NEW_USER] == $member_db[UDB_NAME])
    {
        $have_perm = 1;
    }

    if (!$have_perm)
        msg("error", lang("No Access"), lang("You don't have access for this action"), '#GOBACK');

    // Check access user for category
    if ( !empty($item_db[NEW_CAT]) )
    {
        $all_these_cats = spsep($item_db[NEW_CAT]);
        foreach ($all_these_cats as $all_this_cat)
        {
            if ( !in_array($all_this_cat, $allowed_cats) and isset($cat[$all_this_cat]) )
                 msg("error", lang("Access Denied"), lang("This article is posted under category which you are not allowed to access."), '#GOBACK');
        }
    }

    $short_story_id = 'short_story';
    $full_story_id = 'full_story';

    $newstime = date("D, d F Y h:i:s", $item_db[0]);
    $item_db[NEW_TITLE] = stripslashes( preg_replace(array("'\|'", "'\"'", "'\''"), array("I", "&quot;", "&#039;"), $item_db[NEW_TITLE]) );

    // Are we using the WYSIWYG ?
    $use_wysiwyg = ($config_use_wysiwyg == "no") ? 0 : 1;
    $item_db[NEW_SHORT] = replace_news("admin", $item_db[NEW_SHORT], $use_wysiwyg);
    $item_db[NEW_FULL]  = replace_news("admin", $item_db[NEW_FULL], $use_wysiwyg);

    $CSRF = CSRFMake();
    echoheader("editnews", lang("Edit News"));

    // make category lines
    $i = 0;
    if ( $subaction == 'doeditnews' ) $item_db[NEW_CAT] = join(',', $category);

    if ( count($cat_lines) > 0)
    {
        $lines_html = false;
        foreach ($cat_lines as $single_line)
        {
            $cat_arr = explode("|", $single_line);

            $lines_html .= "<td style='font-size:10px;' valign=top><label for='cat{$cat_arr[0]}'>";

            if ( in_array($cat_arr[NEW_ID], spsep($item_db[NEW_CAT])) )
                 $lines_html .= "<input checked style='background-color:transparent; border:0px;' type='checkbox' name='category[]' id='cat{$cat_arr[0]}' value='{$cat_arr[0]}'>$cat_arr[1]</label>";
            else $lines_html .= "<input style='background-color:transparent; border:0px;' type='checkbox' name='category[]' id='cat{$cat_arr[0]}' value='{$cat_arr[0]}'>$cat_arr[1]</label>";

            $i++;
            if ($i%4 == 0) $lines_html .= '<tr>';
        }
        $lines_html .= "</tr>";
    }

    // Show the Comments for Editing
    $Comments_HTML = false;

    if ( $source == "" or $source == "postponed" or $source == "unapproved")
         $all_comments_db = file(SERVDIR."/cdata/comments.txt");
    else $all_comments_db = file(SERVDIR."/cdata/archives/{$source}.comments.arch");

    $found_newsid = false;
    foreach ($all_comments_db as $comment_line)
    {
        $comment_line = trim($comment_line);
        $comments_arr = explode("|>|",$comment_line);
        if ($comments_arr[0] == $id)
        {
            //if these are comments for our story
            $found_newsid = TRUE;
            if ($comments_arr[COM_USER] != '')
            {
                $flag = 1;
                $different_posters = explode("||", $comments_arr[COM_USER]);

                foreach ($different_posters as $individual_comment)
                {
                    if ($flag == 1)
                    {
                        $bg = "bgcolor=#F7F6F4";
                        $flag = 0;
                    }
                    else
                    {
                        $bg = "";
                        $flag = 1;
                    }

                    $comment_arr            = explode("|", $individual_comment);
                    $comtime                = date("d/m/y h:i:s", intval($comment_arr[COM_ID]));
                    $comm_value             = stripslashes(strip_tags($comment_arr[COM_TEXT]));
                    $comm_excerpt           = word_truncate($comm_value, 75);

                    if ($comment_arr[COM_USER])
                    {
                        $comment_arr[COM_USER] = word_truncate($comment_arr[COM_USER], 25);
                        $Comments_HTML .= proc_tpl('editnews/editnews/comment_line',
                            array
                            (
                                'comment_arr0'  => $comment_arr[COM_ID],
                                'comment_arr1'  => $comment_arr[COM_USER],
                                'comment_arr3'  => $comment_arr[COM_IP],
                                'comm_excerpt'  => my_strip_tags($comm_excerpt),
                            )
                        );

                    }//if not blank
                }

                $Comments_HTML .= proc_tpl('editnews/editnews/comment_actions');
                break;

            }
            else //if there are any comments
            {
                $Comments_HTML = proc_tpl('editnews/editnews/nocomments');
                $found_newsid  = false;
            }
        }
    }

    if ($found_newsid == false)
        $Comments_HTML = proc_tpl('editnews/editnews/nocomments');

    // init x-fields
    $options = array();
    $xfields = array();
    $postpone_date = false;

    // Edit news not replace fields
    if ($subaction == 'doeditnews')
    {
        $item_db[NEW_TITLE] = htmlspecialchars( $_POST['title'] );
        $item_db[NEW_SHORT] = htmlspecialchars( $_POST['short_story'] );
        $item_db[NEW_FULL]  = htmlspecialchars( $_POST['full_story'] );

        $article = array();
        foreach ($cfg['more_fields'] as $i => $v) $article[$i] = htmlspecialchars( $_POST[$i] );
    }
    else
    {
        $article = options_extract($item_db[NEW_MF]);
    }

    // Extract more fields
    foreach ($cfg['more_fields'] as $i => $v)
    {
        $af = isset($article[$i]) ? htmlspecialchars( $article[$i] ) : false;
        if ( $v[0] == '&' )
             $xfields[] = array( $i, substr($v,1), '<span style="color: red;">*</span> '. lang('required','news'), $af );
        else $xfields[] = array( $i, $v, '', $af );
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

    $options = options_extract($item_db[NEW_OPT]);
    if ($source == 'postponed') $postpone_date = $id;

    // show template -------------------------------------------------------------------
    if ( $use_wysiwyg ) $tpl = 'index_cke'; else $tpl = 'index';
    list($_dateD, $_dateM, $_dateY, $_dateH, $_dateI) = make_postponed_date($postpone_date);

    // Add hooks for modify ckeditor
    $CKEDITOR_Settings = hook('CKEDITOR_Settings', false);
    $CKEDITOR_SetsName = hook('CKEDITOR_SetsName', 'settings');

    $Using_HTML = $options['use_html'];
    $Using_Avat = ($config_use_avatar == 'yes') ? 1 : 0;
    $Unapproved = ($source == 'unapproved')? 1 : 0;

    // Remove "Approve" button from editor
    if ($member_db[UDB_ACL] == ACL_LEVEL_JOURNALIST) $Unapproved = 0;

    echo proc_tpl
    (
        'editnews/editnews/'.$tpl,
        array
        (
            'id'                    => intval($id),
            'item_db1'              => $item_db[NEW_USER],
            'item_db2'              => $item_db[NEW_TITLE],
            'item_db3'              => $item_db[NEW_SHORT],
            'item_db4'              => $item_db[NEW_FULL],
            'item_db5'              => $item_db[NEW_AVATAR],
            'short_story_smiles'    => insertSmilies($short_story_id, 4, true, $use_wysiwyg),
            'full_story_smiles'     => insertSmilies($full_story_id, 4, true, $use_wysiwyg),
            'dated'                 => $_dateD,
            'datem'                 => $_dateM,
            'datey'                 => $_dateY,
            'dateh'                 => $_dateH,
            'datei'                 => $_dateI,
        )
    );

    echofooter();
}
// *********************************************************************************************************************
// Move news up/down
// *********************************************************************************************************************
elseif ($action == 'move')
{
    $id = intval($id);

    if (preg_match('~^[0-9]*$~', trim($source))) $src = "archives/$source.news.arch";
    elseif ($source) $src = $source.'_news.txt';
    else $src = 'news.txt';

    // Only for present file
    if (!file_exists(SERVDIR . '/cdata/' . $src)) $src = 'news.txt';
    $dbpath = SERVDIR . '/cdata/' . $src;

    // Search and swap lines
    $all_db = file($dbpath);
    foreach ($all_db as $i => $ln)
    {
        list ($lnid) = explode('|', $ln, 2);
        if ($lnid == $id)
        {
            if ($direct == 'up' && $i > 0)
            {
                $a = $all_db[$i-1];
                $all_db[$i-1] = $all_db[$i];
                $all_db[$i] = $a;
                break;
            }
            elseif ($direct == 'down' && $i < count($all_db))
            {
                $a = $all_db[$i+1];
                $all_db[$i+1] = $all_db[$i];
                $all_db[$i] = $a;
                break;
            }
        }
    }

    $w = fopen($dbpath, 'w');
    fwrite($w, join('', $all_db));
    fclose($w);

    // Redirect after move
    $URL = $PHP_SELF . build_uri('mod,action,id,source,start_from,news_per_page,category,author,ord_title,ord_date', array('editnews','list', $item_db[NEW_ID]), false);
    relocation( $URL, false );
}