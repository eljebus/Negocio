<?php

    if (!defined('INIT_INSTANCE')) die('Access restricted');

    // In case if uses MOD-Rewrite, variables must be come from external url
    if ($config_use_replacement)
    {
        $R = $_SERVER['REQUEST_URI'];
        if (preg_match('~^.*?\?(.*)$~', $_SERVER['REQUEST_URI'], $c ))
        {
            foreach ( explode('&', $c[1]) as $cv )
            {
                list($k, $v) = explode('=', $cv, 2);
                if ($k) $_REQUEST[$k] = $_GET[$k] = $v;
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    if (isset($_COOKIE)) extract($_COOKIE, EXTR_SKIP);
    if (isset($_POST))   extract($_POST,   EXTR_SKIP);
    if (isset($_GET))    extract($_GET,    EXTR_SKIP);

    //-------------------
    // Sanitize Variables
    //-------------------
    if (isset($template) and $template and !preg_match('/^[_a-zA-Z0-9-]{1,}$/', $template))
        die_stat(503, 'invalid template characters');

    if (isset($archive) and $archive and !preg_match('/^[_a-zA-Z0-9-]{1,}$/', $archive))
        die_stat(503, 'invalid archive characters');

    $a7f89abdcf9324b3       = "";
    $phpversion             = phpversion();
    $config_version_name    = "CuteNews v".VERSION;
    $config_version_id      = VERSION_ID;
    $comm_start_from        = htmlspecialchars($comm_start_from);
    $start_from             = htmlspecialchars($start_from);
    $archive                = htmlspecialchars(trim($archive));
    $subaction              = htmlspecialchars(trim($subaction));
    $id                     = htmlspecialchars($id);
    $ucat                   = htmlspecialchars($ucat);
    $number                 = htmlspecialchars($number);
    $template               = htmlspecialchars($template);
    $show                   = htmlspecialchars($show);

    // Only if not exists or PHP_SELF is empty
    if (empty($PHP_SELF))   $PHP_SELF = PHP_SELF;

    if  (is_array($category))
         foreach ($category as $k => $v) $category[$k] = htmlspecialchars($v);
    else $category = htmlspecialchars($category);

    // XSS Config skin
    $config_skin = preg_replace('~[^a-z]~i','', $config_skin);

    // Try loading template (loading safe default template)
    include (SERVDIR.'/skins/base_skin/install/copy/Default.tpl');

    if (empty($template)) $template = 'Default';
    if (file_exists( SERVDIR."/cdata/$template.tpl")) require( SERVDIR."/cdata/$template.tpl");

    hook('loadenv');