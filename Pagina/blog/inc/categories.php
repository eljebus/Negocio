<?PHP

if (!defined('INIT_INSTANCE')) die('Access restricted');

$result = false;
if ($member_db[UDB_ACL] != ACL_LEVEL_ADMIN)
    msg("error", "Access Denied", "You don't have permission to edit categories");

// ********************************************************************************
// Add Category
// ********************************************************************************
if ($action == "add")
{
    CSRFCheck();

    $cat_name   = str_replace('<'.'?', '', $cat_name);
    $cat_icon   = str_replace('<'.'?', '', $cat_icon);
    $cat_access = str_replace('<'.'?', '', $cat_access);
    $cat_name   = htmlspecialchars(stripslashes($cat_name));

    if(!$cat_name) msg("error", lang('Error!'), "Please enter name of the category", "#GOBACK");

    $cat_icon = preg_replace("/ /", "", $cat_icon);
    if ($cat_icon == "(optional)") $cat_icon = "";

    $big_num = file(SERVDIR."/cdata/cat.num.php");
    $big_num = $big_num[0];
    if (!$big_num or $big_num == "") $big_num = 1;

    $all_cats = file(SERVDIR."/cdata/category.db.php");
    foreach($all_cats as $cat_line)
    {
        $cat_arr = explode("|", $cat_line);
        if ($cat_arr[1] == $cat_name) msg("error", lang('Error!'), "Category with this name already exist", '#GOBACK');
        if ($cat_arr[0] == $big_num)  $big_num = 33;
    }
    $new_cats = fopen(SERVDIR."/cdata/category.db.php", "a");
    $cat_name = stripslashes( preg_replace(array("'\|'",), array("&#124",), $cat_name) );
    $cat_icon = stripslashes( preg_replace(array("'\|'",), array("&#124",), $cat_icon) );
    fwrite($new_cats, "$big_num|$cat_name|$cat_icon|$cat_access|||\n");
    fclose($new_cats);
    $big_num ++;

    $num_file = fopen(SERVDIR."/cdata/cat.num.php", "w");
    fwrite($num_file, $big_num);
    fclose($num_file);
}
// ********************************************************************************
// Remove Category
// ********************************************************************************
elseif ($action == "remove")
{
    if (!$catid) msg("error", lang('Error!'), "No category ID", '#GOBACK');

    $old_cats = file(SERVDIR."/cdata/category.db.php");
    $new_cats = fopen(SERVDIR."/cdata/category.db.php", "w");

    foreach ($old_cats as $old_cats_line)
    {
        $cat_arr = explode("|", $old_cats_line);
        if($cat_arr[0] != $catid) fwrite($new_cats, $old_cats_line);
    }
    fclose($new_cats);
}
// ********************************************************************************
// Edit Category
// ********************************************************************************
elseif ($action == "edit")
{
    $CSRF = CSRFMake();
    if (!$catid) msg("error", lang('Error!'), "No category ID", '#GOBACK');

    $all_cats = file(SERVDIR."/cdata/category.db.php");
    foreach($all_cats as $cat_line)
    {
        $cat_arr = explode("|", $cat_line);
        if ($cat_arr[0] == $catid)
        {
            $if_all_access  = empty($cat_arr[3])    ? "selected" :  "";
            $if_1_access    = ($cat_arr[3] == "1")  ? "selected" :  "";
            $if_2_access    = ($cat_arr[3] == "2")  ? "selected" :  "";

            $msg = proc_tpl('category/edit',
                      array('cat_arr[1]'     => $cat_arr[1],
                            'cat_arr[2]'     => $cat_arr[2],
                            'catid'          => $catid,
                            'if_all_access'  => $if_all_access,
                            'if_2_access'    => $if_2_access,
                            'if_1_access'    => $if_1_access,
                            'CSRF'           => $CSRF)
            );

            msg("options", lang("Edit Category"), $msg);
        }
    }
}
// ********************************************************************************
// DO Edit Category
// ********************************************************************************
elseif($action == "doedit")
{
    CSRFCheck();
    $cat_name   = str_replace('<'.'?', '', $cat_name);
    $cat_icon   = str_replace('<'.'?', '', $cat_icon);
    $cat_access = str_replace('<'.'?', '', $cat_access);
    $cat_name   = htmlspecialchars(stripslashes($cat_name));

    if (!$catid) msg("error", lang('Error!'), lang("No category ID"), '#GOBACK');
    if ($cat_name == "") msg("error", lang('Error!'), lang("Category name cannot be blank"), "#GOBACK");

    $old_cats = file(SERVDIR."/cdata/category.db.php");
    $new_cats = fopen(SERVDIR."/cdata/category.db.php", "w");
    foreach($old_cats as $cat_line)
    {
        $cat_arr = explode("|", $cat_line);
        if ( $cat_arr[0] == $catid )
             fwrite($new_cats, "$catid|$cat_name|$cat_icon|$cat_access|||\n");
        else fwrite($new_cats, $cat_line);

    }
    fclose($new_cats);
}
// ********************************************************************************
// List all Categories
// ********************************************************************************
$CSRF = CSRFMake();
echoheader("options", "Categories", make_breadcrumbs('main/options=options/Manage Categories'));

$count_categories = 0;
$all_cats = hook('read_categories', file(SERVDIR."/cdata/category.db.php"));

foreach($all_cats as $cat_line)
{
    if ($i++%2 != 0) $bg = "bgcolor=#F7F6F4"; else $bg = "";

    $cat_arr            = explode("|", $cat_line);
    $cat_arr[1]         = stripslashes( preg_replace(array("'\"'", "'\''"), array("&quot;", "&#039;"), $cat_arr[1]) );
    $cat_help_names[]   = $cat_arr[1];
    $cat_help_ids[]     = $cat_arr[0];

    $result .= "<tr><td $bg>&nbsp;<b>$cat_arr[0]</b></td><td $bg >$cat_arr[1]</td> <td $bg align=center>";
    if ($cat_arr[2] != "") $result .= "<img border=0 src=\"$cat_arr[2]\" high=40 width=40 alt=\"$cat_arr[2]\">"; else $result .= "---";
    $result .= "</td><td $bg align=center>";

    $result .= ($cat_arr[3] == "" || $cat_arr[3] == "0") ? "<span title='".lang('Everyone can Write')."'>---</span>" :  "";
    $result .= ($cat_arr[3] == "1") ? lang("Only Admin") :  "";
    $result .= ($cat_arr[3] == "2") ? lang("Only Editors & Admin") :  "";

    $result .= "</td> <td $bg align=center>
                    <a href=\"$PHP_SELF?mod=categories&action=edit&amp;catid=$cat_arr[0]\">[".lang('edit')."]</a>
                    <a href=\"$PHP_SELF?mod=categories&action=remove&amp;catid=$cat_arr[0]\">[".lang('delete')."]</a></td> </tr>";

    $count_categories ++;
}

if ($count_categories == 0)
    $result = "<tr><td colspan='5'><p><br><b>".lang("You haven't defined any categories yet")."</b><br>".lang("categories are optional and you can write your news without having categories")."<br></p></td></tr>";

echo proc_tpl('category/index', array('result' => $result, 'CSRF' => $CSRF));

echofooter();