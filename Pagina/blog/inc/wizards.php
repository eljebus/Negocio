<?PHP

if (!defined('INIT_INSTANCE')) die('Access restricted');

if ($member_db[UDB_ACL] != ACL_LEVEL_ADMIN)
    msg("error", lang("Access Denied"), lang("You don't have permission for this section"));

if ($action == "" or !$action)
{
    echoheader("wizard", lang("Choose Wizard"), make_breadcrumbs('main/options=options/Choose Wizard'));
    echo proc_tpl('wizard/menu');
    echofooter();
}
// ********************************************************************************
// Initiate the News Integration Wizard
// ********************************************************************************
elseif ($action == "news")
{
    //Gather the Templates
    $templates_list = array();
    if (!$handle = opendir(SERVDIR."/cdata"))
        die("Cannot open directory ".SERVDIR."/cdata ");

    while (false !== ($file = readdir($handle)))
    {
        if (preg_replace('/^.*\.(.*?)$/', '\\1', $file) == 'tpl')
        {
            $file_arr           = explode(".", $file);
            $templates_list[]   = $file_arr[0];
        }
    }
    closedir($handle);

    $templates_html = "<select name=w_template>";
    foreach($templates_list as $single_template)
    {
        if ($single_template != "rss")
        {
            if ($single_template == "Default")
                 $templates_html .= "<option selected value=\"$single_template\">$single_template</option>";
            else $templates_html .= "<option value=\"$single_template\">$single_template</option>";
        }
    }
    $templates_html .= "</select>";

    //Gather the Categories
    $cat_lines = file(SERVDIR."/cdata/category.db.php");
    if ($cat_lines)
    {
        $cat_html = "<select style='display:;' name=w_category[] id=category multiple>";
        foreach ($cat_lines as $single_line)
        {
            $cat_arr    = explode("|", $single_line);
            $cat_html   .= "<option value=\"$cat_arr[0]\">(ID:$cat_arr[0]) $cat_arr[1]</option>\n";
        }
        $cat_html .= "</select><br><label for=allcategory><input id=allcategory onClick=\"if(this.checked){getElementById('category').style.display='none';}else{getElementById('category').style.display='';}\" type=checkbox value='yes' name='w_allcategory'> Or Show from All Categories</label>";
    }
    else $cat_html = "You have no categories";


    echoheader("wizard", "News Integration Wizard", make_breadcrumbs('main/options=options/wizards=Choose Wizards/Integration'));
    echo proc_tpl('wizard/news', array('templates_html' => $templates_html, 'cat_html' => $cat_html));
    echofooter();
}
// ********************************************************************************
// Show The News Integration Code
// ********************************************************************************
elseif ($action == "news_step2")
{
    echoheader("wizard", lang("News Integration"), make_breadcrumbs('main/options=options/wizards=Choose Wizards/wizards:news=Integration/Complete'));

    // Try to determine include path
    $the_code = '&lt;?php'."\n";
    $include_path = dirname(dirname(__FILE__)) .'/show_news.php';

    if ($w_number and $w_number != '')
        $the_code .= '$number="'.$w_number."\";\n";

    if ($w_template != 'Default')
        $the_code .= '$template="'.$w_template."\";\n";

    // Get ready with Categories (if any)
    if ($w_allcategory != 'yes' and isset($w_category) and $w_category != '')
    {
        $i = 0;
        $my_category = join(',', $w_category);
        $the_code .= '$category="'.$my_category."\";\n";
    }

    if ($w_reverse == 'yes')                    $the_code .= "\$reverse=TRUE;\n";
    if ($w_only_active == 'yes')                $the_code .= "\$only_active=TRUE;\n";
    if ($w_static == 'yes')                     $the_code .= "\$static=TRUE;\n";
    if ($w_start_from and $w_start_from != '')  $the_code .= "\$start_from=\"$w_start_from\";\n";

    $the_code .= "include(\"$include_path\");\n?&gt;";
    echo "CuteNews determined your full path to show_news.php to be: '<b>$include_path</b>'<br>
    If for some reasons the include path is incorrect or does not work, please determine<br>
    the relative path for including <i>show_news.php</i> yourself or
    consult your administrator.<br><br>
    To show your news, insert (copy & paste) the code into some of your pages (*.php) :<br><br>
    <textarea style='font-weight: bold;' cols=70 rows=10>$the_code</textarea><br>";

    echofooter();
}
// ********************************************************************************
// Initiate the RSS Wizard
// ********************************************************************************
elseif ($action == "rss")
{
    echoheader("wizard", lang("RSS Set-Up Wizard"), make_breadcrumbs('main/options=options/wizards=Choose Wizards/Rss Setup'));

    echo "Rich Site Summary (sometimes referred to as Really Simple Syndication);<br>
    RSS allows a web developer to share the content on his/her site. RSS repackages the web content <br>
    as a list of data items, to which you can subscribe from a directory of RSS publishers. <br>
    RSS 'feeds' can be read with a web browser or special RSS reader called a content aggregator.
    <br><br><input onClick=\"document.location='$PHP_SELF?mod=wizards&action=rss_step2';\" type=button value='Proceed with RSS Configuration >>'><br><br>";

    echofooter();
}
// ********************************************************************************
// Show the RSS config
// ********************************************************************************
elseif ($action == "rss_step2")
{
    include(SERVDIR."/cdata/rss_config.php");

    if ($rss_language == '' or !$rss_language) $rss_language = 'en-us';
    if ($rss_encoding == '' or !$rss_encoding) $rss_encoding = 'UTF-8';

    echoheader("wizard", lang("RSS Configuration"), make_breadcrumbs('main/options=options/wizards=Choose Wizards/wizards:rss=Rss Setup/Configuration'));

    echo proc_tpl('wizard/rss_step2', array
    (
        'config_http_script_dir' => $config_http_script_dir,
        'rss_news_include_url' => $rss_news_include_url,
        'rss_title' => $rss_title,
        'rss_encoding' => $rss_encoding,
        'rss_language' => $rss_language,
    ));

    echofooter();
}

