<?PHP

if (!defined('INIT_INSTANCE')) die('Access restricted');

// ********************************************************************************
// CuteCode
// ********************************************************************************
if ($action == "cutecode")
{
    $read = proc_tpl('about/cutecode', array('target' => $target));
    echo $read;
}
else
{
    echoheader("question", lang("Help/About"));

    if ( function_exists("md5") )
         $functions_md5 = md5( join('', file( SERVDIR."/core/core.php")) );
    else $functions_md5 = "MD5NotSupported";

    // Try license key
    if ( file_exists(SERVDIR."/cdata/reg.php") ) include(SERVDIR."/cdata/reg.php");

    $read = proc_tpl
    (
        'about/index',
        array(  'config_version_name'   => $config_version_name,
                'config_version_id'     => $config_version_id,
                'config_http_script_dir'=> $config_http_script_dir,
                'functions_md5'         => $functions_md5,
                'reg_site_key'          => $reg_site_key,
             ),

        array('REG' => file_exists(SERVDIR.'/cdata/reg.php'))
    );

    echo $read;

    echofooter();
}

?>