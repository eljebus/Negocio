<?PHP

if (!defined('INIT_INSTANCE')) die('Access restricted');

if ($member_db[UDB_ACL] == ACL_LEVEL_COMMENTER and ($action != 'personal' and $action != 'options' and $action != 'dosavepersonal'))
    msg('error', 'Error!', 'Access Denied for your user-level (commenter)');

$do_template = preg_replace('~[^a-z0-9_]~i', '', $do_template);

// Init Templates
$Template_Form = array
(
    array('name' => 'template_active', 'title' => 'Active News'),
    array('name' => 'template_full', 'title' => 'Full Story'),
    array('name' => 'template_comment', 'title' => 'Comment'),
    array('name' => 'template_form', 'title' => 'Add comment form'),
    array('name' => 'template_prev_next', 'title' => 'News Pagination'),
    array('name' => 'template_comments_prev_next', 'title' => 'Comments Pagination'),
);
$Template_Form = hook('template_forms', $Template_Form);

// ********************************************************************************
// Options Menu
// ********************************************************************************
if ($action == "options" or $action == '')
{
    echoheader("options", "Options", make_breadcrumbs('main/=options'));

    //----------------------------------
    // Predefine Options
    //----------------------------------

    // access means the lower level of user allowed; 1:admin, 2:editor+admin, 3:editor+admin+journalist, 4:all
    $options = array
    (
        array(
               'name'               => lang("Personal data"),
               'url'                => "$PHP_SELF?mod=options&action=personal",
               'access'             => ACL_LEVEL_COMMENTER,
        ),
        array(
               'name'               => lang("Block IP's from posting comments"),
               'url'                => "$PHP_SELF?mod=ipban",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("System configurations"),
               'url'                => "$PHP_SELF?mod=options&action=syscon&rand=".time(),
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Integration wizard"),
               'url'                => "$PHP_SELF?mod=wizards",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Edit templates"),
               'url'                => "$PHP_SELF?mod=options&action=templates",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Add/Edit users"),
               'url'                => "$PHP_SELF?mod=editusers&action=list",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Archive manager"),
               'url'                => "$PHP_SELF?mod=tools&action=archive",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Manage uploaded images"),
               'url'                => "$PHP_SELF?mod=images",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Backup tool"),
               'url'                => "$PHP_SELF?mod=tools&action=backup",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Edit categories"),
               'url'                => "$PHP_SELF?mod=categories",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("User log"),
               'url'                => "$PHP_SELF?mod=tools&action=userlog",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Word replacement"),
               'url'                => "$PHP_SELF?mod=tools&action=replaces",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang("Additional fields"),
               'url'                => "$PHP_SELF?mod=tools&action=xfields",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang('Update cutenews', 'options'),
               'url'                => "$PHP_SELF?mod=update&action=update",
               'access'             => ACL_LEVEL_ADMIN,
        ),
        array(
               'name'               => lang('Plugin manager', 'options'),
               'url'                => "$PHP_SELF?mod=tools&action=plugins",
               'access'             => ACL_LEVEL_ADMIN,
        ),
    );

    // Optional Fields -------------------------------
    if ($config_use_replacement)
    {
        $options[] = array(
            'name'              => lang('URL Rewrite manager', 'options'),
            'url'               => "$PHP_SELF?mod=tools&action=rewrite",
            'access'            => ACL_LEVEL_ADMIN,
        );
    }

    $options = hook('more_options', $options);

    //------------------------------------------------
    // Cut the options for wich we don't have access
    //------------------------------------------------
    $count_options = count($options);
    for ($i = 0; $i<$count_options; $i++)
    {
        if ($member_db[UDB_ACL] > $options[$i]['access'])
            unset($options[$i]);
    }

    $i = 0;
    echo '<div style="margin: 0 0 0 64px">';
    foreach ($options as $option)
    {
        echo "<div style='float: left; padding: 2px; width: 280px;'><a href='".$option['url']."'><b>".$option['name']."</b></a></div>";
    }
    echo '</div>';
    echofooter();
}
// ********************************************************************************
// Show Personal Data
// ********************************************************************************
elseif ($action == "personal")
{
    $CSRF = CSRFMake();

    if ($member_db[UDB_ACL] == ACL_LEVEL_COMMENTER)
         echoheader("user", "Personal Data");
    else echoheader("user", "Personal Data", make_breadcrumbs('main/options=options/Personal Data'));

    foreach($member_db as $key => $value)
        $member_db[$key]  = stripslashes(preg_replace(array("'\"'", "'\''"), array("&quot;", "&#039;"), $member_db[$key]));

    // define access level
    $access_level = array(ACL_LEVEL_ADMIN       => 'administrator', ACL_LEVEL_EDITOR    => 'editor',
                          ACL_LEVEL_JOURNALIST  => 'journalist',    ACL_LEVEL_COMMENTER => 'commenter');

    echo proc_tpl('options/personal',
                  array(
                      'member_db[2]' => $member_db[UDB_NAME],
                      'member_db[4]' => $member_db[UDB_NICK],
                      'member_db[5]' => $member_db[UDB_EMAIL],
                      'member_db[6]' => $member_db[UDB_COUNT],
                      'member_db[8]' => $member_db[UDB_AVATAR],
                      'ifchecked'    => ($member_db[UDB_CBYEMAIL] == 1)? "checked" : false, // if user wants to hide his e-mail
                      'access_level' => $access_level[ $member_db[UDB_ACL] ],
                      'registrationdate' => date("D, d F Y", $member_db[0]), // registration date
                      'bg'           => $member_db[UDB_ACL] < ACL_LEVEL_COMMENTER? "bgcolor=#F7F6F4" : false,
                  ),
                  array('NOTCOMMENTER' => $member_db[UDB_ACL] < ACL_LEVEL_COMMENTER)
    );

    echofooter();
}
// ********************************************************************************
// Save Personal Data
// ********************************************************************************
elseif ($action == "dosavepersonal")
{
    CSRFCheck();

    $username           = $member_db[UDB_NAME];
    $editnickname       = replace_comment("add", $editnickname);
    $editmail           = replace_comment("add", $editmail);
    $edithidemail       = replace_comment("add", $edithidemail);
    $change_avatar      = replace_comment("add", $change_avatar);

    if ($editpassword and !preg_match("/^[\.A-z0-9_\-]{1,31}$/i", $editpassword))
        msg("error", lang('Error!'), lang("Your password must contain only valid characters and numbers"), '#GOBACK');

    $edithidemail   = $edithidemail? 1 : 0;
    $pack           = user_search($username);

    // editing password (with confirm)
    if ($editpassword)
    {
        if ($confirmpassword == $editpassword)
        {
            $hashs          = hash_generate($editpassword);
            $pack[UDB_PASS] = $hashs[ count($hashs) - 1 ];
        }
        else msg('error', lang('Error!'), lang('Confirm password not match'), "#GOBACK");
    }

    $pack[UDB_NICK]         = $editnickname;
    $pack[UDB_EMAIL]        = $editmail;
    $pack[UDB_CBYEMAIL]     = $edithidemail;
    $pack[UDB_AVATAR]       = $change_avatar;

    user_update($username, $pack);
    add_to_log($username, lang('Update personal data'));

    msg("info", lang("Changes Saved"), lang("Your personal information was saved"), "#GOBACK");
    
}
// ********************************************************************************
// Edit Templates
// ********************************************************************************
elseif ($action == "templates")
{
    if ($member_db[UDB_ACL] != ACL_LEVEL_ADMIN)
        msg("error", lang("Access Denied"), lang("You don't have permissions for this type of action"), '#GOBACK');

    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Detect all template packs we have
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    $templates_list = array();
    if (!$handle = opendir(SERVDIR."/cdata")) die("Cannot open directory ".SERVDIR."/cdata ");
    while (false !== ($file = readdir($handle)))
    {
        if(preg_replace('/^.*\.(.*?)$/', '\\1', $file) == 'tpl')
        {
            $file_arr           = explode(".", $file);
            $templates_list[]   = $file_arr[0];
        }
    }
    closedir($handle);

    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      If we want to create new template
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($subaction == "new")
    {
        echoheader("options", "New Template", make_breadcrumbs('main/options=options/options:templates=templates/New'));
        echo proc_tpl('options/make_template');
        echofooter();
        die();
    }
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Do Create the new template
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($subaction == "donew")
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $template_name))
            msg("error", lang('Error!'), lang("The name of the template may only contain letters and numbers"), '#GOBACK');

        if (file_exists(SERVDIR."/cdata/$template_name.tpl"))
            msg("error", lang('Error!'), lang("Template with this name already exists"), '#GOBACK');

        // Make file
        if ( !file_exists(SERVDIR."/cdata/$base_template.tpl")) $base_template = 'Default';
        if ( !copy(SERVDIR."/cdata/$base_template.tpl", SERVDIR."/cdata/$template_name.tpl") )
             msg("error", lang('Error!'), str_replace('%1', $base_template, lang("Cannot copy file %1 to ./cdata/ folder with name "))."$template_name.tpl", '#GOBACK');

        chmod(SERVDIR."/cdata/$template_name.tpl", 0666);

        msg("info", lang("Template Created"), lang("A new template was created with name")." <b>$template_name</b>", '#GOBACK');
    }
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Deleting template, preparation
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($subaction == "delete")
    {
        if (strtolower($do_template) == "default")
            msg("Error",  lang('Error!'), lang("You cannot delete the default template"), '#GOBACK');

        if (strtolower($do_template) == "rss")
            msg("Error", lang('Error!'), lang("You cannot delete the RSS template, you are not even supposed to edit it"), '#GOBACK');

        $msg = proc_tpl('options/sure_delete');
        msg("info", lang("Deleting Template"), $msg);
    }
    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      DO Deleting template
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    if ($subaction == "dodelete")
    {
        if(strtolower($do_template) == "default")
            msg("Error", lang('Error!'), lang("You cannot delete the default template"), '#GOBACK');

        $unlink = unlink(SERVDIR."/cdata/$do_template.tpl");
        if ( !$unlink )
             msg("error", lang('Error!'), "Cannot delete file ./cdata/$do_template.tpl <br>maybe there is no permission from the server", '#GOBACK');
        else msg("info",  lang("Template Deleted"), str_replace('%1', $do_template, lang("The template <b>%1</b> was deleted.")));
    }

    /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Show The Template Manager
     ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
    $show_delete_link = false;
    if ($do_template == '' or !$do_template)
    {
        $do_template = 'Default';
    }
    elseif ( !in_array(strtolower($do_template), array('default','rss','headlines')) )
    {
        $show_delete_link = "<a href=\"$PHP_SELF?action=templates&mod=options&subaction=delete&do_template=$do_template\">[".lang('delete this template')."]</a>";
    }

    // Load template variables ---------------------
    require(SERVDIR."/cdata/$do_template.tpl");
    foreach ($Template_Form as $id => $template)
    {
        $tplcon = (ini_get('magic_quotes_gpc')) ? stripslashes($$template['name']) : $$template['name'];
        $Template_Form[$id]['part'] = htmlspecialchars( $tplcon );
    }

    echoheader("options", "Templates", make_breadcrumbs('main/options=options/Templates'));

    $SELECT_template = false;
    foreach ($templates_list as $single_template)
    {
        if ($single_template == $do_template)
             $SELECT_template .= "<option selected value='$single_template'>$single_template</option>";
        else $SELECT_template .= "<option value='$single_template'>$single_template</option>";
    }

    $save = ($save == 'success')? '- Success saved!' : '';
    echo proc_tpl('options/templates');
    echofooter();
}
// ********************************************************************************
// Do Save Changes to Templates
// ********************************************************************************
elseif($action == "dosavetemplates")
{
    if ($member_db[UDB_ACL] != 1)
        msg("error", lang("Access Denied"), lang("You don't have permissions for this type of action", '#GOBACK'));

    if ($do_template == "" or !$do_template)
        $do_template = "Default";

    $template_file = SERVDIR."/cdata/$do_template.tpl";

    $handle = fopen($template_file, "w");
    fwrite($handle, '<'.'?php'."\n///////////////////// TEMPLATE $do_template /////////////////////\n");
    foreach ($Template_Form as $parts)
    {
        $name  = $parts['name'];
        $value = $_REQUEST['edit_'.$name];
        $value = str_replace('HTML;', '', $value);
        $value = (ini_get('magic_quotes_gpc')) ? stripslashes($value) : $value;
        fwrite($handle, "\${$name} = <<<HTML\n{$value}\nHTML;\n\n\n");
    }
    fwrite($handle, "?>");
    fclose($handle);

    add_to_log($member_db[UDB_NAME], lang('Update templates'));
    relocation($PHP_SELF.'?action=templates&mod=options&do_template='.$do_template.'&save=success');
}

