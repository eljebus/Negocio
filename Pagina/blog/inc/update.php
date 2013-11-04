<?php

if (!defined('INIT_INSTANCE')) die('Access restricted');

// Only admin there
if ($member_db[UDB_ACL] != ACL_LEVEL_ADMIN)
    msg("error", lang("Access Denied"), lang("You don't have permission for this section"));

if (ini_get('allow_url_fopen') == 0)
    msg("error", lang("Access Denied"), lang("Please check 'allow_url_fopen' option in php.ini file."));


function create_random_string($length, $word_max_length)
{
    $possible_symbols = 'ABCDEFGHIJKLMNOPQRSTUWXYZabcdefghijklmnopqrstuwxyz0123456789';
    $rand_max = strlen($possible_symbols)-1;
    $result = "";
    $symbol_count = 0;
    $new_word = true;
    $word_count = $word_max_length;
    while (strlen($result) < $length)
    {
        if ($new_word)
        {
            $word_count = rand(1, $word_max_length);
            $new_word = false;
        }

        $cur_symbol_id = rand(0, $rand_max);
        $result .= $possible_symbols[$cur_symbol_id];
        $symbol_count++;

        if ($symbol_count == $word_count)
        {
            $result .= ' ';
            $symbol_count = 0;
            $new_word = true;
        }
    }
    return $result;
}

if ($action == 'update')
{
    $need_update = false;
    $last_version_file = fopen("http://cutephp.com/cutenews/latest_version.php", "r");
    ob_start();
    fpassthru($last_version_file);
    list($last_version, $last_version_name) = explode('|', ob_get_clean());

    if ($last_version > $config_version_id)
        $need_update = true;

    if ($need_update)
    {
        $update_key = base64_encode(create_random_string(50, 7));
        $update_temp = fopen(SERVDIR.'/cdata/update_temp.php', "w");
        fwrite($update_temp, "<?php\n\$update_key='".$update_key."';\n?>");
        fclose($update_temp);
        setcookie('update', $update_key, time() + 60*60, '/');
        echoheader('info', lang("Update status"), make_breadcrumbs('main/options=options/Update Status'));
        echo proc_tpl('update/status');
        echofooter();
    }
    else
        msg('info', lang('Update status'), lang('No update: your revision is the latest one'));
}
?>