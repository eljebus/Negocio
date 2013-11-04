<?php

    /* Check PHP Version */
    if ( substr(PHP_VERSION, 0, 5) < '4.0.3') die('PHP Version is '.PHP_VERSION.', need great than PHP &gt;= 4.0.3 for start cutenews');

    // Remove simple error
    error_reporting(E_ALL ^ E_NOTICE);

    // DEFINITIONS
    define('EXEC_TIME',               microtime(true));

    // BASE SETTINGS
    define('VERSION',                 '1.5.3');
    define('VERSION_ID',              191);

    define('SERVDIR',                 dirname(dirname(__FILE__).'.html'));
    define('SKINS',                   '/skins');

    // DEBUG
    define('STORE_ERRORS',            true);

    // CRYPT SETTINGS
    define('HASH_METHOD',             'sha256'); // hash_algos()

    // ACL: base level
    define('ACL_LEVEL_ADMIN',         1);
    define('ACL_LEVEL_EDITOR',        2);
    define('ACL_LEVEL_JOURNALIST',    3);
    define('ACL_LEVEL_COMMENTER',     4);

    // Define user.db.php column
    define('UDB_ID',                  0); // add time
    define('UDB_ACL',                 1); // acl = 1,2,3,4
    define('UDB_NAME',                2); // username
    define('UDB_PASS',                3); // password (md5, sha-256, etc)
    define('UDB_NICK',                4); // nickname
    define('UDB_EMAIL',               5); // email
    define('UDB_COUNT',               6); // count of written news
    define('UDB_CBYEMAIL',            7); // user wants to hide his e-mail
    define('UDB_AVATAR',              8); // default avatar user for write news
    define('UDB_LAST',                9); // last login timestamp
    define('UDB_RESERVED1',           10);
    define('UDB_RESERVED2',           11);
    define('UDB_RESERVED3',           12);

    // Define news.db.php columns
    define('NEW_ID',                  0);
    define('NEW_USER',                1);
    define('NEW_TITLE',               2);
    define('NEW_SHORT',               3);
    define('NEW_FULL',                4);
    define('NEW_AVATAR',              5);
    define('NEW_CAT',                 6);
    define('NEW_RATE',                7); // rating function
    define('NEW_MF',                  8); // more fields
    define('NEW_OPT',                 9); // optins

    // define cats
    define('CAT_ID',                  0);
    define('CAT_NAME',                1);
    define('CAT_ICON',                2);
    define('CAT_PERM',                3);

    // define comments
    define('COM_ID',                  0);
    define('COM_USER',                1);
    define('COM_MAIL',                2);
    define('COM_IP',                  3);
    define('COM_TEXT',                4);

    define('INIT_INSTANCE',           1);
    define('EMAIL_FORCE_LOG',         0);

    // -----------------------------------------------------------------------------------------------------------------

    // include necessary libs
    include_once (SERVDIR.'/core/core.php');

    // catch errors
    set_error_handler("user_error_handler");

    // Off magic_quotes
    ini_set ('magic_quotes_gpc', 0);

    // configuration files
    if (file_exists(SERVDIR.'/cdata/config.php'))
        include_once (SERVDIR.'/cdata/config.php');

    // make default config after update from 1.4.x
    if (!isset($config_default_charset))    $config_default_charset = 0;
    if (!isset($config_useutf8))            $config_useutf8 = 0;
    if (!isset($config_utf8html))           $config_utf8html = 0;
    if (!isset($config_use_replacement))    $config_use_replacement = 0;
    if (!isset($config_ipauth))             $config_ipauth = 0;
    if (!isset($config_xss_strict))         $config_xss_strict = 0;
    if (!isset($config_userlogs))           $config_userlogs = 0;
    if (!isset($config_backup_news))        $config_backup_news = 'yes';
    if (!isset($config_use_captcha))        $config_use_captcha = 0;
    if (!isset($config_use_rater))          $config_use_rater = 0;
    if (!isset($config_use_fbcomments))     $config_use_fbcomments = 'no';
    if (!isset($config_fb_i18n))            $config_fb_i18n = 'en_US';
    if (!isset($config_fb_inactive))        $config_fb_inactive = 'yes';
    if (!isset($config_fb_comments))        $config_fb_comments = '4';
    if (!isset($config_fb_box_width))       $config_fb_box_width = '470';
    if (!isset($config_fbcomments_color))   $config_fbcomments_color = 'light';
    if (!isset($config_fb_appid))           $config_fb_appid = '';
    if (empty($config_ban_attempts))        $config_ban_attempts = 5;
    if (!isset($config_use_fblike))         $config_use_fblike = "no";
    if (!isset($config_fblike_send_btn))    $config_fblike_send_btn = "no";
    if (!isset($config_fblike_style))       $config_fblike_style = "standard";
    if (!isset($config_fblike_width))       $config_fblike_width = "450";
    if (!isset($config_fblike_show_faces))  $config_fblike_show_faces = "no";
    if (!isset($config_fblike_font))        $config_fblike_font = "arial";
    if (!isset($config_fblike_color))       $config_fblike_color = "light";
    if (!isset($config_fblike_verb))        $config_fblike_verb = "like";
    if (!isset($config_use_twitter))        $config_use_twitter = "no";
    if (!isset($config_tw_url))             $config_tw_url = "";
    if (!isset($config_tw_text))            $config_tw_text = "";
    if (!isset($config_tw_show_count))      $config_tw_show_count = "none";
    if (!isset($config_tw_via))             $config_tw_via = "";
    if (!isset($config_tw_recommended))     $config_tw_recommended = "";
    if (!isset($config_tw_hashtag))         $config_tw_hashtag = "";
    if (!isset($config_tw_large))           $config_tw_large = "no";
    if (!isset($config_tw_lang))            $config_tw_lang = "en";
    if (!isset($config_disable_pagination)) $config_disable_pagination = 0;
    if (empty($config_allowed_extensions))  $config_allowed_extensions = "gif,jpg,png,bmp,jpe,jpeg";
    if (empty($config_csrf))                $config_csrf = 0;

    // adjust timezone
    if (function_exists('date_default_timezone_set'))
        date_default_timezone_set( empty($config_timezone)?  'Europe/London' : $config_timezone );

    // embedded code no send codes
    if (empty($NotHeaders) && $config_useutf8 == '1')
    {
        header('Content-Type: text/html; charset=UTF-8', true);
        header('Accept-Charset: UTF-8', true);
    }

    // loading plugins
    $_HOOKS = array();
    if (is_dir(SERVDIR.'/cdata/plugins'))
        foreach (read_dir(SERVDIR.'/cdata/plugins', array(), false) as $plugin)
            if (preg_match('~\.php$~i', $plugin)) include (SERVDIR . $plugin);

    // load config
    $cfg = array();
    if (file_exists(SERVDIR . '/cdata/conf.php'))
        $cfg = unserialize( str_replace("<?php die(); ?>\n", '', implode('', file ( SERVDIR . '/cdata/conf.php' ))) );
    else $cfg = array();

    // initialize mod_rewrite if present
    if  ($config_use_replacement && file_exists(SERVDIR.'/cdata/conf_rw.php'))
         include ( SERVDIR.'/cdata/conf_rw.php' );

    // check skin if exists
    $config_skin = preg_replace('~[^a-z]~i','', $config_skin);
    if (!isset($config_skin) or !$config_skin or !file_exists(SERVDIR."/skins/$config_skin.skin.php"))
    {
        $using_safe_skin = true;
        $config_skin = 'default';
    }

    // Detect My IP and check it
    if (isset($HTTP_X_FORWARDED_FOR)) $ip = $HTTP_X_FORWARDED_FOR;
    elseif (isset($HTTP_CLIENT_IP))   $ip = $HTTP_CLIENT_IP;
    if (empty($ip))                   $ip = $_SERVER['REMOTE_ADDR'];
    if (empty($ip))                   $ip = false;

    if ( !preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $ip) )
         $ip = false;

    // use default, hooked or cfg skin
    if ( $SKIN = hook('change_skin') )
         define('SKIN', $SKIN);
    else define('SKIN', SKINS.'/'.(isset($cfg['skin'])? $cfg['skin'] : 'base_skin'));

    // Definity PHPSELF
    if ( !isset($PHP_SELF) && empty($PHP_SELF) )
    {
         $PHP_SELF = $_SERVER["SCRIPT_NAME"];
         define('PHP_SELF', $PHP_SELF);
    }
    else define('PHP_SELF', $PHP_SELF);

    // CRYPT_SALT consist an IP?
    define('CRYPT_SALT',        ($config_ipauth == '1'? $ip : false).'@'.$cfg['crypt_salt']);

    // experimental defines
    define('RATEY_SYMBOL',      empty($config_ratey) ? '*' : str_replace('&amp;', '&', $config_ratey) ); // &#9734;
    define('RATEN_SYMBOL',      empty($config_raten) ? '&ndash;' : str_replace('&amp;', '&', $config_raten) ); // &#9733;

    // SERVER values make
    $_SERVER["HTTP_ACCEPT"]             = isset($_SERVER["HTTP_ACCEPT"])?           $_SERVER["HTTP_ACCEPT"] : false;
    $_SERVER["HTTP_ACCEPT_CHARSET"]     = isset($_SERVER["HTTP_ACCEPT_CHARSET"])?   $_SERVER["HTTP_ACCEPT_CHARSET"] : false;
    $_SERVER["HTTP_ACCEPT_ENCODING"]    = isset($_SERVER["HTTP_ACCEPT_ENCODING"])?  $_SERVER["HTTP_ACCEPT_ENCODING"] : false;
    $_SERVER["HTTP_CONNECTION"]         = isset($_SERVER["HTTP_CONNECTION"])?       $_SERVER["HTTP_CONNECTION"] : false;

    // Cookies
    if (isset($_COOKIE['session']) && $_COOKIE['session'])
    {
        $xb64d = xxtea_decrypt( base64_decode( strtr($_COOKIE['session'], '-_.', '=/+') ), CRYPT_SALT );
        if ($xb64d) $_SESS = unserialize( $xb64d ); else $_SESS = array();
    }
    else
    {
        $_SESS = array();
    }

    // create cache
    $_CACHE = array();

    // save cfg file
    $cfg = hook('init_modify_cfg', $cfg);

    $fx = fopen(SERVDIR.'/cdata/conf.php', 'w');
    fwrite($fx, "<?php die(); ?>\n" . serialize($cfg) );
    fclose($fx);

    // More default options
    if (!getoption('ckeditor_customize')) $config_ckeditor_customize = read_tpl('default/ckeditor.options');

    //----------------------------------
    // Html Special Chars (HEX -> UTF-8) L-Endian
    //----------------------------------
    $HTML_SPECIAL_CHARS_UTF8 = array
    (
        'c2a1' => '&iexcl;',
        'c2a2' => '&cent;',
        'c2a3' => '&pound;',
        'c2a4' => '&curren;',
        'c2a5' => '&yen;',
        'c2a6' => '&brvbar;',
        'c2a7' => '&sect;',
        'c2a8' => '&uml;',
        'c2a9' => '&copy;',
        'c2aa' => '&ordf;',
        'c2ab' => '&laquo;',
        'c2bb' => '&raquo;',
        'c2ac' => '&not;',
        'c2ae' => '&reg;',
        'c2af' => '&macr;',
        'c2b0' => '&deg;',
        'c2ba' => '&ordm;',
        'c2b1' => '&plusmn;',
        'c2b9' => '&sup1;',
        'c2b2' => '&sup2;',
        'c2b3' => '&sup3;',
        'c2b4' => '&acute;',
        'c2b7' => '&middot;',
        'c2b8' => '&cedil;',
        'c2bc' => '&frac14;',
        'c2bd' => '&frac12;',
        'c2be' => '&frac34;',
        'c2bf' => '&iquest;',
        'c380' => '&Agrave;',
        'c381' => '&Aacute;',
        'c382' => '&Acirc;',
        'c383' => '&Atilde;',
        'c384' => '&Auml;',
        'c385' => '&Aring;',
        'c386' => '&AElig;',
        'c387' => '&Ccedil;',
        'c388' => '&Egrave;',
        'c389' => '&Eacute;',
        'c38a' => '&Ecirc;',
        'c38b' => '&Euml;',
        'c38c' => '&Igrave;',
        'c38d' => '&Iacute;',
        'c38e' => '&Icirc;',
        'c38f' => '&Iuml;',
        'c390' => '&ETH;',
        'c391' => '&Ntilde;',
        'c392' => '&Ograve;',
        'c393' => '&Oacute;',
        'c394' => '&Ocirc;',
        'c395' => '&Otilde;',
        'c396' => '&Ouml;',
        'c397' => '&times;',
        'c398' => '&Oslash;',
        'c399' => '&Ugrave;',
        'c39a' => '&Uacute;',
        'c39b' => '&Ucirc;',
        'c39c' => '&Uuml;',
        'c39d' => '&Yacute;',
        'c39e' => '&THORN;',
        'c39f' => '&szlig;',
        'c3a0' => '&agrave;',
        'c3a1' => '&aacute;',
        'c3a2' => '&acirc;',
        'c3a3' => '&atilde;',
        'c3a4' => '&auml;',
        'c3a5' => '&aring;',
        'c3a6' => '&aelig;',
        'c3a7' => '&ccedil;',
        'c3a8' => '&egrave;',
        'c3a9' => '&eacute;',
        'c3aa' => '&ecirc;',
        'c3ab' => '&euml;',
        'c3ac' => '&igrave;',
        'c3ad' => '&iacute;',
        'c3ae' => '&icirc;',
        'c3af' => '&iuml;',
        'c3b0' => '&eth;',
        'c3b1' => '&ntilde;',
        'c3b2' => '&ograve;',
        'c3b3' => '&oacute;',
        'c3b4' => '&ocirc;',
        'c3b5' => '&otilde;',
        'c3b6' => '&ouml;',
        'c3b7' => '&divide;',
        'c3b8' => '&oslash;',
        'c3b9' => '&ugrave;',
        'c3ba' => '&uacute;',
        'c3bb' => '&ucirc;',
        'c3bc' => '&uuml;',
        'c3bd' => '&yacute;',
        'c3be' => '&thorn;',
        'c3bf' => '&yuml;',
        'c592' => '&OElig;',
        'c593' => '&oelig;',
        'c5a0' => '&Scaron;',
        'c5a1' => '&scaron;',
        'c5b8' => '&Yuml;',
        'cb86' => '&circ;',
        'cb9c' => '&tilde;',
        'c692' => '&fnof;',
        'ce91' => '&Alpha;',
        'ce92' => '&Beta;',
        'ce93' => '&Gamma;',
        'ce94' => '&Delta;',
        'ce95' => '&Epsilon;',
        'ce96' => '&Zeta;',
        'ce97' => '&Eta;',
        'ce98' => '&Theta;',
        'ce99' => '&Iota;',
        'ce9a' => '&Kappa;',
        'ce9b' => '&Lambda;',
        'ce9c' => '&Mu;',
        'ce9d' => '&Nu;',
        'ce9e' => '&Xi;',
        'ce9f' => '&Omicron;',
        'cea0' => '&Pi;',
        'cea1' => '&Rho;',
        'cea3' => '&Sigma;',
        'cea4' => '&Tau;',
        'cea5' => '&Upsilon;',
        'cea6' => '&Phi;',
        'cea7' => '&Chi;',
        'cea8' => '&Psi;',
        'cea9' => '&Omega;',
        'ceb1' => '&alpha;',
        'ceb2' => '&beta;',
        'ceb3' => '&gamma;',
        'ceb4' => '&delta;',
        'ceb5' => '&epsilon;',
        'ceb6' => '&zeta;',
        'ceb7' => '&eta;',
        'ceb8' => '&theta;',
        'ceb9' => '&iota;',
        'ceba' => '&kappa;',
        'cebb' => '&lambda;',
        'cebc' => '&mu;',
        'cebd' => '&nu;',
        'cebe' => '&xi;',
        'cebf' => '&omicron;',
        'cf80' => '&pi;',
        'cf81' => '&rho;',
        'cf82' => '&sigmaf;',
        'cf83' => '&sigma;',
        'cf84' => '&tau;',
        'cf85' => '&upsilon;',
        'cf86' => '&phi;',
        'cf87' => '&chi;',
        'cf88' => '&psi;',
        'cf89' => '&omega;',
        'cf91' => '&thetasym;',
        'cf92' => '&upsih;',
        'cf96' => '&piv;',
        'e2809d' => '&rdquo;',
        'e2809c' => '&ldquo;',
        'e284a2' => '&trade;',
        'e28099' => '&rsquo;',
        'e28098' => '&lsquo;',
        'e280b0' => '&permil;',
        'e280a6' => '&hellip;',
        'e282ac' => '&euro;',
        'e28093' => '&ndash;',
        'e28094' => '&mdash;',
        'e280a0' => '&dagger;',
        'e280a1' => '&Dagger;',
        'e280b9' => '&lsaquo;',
        'e280ba' => '&rsaquo;',
        'e280b2' => '&prime;',
        'e280b3' => '&Prime;',
        'e280be' => '&oline;',
        'e28498' => '&weierp;',
        'e28491' => '&image;',
        'e2849c' => '&real;',
        'e284b5' => '&alefsym;',
        'e28690' => '&larr;',
        'e28691' => '&uarr;',
        'e28692' => '&rarr;',
        'e28693' => '&darr;',
        'e28694' => '&harr;',
        'e286b5' => '&crarr;',
        'e28790' => '&lArr;',
        'e28791' => '&uArr;',
        'e28792' => '&rArr;',
        'e28793' => '&dArr;',
        'e28794' => '&hArr;',
        'e28880' => '&forall;',
        'e28882' => '&part;',
        'e28883' => '&exist;',
        'e28885' => '&empty;',
        'e28887' => '&nabla;',
        'e28888' => '&isin;',
        'e28889' => '&notin;',
        'e2888b' => '&ni;',
        'e2888f' => '&prod;',
        'e28891' => '&sum;',
        'e28892' => '&minus;',
        'e28897' => '&lowast;',
        'e2889a' => '&radic;',
        'e2889d' => '&prop;',
        'e2889e' => '&infin;',
        'e288a0' => '&ang;',
        'e288a7' => '&and;',
        'e288a8' => '&or;',
        'e288a9' => '&cap;',
        'e288aa' => '&cup;',
        'e288ab' => '&int;',
        'e288b4' => '&there4;',
        'e288bc' => '&sim;',
        'e28985' => '&cong;',
        'e28988' => '&asymp;',
        'e289a0' => '&ne;',
        'e289a1' => '&equiv;',
        'e289a4' => '&le;',
        'e289a5' => '&ge;',
        'e28a82' => '&sub;',
        'e28a83' => '&sup;',
        'e28a84' => '&nsub;',
        'e28a86' => '&sube;',
        'e28a87' => '&supe;',
        'e28a95' => '&oplus;',
        'e28a97' => '&otimes;',
        'e28aa5' => '&perp;',
        'e28b85' => '&sdot;',
        'e28c88' => '&lceil;',
        'e28c89' => '&rceil;',
        'e28c8a' => '&lfloor;',
        'e28c8b' => '&rfloor;',
        'e29fa8' => '&lang;',
        'e29fa9' => '&rang;',
        'e2978a' => '&loz;',
        'e299a0' => '&spades;',
        'e299a3' => '&clubs;',
        'e299a5' => '&hearts;',
        'e299a6' => '&diams;',
    );

    // Decode UTF-8 code-table
    $HTML_SPECIAL_CHARS = array();
    foreach ($HTML_SPECIAL_CHARS_UTF8 as $hex => $html)
    {
        $key = '';
        if (strlen($hex) == 4)      $key = pack("CC",  hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)));
        elseif (strlen($hex) == 6)  $key = pack("CCC", hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));

        if ($key) $HTML_SPECIAL_CHARS[$key] = $html;
    }

    hook('init_header_after');

?>