// ********************************************************************************
// Save the RSS Configuration
// ********************************************************************************
elseif ($action == "dosaverss")
{
    if (strpos($rss_news_include_url, 'http://') === false)
        msg("error",  lang('Error!'), lang("The URL where you include your news must start with <b>http://</b>"));

    $handler = fopen(SERVDIR."/cdata/rss_config.php", "w") or msg("error",  lang('Error!'), "Cannot open file ./cdata/rss_config.php");
    fwrite($handler, "<?PHP \n\n//RSS Configurations (Auto Generated file)\n\n");

    fwrite($handler, "\$rss_news_include_url = \"".htmlspecialchars($rss_news_include_url)."\";\n\n");
    fwrite($handler, "\$rss_title = \"".htmlspecialchars($rss_title)."\";\n\n");
    fwrite($handler, "\$rss_encoding = \"".htmlspecialchars($rss_encoding)."\";\n\n");
    fwrite($handler, "\$rss_language = \"".htmlspecialchars($rss_language)."\";\n\n");

    fwrite($handler, "?>");
    fclose($handler);

    msg("wizard", lang("RSS Configuration Saved"), lang("The configurations were saved successfully").".<br><br><input onClick=\"document.location='$PHP_SELF?mod=wizards&action=customizerss';\" type=button value='Proceed With RSS Customization >>'>");
}
// ********************************************************************************
// Save the RSS Configuration
// ********************************************************************************
elseif ($action == "customizerss")
{
    echoheader("wizard", lang("RSS Customization"), make_breadcrumbs('main/options=options/wizards=Choose Wizards/wizards:rss=Rss Setup/wizards:rss_step2=Configuration/Complete'));

    // Detect the categories (if any)
    $cat_lines = file(SERVDIR."/cdata/category.db.php");
    if(count($cat_lines) > 0)
    {
        $cat_options .= '<select style="" id=categories multiple size=5>'."\n";
        foreach ($cat_lines as $single_line)
        {
            $cat_arr = explode("|", $single_line);
            $cat_options .= "<option value=\"$cat_arr[0]\">(ID:$cat_arr[0]) $cat_arr[1]</option>\n";
        }
        $cat_options .= "</select><br><label for=allcategories><input onclick=\"if(this.checked){getElementById('categories').style.display='none';}else{getElementById('categories').style.display='';}\" type=checkbox id=allcategories value=yes>".lang('Or show from all Categories')."</label>";

    }
    else $cat_options = lang("You do not have any categories").". <input type=hidden id=categories><input type=hidden id=allcategories>";

    // Show the HTML
    echo proc_tpl('wizard/customizerss', array('config_http_script_dir' => $config_http_script_dir, 'cat_options' => $cat_options));
    echofooter();
}