// ********************************************************************************
// System Configuration
// ********************************************************************************
elseif ($action == "syscon")
{
    if ($member_db[UDB_ACL] != ACL_LEVEL_ADMIN)
        msg("error", lang("Access Denied"), lang("You don't have permissions to access this section"), '#GOBACK');

    $bc = 'main/options/options:syscon=config';
    if (isset($_REQUEST['message'])) $bc .= '/='.lang('Your Configuration Saved');

    $csrf_code = CSRFMake();

    function showRow($title="", $description="", $field="")
    {
        global $i;

        if ( $i%2 == 0 and $title != "") $bg = "bgcolor=#F7F6F4"; else $bg = "";
        echo proc_tpl("options/syscon.row", array('bg' => $bg, 'title' => $title, 'field' => $field, 'description' => $description));
        $i++;
    }

    // Make syscon row
    function syscon($config_name, $title, $options = null)
    {
        global $counter;

        list($title, $desc) = explode('|', $title, 2);
        list($config_name, $opt) = explode('=', $config_name, 2);

        $out = '';
        $var = getoption($config_name);

        // Is digits or empty - INPUT
        if (!is_array($options))
        {
            $opt = $opt ? $opt : 40;

            if ($options == ':text:')
            {
                list($cols, $rows) = explode('/', $opt);
                $out = '<textarea cols="'.$cols.'" rows="'.$rows.'" name="save_con['.$config_name.']">'.htmlspecialchars($var).'</textarea>';
            }
            elseif ($options == 'Y/N')
            {
                $checked = $var? 'checked="checked"': '';
                $out = '<input type="checkbox" name="save_con['.$config_name.']" value="1" '.$checked.' />';
            }
            elseif ($options == 'y/n')
            {
                $out  = '<input type="radio" name="save_con['.$config_name.']" value="no" '.(($var == 'no')? 'checked="checked"' : '').' /> No ';
                $out .= '<input type="radio" name="save_con['.$config_name.']" value="yes" '.(($var != 'no')? 'checked="checked"' : '').' /> Yes';
            }
            else
            {
                $out = '<input type="text" class="cn" name="save_con['.$config_name.']" value="'.$var.'" size="'.$opt.'" />';
            }
        }
        // Is array - SELECT
        elseif (is_array($options))
        {
            $out = '<select name="save_con['.$config_name.']">';
            foreach ($options as $key => $value)
            {
                if ($var == $key) $selected = ' selected="selected" '; else $selected = '';
                $out .= '<option value="'.$key.'"'.$selected.'>'.htmlspecialchars($value).'</option>';
            }
            $out .= '</select>';
        }

        // --- make line ---
        if ( ($counter++)%2 == 0) $bg = "bgcolor=#F7F6F4"; else $bg = "";
        return proc_tpl("options/syscon.row", array('bg' => $bg, 'title' => lang($title), 'field' => $out, 'description' => lang($desc)));
    }

    // ---------- show options
    echoheader("options", lang("System Configuration"), make_breadcrumbs($bc));
    echo proc_tpl('options/syscon.top', array('add_fields' => hook('field_options_buttons')));

    $skins = array();
    $dirs  = read_dir(SERVDIR."/skins", array(), false);
    foreach ($dirs as $skin_file) if (preg_match('/([^\/]+)\.skin\.php$/i', $skin_file, $c)) $skins[$c[1]] = $c[1];

    // General
    echo "<tr style='' id=general><td colspan=10 width=100%><table cellpadding=0 cellspacing=0 width=100%>";

    echo syscon('http_script_dir',  'Full URL to CuteNews directory|example: http://yoursite.com/cutenews');
    echo syscon('default_charset',  'Frontend default codepage|for example: windows-1251, utf-8, koi8-r etc');
    echo syscon('skin',             'CuteNews skin|you can download more from our website', $skins);
    echo syscon('useutf8',          'Use UTF-8|with this option, admin panel uses utf-8 charset', 'Y/N');
    echo syscon('utf8html',         "Don't convert UTF8 symbols to HTML entities|no conversion, e.g. &aring; to &amp;aring;", 'Y/N');
    echo syscon('use_wysiwyg',      "Use WYSIWYG Editor|use (or not) the advanced editor", array('no'=>'No', 'ckeditor'=>'CKEditor'));

    if (getoption('use_wysiwyg') == 'ckeditor')
        echo syscon('ckeditor_customize=50/12', 'Customize CKEditor|<a href="http://docs.cksource.com/CKEditor_3.x/Developers_Guide/Toolbar" target="_blank">CKEditor options</a>', ':text:');

    echo syscon('use_html',         "Use HTML in the article|if checked, CuteNews will parse the article as HTML elements + text, if unchecked - your article will be treated as pute text", 'Y/N');
    echo syscon('date_adjust=5',    'Time adjustment|in minutes; eg. : <b>180</b>=+3 hours; <b>-120</b>=-2 hours');
    echo syscon('smilies',          'Smilies|Separate them with commas (<b>,</b>)');
    echo syscon('auto_archive',         'Automatic archiving every month|Every month your active news will be archived', array('no'=>'No','yes'=>'Yes'));
    echo syscon('allow_registration',   'Allow self-Registration|allow users to register automatically', 'Y/N');
    echo syscon('registration_level',   'Self-registration level|choose your status', array(ACL_LEVEL_JOURNALIST=>"Journalist", ACL_LEVEL_COMMENTER=>"Commentator"));
    echo syscon('ban_attempts=5',       'Number of login attempts|specify the number of attempts to enter the password. Once it is exceeded, the account will be automatically banned for an hour.');
    echo syscon('xss_strict',           'XSS strict|if "strong", remove all suspicious parameters in tags', array(0=>"No", 1=>"Strong", 2=>"Total Filter"));
    echo syscon('use_replacement',      'Custom rewrite|allow rewrite news url path', 'Y/N');
    echo syscon('ipauth',               'Check IP|stronger authenticate (by changing this setting, you will be logged out)', 'Y/N');
    echo syscon('userlogs',             'Enable user logs|store user logs', 'Y/N');
    echo syscon('allowed_extensions',   'Allowed extensions|Used by file manager. Enter by comma without space');
    echo syscon('csrf',                 'Check CSRF|Protect your safety by checking cross-site request forgery', 'Y/N');

    hook('field_options_general');
    echo "</table></td></tr>";
    echo "<tr style='display:none' id=news><td colspan=10 width=100%><table cellpadding=0 cellspacing=0 width=100%>";

    echo syscon('use_avatar',           "Use avatars|if 'No', the avatar URL won't be shown", 'y/n');
    echo syscon('reverse_active',       'Reverse News|if yes, older news will be shown on the top', 'y/n');
    echo syscon('full_popup',           'Show full story in popup|full Story will be opened in PopUp window', 'y/n');
    echo syscon('full_popup_string',    "Settings for full story popup|only if 'Show Full Story In PopUp' is enabled");
    echo syscon('show_comments_with_full', 'Show comments when showing full story|if yes, comments will be shown under the story','y/n');
    echo syscon('timestamp_active=15',  'Time format for news|view help for time formatting <a href="http://www.php.net/manual/en/function.date.php" target="_blank">here</a>');
    echo syscon('backup_news',          'Make backup news|when you add or edit news, a backup is made', 'y/n');
    echo syscon('use_captcha',          'Use CAPTCHA|on registration and comments', 'Y/N');
    echo syscon('use_rater',            'Use rating|use internal rating system', 'Y/N');
    echo syscon('disable_pagination',   'Disable pagination|Use it to disable pagination', 'Y/N');

    if ($config_use_rater)
    {
        echo syscon('ratey=8', 'Rate symbol 1|rate full symbol');
        echo syscon('raten=8', 'Rate symbol 2|rate empty symbol');
    }
    hook('field_options_news');

    echo"</table></td></tr>";

    // Comments
    echo "<tr style='display: none' id=comments>";
    echo "<td colspan=10 width=100%><table cellpadding=0 cellspacing=0 width=100%>";

    echo syscon('auto_wrap=10', 'Auto wrap comments|any word that is longer than this will be wrapped');
    echo syscon('reverse_comments=10', 'Reverse comments|newest comments will be shown at the top','y/n');
    echo syscon('flood_time=10', 'Comments flood protection|in seconds; 0 = no protection');
    echo syscon('comment_max_long=10', 'Max. Length of comments in characters|enter <b>0</b> to disable checking');
    echo syscon('comments_per_page=10', 'Comments per page (pagination)|enter <b>0</b> or leave empty to disable pagination');
    echo syscon('only_registered_comment', 'Only registered users can post comments|if yes, only registered users can post comments','y/n');
    echo syscon('allow_url_instead_mail', 'Allow mail field to act as URL field|visitors will be able to put their site URL instead of an email','y/n');
    echo syscon('comments_popup', 'Show comments in popup|comments will be opened in PopUp window','y/n');
    echo syscon('comments_popup_string', "Settings for comments popup|only if 'Show Comments In PopUp' is enabled");
    echo syscon('show_full_with_comments', 'Show full story when showing comments|if yes, comments will be shown under the story','y/n');
    echo syscon('timestamp_comment=15', 'Time format for comments|view help for time formatting <a href="http://www.php.net/manual/en/function.date.php" target="_blank">here</a>');

    hook('field_options_comments');

    echo"</table></td></tr>";

    // Notifications
    echo "<tr style='display:none' id=notifications><td colspan=10 width=100%><table cellpadding=0 cellspacing=0 width=100%>";

    echo syscon('notify_status', 'Notifications - Active/Disabled|global status of notifications', array("active"=>"Active","disabled"=>"Disabled"));
    echo syscon('notify_registration', 'Notify of new registrations|automatic registration of new users','y/n');
    echo syscon('notify_comment', 'Notify of new comments|when new comment is added','y/n');
    echo syscon('notify_unapproved', 'Notify of unapproved news|when unapproved article is posted (by journalists)','y/n');
    echo syscon('notify_archive', 'Notify of auto-archiving|when (if) news are auto-archived','y/n');
    echo syscon('notify_postponed', 'Notify of activated postponed articles|when postponed article is activated','y/n');
    echo syscon('notify_email', 'Email(s)|where the notification will be send, separate multyple emails by comma');

    hook('field_options_notifications');
    echo "</table></td></tr>";

    // Facebook preferences
    $config_fb_comments     = $config_fb_comments ? $config_fb_comments : 4;
    $config_fb_box_width    = $config_fb_box_width ? $config_fb_box_width : 470;
    $config_fb_i18n         = empty($config_fb_i18n) ? 'en_US' : $config_fb_i18n;

    echo "<tr style='display:none' id='social'><td colspan=10 width=100%>";
    echo "<div class='consys_sub'>Facebook:</div>";
    echo "<table cellpadding=0 cellspacing=0 width=100%>";
    echo syscon('fb_i18n', 'Facebook i18n code|by default en_US');
    echo syscon('fb_appid', "Facebook appID|Get your AppId <a href='https://developers.facebook.com/apps' target='_blank'>there</a>");
    echo "</table>";

    // Facebook comments
    echo "<div class='consys_sub'>Facebook comments:</div>";
    echo "<table cellpadding=0 cellspacing=0 width=100%>";
    echo syscon('use_fbcomments', 'Use facebook comments for post|if yes, facebook comments will be shown','y/n');
    echo syscon('fb_inactive', 'In active news|Show in active news list','y/n');
    echo syscon('fb_comments=5', 'Comments number|Count comment under top box');
    echo syscon('fb_box_width=5', 'Box width|In pixels');
    echo syscon('fbcomments_color', 'Color scheme|The color scheme of the plugin', array("light"=>"Light","dark"=>"Dark"));
    echo "</table>";

    // Facebook like button
    echo "<div class='consys_sub'>Facebook like button:</div>";
    echo "<table cellpadding=0 cellspacing=0 width=100%>";
    echo syscon('use_fblike', 'Use facebook like button|if yes, facebook button will be shown','y/n');
    echo syscon('fblike_send_btn', 'Send Button|include a send button','y/n');
    echo syscon('fblike_style', 'Layout style|determines the size and amount of social context next to the button', array("standard"=>"standard","button_count"=>"button_count", "box_count"=>"box_count"));
    echo syscon('fblike_width=5', 'Box width|In pixels');
    echo syscon('fblike_show_faces', 'Show faces|if yes, profile pictures below the button will be shown','y/n');
    echo syscon('fblike_font', 'Font|The font of the plugin', array("arial"=>"Arial","lucida grande"=>"Lucida grande", "segoe ui"=>"Segoe ui", "tahoma"=>"Tahoma", "trebuchet ms"=>"Trebuchet ms", "verdana"=>"Verdana"));
    echo syscon('fblike_color', 'Color scheme|The color scheme of the plugin', array("light"=>"Light","dark"=>"Dark"));
    echo syscon('fblike_verb', 'Verb to display|The verb to display in the button', array("like"=>"Like","recommend"=>"Recommend"));
    echo "</table>";

    // Twitter share button
    echo "<div class='consys_sub'>Twitter button:</div>";
    echo "<table cellpadding=0 cellspacing=0 width=100%>";
    echo syscon('use_twitter', 'Use twitter button|if yes, twitter button will be shown','y/n');
    echo syscon('tw_url=15', 'Share URL|if empty, use the page URL');
    echo syscon('tw_text=15', 'Tweet text|if empty, use the title of the page');
    echo syscon('tw_show_count', 'Show count|if yes, count of tweets will be shown near button', array("horisontal"=>"Horisontal", "vertical"=>"Vertical", "none"=>"None"));
    echo syscon('tw_via=10', 'Via @|Screen name of the user to attribute the Tweet to');
    echo syscon('tw_recommended=10', 'Recommended @|Accounts suggested to the user after tweeting, comma-separated.');
    echo syscon('tw_hashtag=10', 'Hashtag #|Comma-separated hashtags appended to the tweet text');
    echo syscon('tw_large', 'Large button|if yes, the twitter button will be large', 'y/n');
    echo syscon('tw_lang', 'Language|The language of button text', array("en"=>"English", "fr"=>"French", "ar"=>"Arabic","ja"=>"Japanese","es"=>"Spanish","de"=>"German","it"=>"Italian","id"=>"Indonesian","pt"=>"Portuguese","ko"=>"Korean","tr"=>"Turkish","ru"=>"Russian","nl"=>"Dutch","fil"=>"Filipino","msa"=>"Malay","zh-tw"=>"Traditional Chinese","zh-cn"=>"Simplified Chinese","hi"=>"Hindi","no"=>"Norwegian","sv"=>"Swedish","fi"=>"Finnish","da"=>"Danish","pl"=>"Polish","hu"=>"Hungarian","fa"=>"Farsi","he"=>"Hebrew","ur"=>"Urdu","th"=>"Thai","uk"=>"Ukrainian","ca"=>"Catalan","el"=>"Greek","eu"=>"Basque","cs"=>"Czech","gl"=>"Galician","ro"=>"Romanian"));

    hook('field_options_social');


    echo "</table></td></tr>";

    hook('field_options_additional');

    echo "
    <input type=hidden id=currentid name=current value=general>
    <input type=hidden name=mod value=options>
    <input type=hidden name=action value=dosavesyscon>".
    showRow("", "", "<br /><input style='font-weight:bold;font-size:120%;' type=submit value=\"     Save Changes     \" accesskey=\"s\">")."
    </form></table>";

    // select tabs ----------------
echo <<<HTML
    <script type="text/javascript">
           var iof = document.location.toString();
           if (iof.indexOf('#') > 0) ChangeOption(iof.substr(iof.indexOf('#') + 1));
    </script>
HTML;

    echofooter();
}
// ********************************************************************************
// Save System Configuration
// ********************************************************************************
elseif ($action == "dosavesyscon")
{
    CSRFCheck();

    $config_filter = array();

    // Sanitize skin var
    $save_con["skin"] = preg_replace('~[^a-z0-9_.]~i', '', $save_con["skin"]);
    if (!file_exists(SERVDIR."/skins/".$save_con["skin"].".skin.php")) $save_con['skin'] = 'default';

    if ($member_db[UDB_ACL] != 1)
        msg("error", lang("Access Denied"), lang("You don't have permission for this section"), '#GOBACK');

    $handler = fopen(SERVDIR."/cdata/config.php", "w");
    fwrite ($handler, "<?php \n\n//System Configurations (Auto Generated file)\n");
    foreach($save_con as $name => $value)
    {
        $value = str_replace(array('$', '"'), '', $value);
        fwrite($handler, "\$config_$name = \"".$value."\";\n");
    }
    fwrite($handler, "?>");
    fclose($handler);

    add_to_log($member_db[UDB_NAME], lang('Update system configurations'));

    relocation(PHP_SELF.'?mod=options&action=syscon&message=1#'.$_REQUEST['current']);
    include (SERVDIR."/skins/".$save_con["skin"].".skin.php");
}

hook('options_additional_actions');