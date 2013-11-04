<?php

if (!defined('INIT_INSTANCE')) die('Access restricted');

// Strong check for deprecated -----------------------------------------------------------------------------------------
function deprecated_check()
{
    // In 1.5.0b has exists temporary db.users
    if (file_exists(SERVDIR.'/cdata/db.users.php'))
    {
        $users = file(SERVDIR.'/cdata/db.users.php');
        unset($users[0]);
        foreach ($users as $v)
        {
            list(,$b) = explode('|', $v, 2);
            $b = unserialize($b);
            if ( user_search($b[UDB_NAME]) == false ) user_add($b);
        }
    }
}

// DEBUG functions -----------------------------------------------------------------------------------------------------

// error_dump.log always 0600 for deny of all
// User-defined error handler for catch errors
function user_error_handler($errno, $errmsg, $filename, $linenum, $vars)
{
    global $config_time_adjust;
    $errtypes = array
    (
        E_ERROR             => "Error",
        E_WARNING           => "Warning",
        E_PARSE             => "Parsing Error",
        E_NOTICE            => "Notice",
        E_CORE_ERROR        => "Core Error",
        E_CORE_WARNING      => "Core Warning",
        E_COMPILE_ERROR     => "Compile Error",
        E_COMPILE_WARNING   => "Compile Warning",
        E_USER_ERROR        => "User Error",
        E_USER_WARNING      => "User Warning",
        E_USER_NOTICE       => "User Notice",
        E_STRICT            => "Runtime Notice",
        E_DEPRECATED        => "Deprecated"
    );

    // E_NOTICE skip
    if ($errno == E_NOTICE) return;
    
    $out = $errtypes[$errno].': '.$errmsg.'; '.trim($filename).':'.$linenum.";";
    $out = str_replace(array("\n", "\r", "\t"), ' ', $out);

    // Store data
    if (defined('STORE_ERRORS') && STORE_ERRORS)
    {
        $str = trim(str_replace(array("\n","\r",SERVDIR), array(" ", " ", ''), $out));
        if (is_writable(SERVDIR.'/cdata/log'))
        {
            $need_time = time() + $config_time_adjust*60;
            $log = fopen(SERVDIR.'/cdata/log/error_dump.log', 'a');
            fwrite($log, $need_time.'|'.date('Y-m-d H:i:s', $need_time).'|'.$str."\n");
            fclose($log);
        }
    }

}

function die_stat($No, $Reason = false)
{
    global $config_time_adjust;
    $HTTP = array
    (
        0   => '',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        503 => '503 Service Unavailable',
    );

    $Response = isset($HTTP[$No])? $HTTP[$No] : $HTTP[503];

    if ($No)
    {
        header('HTTP/1.1 '.$Response, true);
        echo $Reason? '<h2>'.$Response.'</h2>'.$Reason : '<h2>'.$Response.'</h2>';
    }
    else echo $Reason;

    // Log stat
    if (defined('STORE_ERRORS') && STORE_ERRORS)
    {
        if (is_writable(SERVDIR.'/cdata/log'))
        {
            $need_time = time() + $config_time_adjust*60;
            $log = fopen(SERVDIR.'/cdata/log/error_dump.log', 'a');
            fwrite($log, $need_time.'|'.date('Y-m-d H:i:s', $need_time).'|DIE_STAT: '.$No.'; '.str_replace(array("\n","\r",SERVDIR), array(" ", " ", ''), $Reason)."\n");
            fclose($log);
        }
    }
    die();
}

function getoption($opt_name)
{
    return isset($GLOBALS["config_$opt_name"]) ? $GLOBALS["config_$opt_name"] : FALSE;
}

// Modified from http://en.wikibooks.org/wiki/Algorithm_implementation/Sorting/Quicksort#PHP for quicksort cutenews
// $order = A(ascending), D(escending)
// Usage: 0-7/A or 0-7/D

function quicksort($array, $by = 0)
{
    $bysort = $by;
    list ($by, $ord) = explode('/', $by);
    if (count($array) < 2) return $array;

    $left = $right = array();

    reset($array);
    $pivot_key  = key($array);
    $pivot      = array_shift($array);
    $pivox      = explode('|', $pivot);

    foreach ($array as $k => $v)
    {
        $vx = explode('|', $v);
        if ($ord == 'A' || $ord == 'asc')
             { if ($vx[$by] < $pivox[$by]) $left[$k] = $v; else $right[$k] = $v; }
        else { if ($vx[$by] > $pivox[$by]) $left[$k] = $v; else $right[$k] = $v; }
    }

    return array_merge(quicksort($left, $bysort), array($pivot_key => $pivot), quicksort($right, $bysort));
}

// SKINS functions -----------------------------------------------------------------------------------------------------

// Simply read template file
function read_tpl($tpl = 'index')
{
    global $_CACHE;

    // get from cache
    if (isset($_CACHE['tpl_'.$tpl]))
        return $_CACHE['tpl_'.$tpl];

    // Get plugin patch
    if  ($tpl[0] == '/')
         $open = SERVDIR.'/cdata/plugins/'.substr($tpl,1).'.tpl';
    else $open = SERVDIR.SKIN.'/'.($tpl?$tpl:'default').'.tpl';

    // Try open
    $not_open = false;
    $r = fopen($open, 'r') or $not_open = true;
    if ($not_open) return false;

    ob_start();
    fpassthru($r);
    $ob = ob_get_clean();
    fclose($r);

    // cache file
    $_CACHE['tpl_'.$tpl] = $ob;
    return $ob;
}

// More process for template {$args}, {$ifs}
function proc_tpl($tpl, $args = array(), $ifs = array())
{
    // predefined arguments
    $args['PHP_SELF'] = PHP_SELF;

    // Globals are saved too
    foreach ($GLOBALS as $gi => $gv)
    {
        if ( in_array($gi, array('session', '_CACHE', '_HOOKS', 'HTML_SPECIAL_CHARS', '_SESS',
                                 'GLOBALS', '_ENV', '_REQUEST', '_SERVER', '_FILES', '_COOKIE', '_POST', '_GET')))
             continue;

        if (!isset($args[$gi])) $args[$gi] = $gv;
    }

    // reading template 
    $d = read_tpl($tpl);

    // Replace if constructions {VAR}....{/VAR} if set $ifs['VAR'] : {-VAR}...{/-VAR} if no isset $ifs['VAR']
    foreach ($ifs as $i => $v)
    {
        $r = isset($v) && $v ? $v : false;
        $d = preg_replace('~{'.$i.'}(.*?){/'.$i.'}~s', ($r?"\\1":''), $d);
        $d = preg_replace('~{\-'.$i.'}(.*?){/\-'.$i.'}~s', ($r?'':"\\1"), $d);
    }

    // Replace variables in $args
    $keys = $vals = array();
    foreach ($args as $i => $v)
    {
        $keys[] = '{$'.$i.'}';
        $vals[] = $v;
    }
    $d = str_replace($keys, $vals, $d);

    // Catch Foreach Cycles
    if ( preg_match_all('~{foreach from\=([^}]+)}(.*?){/foreach}~is', $d, $rep, PREG_SET_ORDER) )
    {
        foreach ($rep as $v)
        {
            $rpl = false;
            if (is_array($args[ $v[1] ]))
            {
                foreach ($args[ $v[1] ] as $x)
                {
                    $bulk = $v[2];

                    // String simply replaces {$FromValue.}, Array -> {$FromValue.Precise}
                    if  (is_array($x))
                         foreach ($x as $ik => $iv) $bulk = str_replace('{$'.$v[1].".$ik}", $iv, $bulk);
                    else $bulk = str_replace('{$'.$v[1].".}", $x, $bulk);

                    $rpl .= $bulk;
                }
            }

            $d = str_replace($v[0], $rpl, $d);
        }
    }

    // Catch {if} constructions
    if ( preg_match_all('~{if\s+(.*?)}(.*?){/if}~is', $d, $rep, PREG_SET_ORDER))
    {
        foreach ($rep as $vs)
        {
            $var = 0;
            $vs[1] = trim($vs[1]);
            if     ($vs[1][0] == '$') $var = $args[ substr($vs[1], 1) ];
            elseif ($vs[1][1] == '$') $var = $args[ substr($vs[1], 2) ];

            // If boolean logic OK, replace
            if ($vs[1][0] == '$' && $var)            $d = str_replace($vs[0], $vs[2], $d);
            elseif ($vs[1][0] == '!' && empty($var)) $d = str_replace($vs[0], $vs[2], $d);
            else $d = str_replace($vs[0], false, $d);
        }
    }

    // Skins lang support
    if ( preg_match_all('~{{(.*?)}}~i', $d, $rep, PREG_SET_ORDER) )
    {
        foreach ($rep as $v)
            $d = str_replace($v[0], lang($v[1]), $d);
    }

    // override process template (filter)
    list($d) = hook('func_proc_tpl', array($d, $tpl, $args, $ifs));

    // truncate unused
    $d = preg_replace('~{\$[^}]+}+~s', '', $d);

    // replace all
    return ( $d );
}

// Return say value of lang if present
// Replaces %1, %2...%n from func_num_args
function lang($say)
{
    global $lang;
    $say = hook('lang_say_before', $say);

    if (func_num_args() > 1)
    {
        for ($i = 1; $i < func_num_args(); $i++)
        {
            $axi = func_get_arg($i);
            $say = str_replace('%'.$i, $axi, $say);
        }
    }

    return hook('lang_say_after', empty($lang[strtolower($say)]) ? $say : $lang[strtolower($say)]);
}

function utf8_strtolower($utf8)
{
    global $HTML_SPECIAL_CHARS, $config_utf8html;

    // European languages to htmlentities
    if ($config_utf8html == 0)
    {
        $utf8 = strtolower( str_replace( array_keys($HTML_SPECIAL_CHARS), array_values($HTML_SPECIAL_CHARS), $utf8) );
    }

    // Rus Language translation
    $SPEC_TRANSLATE = explode('|', "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯ|абвгдеёжзийклмнопрстуфхцчшщьыъэюя");
    $utf8 =  str_replace( explode(' ', trim(preg_replace('~([\xD0][\x00-\xFF])~', '\\1 ', $SPEC_TRANSLATE[0]))),
                          explode(' ', trim(preg_replace('~([\xD0-\xD1][\x00-\xFF])~', '\\1 ', $SPEC_TRANSLATE[1]))),
                          $utf8);

    return $utf8;
}

// @url http://www.php.net/manual/de/function.utf8-decode.php#100478
function UTF8ToEntities ($string)
{
    global $config_useutf8;

    // Don't convert anything if $config_useutf8 = 0
    if ($config_useutf8 == '0') return $string;

    /* note: apply htmlspecialchars if desired /before/ applying this function
    /* Only do the slow convert if there are 8-bit characters */
    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
    if (! preg_match("~[\200-\237]~", $string) and ! preg_match("~[\241-\377]~", $string))
        return $string;

    // reject too-short sequences
    $string = preg_replace("/[\302-\375]([\001-\177])/", "&#65533;\\1", $string);
    $string = preg_replace("/[\340-\375].([\001-\177])/", "&#65533;\\1", $string);
    $string = preg_replace("/[\360-\375]..([\001-\177])/", "&#65533;\\1", $string);
    $string = preg_replace("/[\370-\375]...([\001-\177])/", "&#65533;\\1", $string);
    $string = preg_replace("/[\374-\375]....([\001-\177])/", "&#65533;\\1", $string);
    $string = preg_replace("/[\300-\301]./", "&#65533;", $string);
    $string = preg_replace("/\364[\220-\277]../", "&#65533;", $string);
    $string = preg_replace("/[\365-\367].../", "&#65533;", $string);
    $string = preg_replace("/[\370-\373]..../", "&#65533;", $string);
    $string = preg_replace("/[\374-\375]...../", "&#65533;", $string);
    $string = preg_replace("/[\376-\377]/", "&#65533;", $string);
    $string = preg_replace("/[\302-\364]{2,}/", "&#65533;", $string);

    // decode four byte unicode characters
    $string = preg_replace(
        "/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/e",
        "'&#'.((ord('\\1')&7)<<18 | (ord('\\2')&63)<<12 |" .
            " (ord('\\3')&63)<<6 | (ord('\\4')&63)).';'",
        $string);

    // decode three byte unicode characters
    $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",
        "'&#'.((ord('\\1')&15)<<12 | (ord('\\2')&63)<<6 | (ord('\\3')&63)).';'",
        $string);

    // decode two byte unicode characters
    $string = preg_replace("/([\300-\337])([\200-\277])/e",
        "'&#'.((ord('\\1')&31)<<6 | (ord('\\2')&63)).';'",
        $string);

    // reject leftover continuation bytes
    $string = preg_replace("/[\200-\277]/", "&#65533;", $string);

    return $string;
}

// XXTEA ---------------------------------------------------------------------------------------------------------------

function long2str($v, $w)
{
    $len = count($v);
    $n   = ($len - 1) << 2;
    if ($w)
    {
        $m = $v[$len - 1];
        if (($m < $n - 3) || ($m > $n)) return false;
        $n = $m;
    }

    $s = array();
    for ($i = 0; $i < $len; $i++) $s[$i] = pack("V", $v[$i]);
    if ($w) return substr(join('', $s), 0, $n);
    else    return join('', $s);

}

function str2long($s, $w)
{
    $v = unpack("V*", $s.str_repeat("\0", (4 - strlen($s) % 4) & 3));
    $v = array_values($v);
    if ($w) $v[count($v)] = strlen($s);
    return $v;
}

function int32($n)
{
    while ($n >= 2147483648)  $n -= 4294967296;
    while ($n <= -2147483649) $n += 4294967296;
    return (int)$n;
}

function xxtea_encrypt($str, $key)
{
    if ($str == "") return "";

    $v = str2long($str, true);
    $k = str2long($key, false);
    if (count($k) < 4) for ($i = count($k); $i < 4; $i++) $k[$i] = 0;

    $n      = count($v) - 1;
    $z      = $v[$n];
    $y      = $v[0];
    $delta  = 0x9E3779B9;
    $q      = floor(6 + 52 / ($n + 1));
    $sum    = 0;

    while (0 < $q--)
    {
        $sum = int32($sum + $delta);
        $e = $sum >> 2 & 3;
        for ($p = 0; $p < $n; $p++)
        {
            $y = $v[$p + 1];
            $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $z = $v[$p] = int32($v[$p] + $mx);
        }
        $y = $v[0];
        $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
        $z = $v[$n] = int32($v[$n] + $mx);
    }
    return long2str($v, false);
}

function xxtea_decrypt($str, $key)
{
    if ($str == "") return "";

    $v = str2long($str, false);
    $k = str2long($key, false);
    if (count($k) < 4) for ($i = count($k); $i < 4; $i++) $k[$i] = 0;

    $n      = count($v) - 1;
    $z      = $v[$n];
    $y      = $v[0];
    $delta  = 0x9E3779B9;
    $q      = floor(6 + 52 / ($n + 1));
    $sum    = int32($q * $delta);

    while ($sum != 0)
    {
        $e = $sum >> 2 & 3;
        for ($p = $n; $p > 0; $p--)
        {
            $z = $v[$p - 1];
            $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $y = $v[$p] = int32($v[$p] - $mx);
        }
        $z      = $v[$n];
        $mx     = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
        $y      = $v[0] = int32($v[0] - $mx);
        $sum    = int32($sum - $delta);
    }
    return long2str($v, true);
}

// Mail function -------------------------------------------------------------------------------------------------------
function send_mail($to, $subject, $message, $hdr = false)
{
    global $config_time_adjust;
    if (!isset($to)) return false;
    if (!$to) return false;

    $tos = spsep($to);
    $from = 'Cutenews <cutenews@'.$_SERVER['SERVER_NAME'].'>';

    $headers = '';
    $headers .= "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/plain;\n";
    $headers .= 'From: '.$from."\n";
    $headers .= 'Reply-to: '.$from."\n";
    $headers .= 'Return-Path: '.$from."\n";
    $headers .= 'Message-ID: <' . md5(uniqid(time() + $config_time_adjust*60)) . '@' . $_SERVER['SERVER_NAME'] . ">\n";
    $headers .= "X-Mailer: PHP/" . phpversion()."\n";
    $headers .= "Date: " . date('r', time() + $config_time_adjust*60) . "\n";
    $headers .= $hdr;

    foreach ($tos as $v)
    {
        if ($v)
        {
            $mx = false;
            $pt = SERVDIR.'/cdata/log/mail.log';
            $ms = "-------------\n".$headers."Subject: $subject\n\n".$message."\n\n";
            mail($v, $subject, $message, $headers) or $mx = true;
            if (defined('EMAIL_FORCE_LOG') && EMAIL_FORCE_LOG or $mx)
            {
                $log = fopen($pt, 'a+');
                fwrite($log, $ms);
                fclose($log);
            }
        }
    }

    return true;
}

function exec_time()
{
    echo "<!-- execution time: ".round(microtime(true) - EXEC_TIME, 3)." -->";
}

function send_cookie()
{
    global $_SESS;

    // String serialize
    $cookie = strtr(base64_encode( xxtea_encrypt(serialize($_SESS), CRYPT_SALT) ), '=/+', '-_.');

    // if remember flag exists
    if ( isset($_SESS['@']) && $_SESS['@'])
         setcookie('session', $cookie, time() + 60*60*24*30, '/');
    else setcookie('session', $cookie, 0, '/');
}

// hash type MD5 and SHA256
function hash_generate($password, $md5hash = false)
{
    $try = array
    (
        0 => md5($password),
        1 => utf8decrypt($password, $md5hash),
        2 => SHA256_hash($password),
    );

    return $try;
}

// UTF8-Cutenews compliant
function utf8decrypt($str, $oldhash)
{
    $len = strlen($str) * 3;
    while($len >= 16) $len -= 16;
    $len = floor($len / 2);

    $salt = substr($oldhash, $len, 10);
    $pass = SHA256_hash($salt.$str.'`>,');
    $pass = substr($pass, 0, $len).$salt.substr($pass, $len);

    return $pass;
}

// $rec = recursive scan
function read_dir($dir_name, $cdir = array(), $rec = true, $replacement = SERVDIR)
{
    $dir = opendir($dir_name);
    if (is_resource($dir))
    {
        while (false !== ($file = readdir($dir)))
        if ($file != "." and $file != "..")
        {
            $path = $dir_name.'/'.$file;
            if ( is_readable($path) )
            {
                if ( is_dir($path) && $rec) $cdir = read_dir($path, $cdir, true, $replacement);
                elseif (is_file($path)) $cdir[] = str_replace($replacement, '', $path);
            }
        }
        closedir($dir);
    }
    return $cdir;
}

function scan_dir($dir)
{
    $dh  = opendir($dir);
    while (false !== ($filename = readdir($dh))) {
        $files[] = $filename;
    }
    return $files;
}

function del_dir($dir) {
    $files = array_diff(scan_dir($dir), array('.','..'));

    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? del_dir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

// Add hook to system
function add_hook($hook, $func)
{
    global $_HOOKS;

    if (!isset($_HOOKS[$hook])) $_HOOKS[$hook] = array();
    $_HOOKS[$hook][] = $func;
}

// Cascade Hooks
function hook($hook, $args = null)
{
    global $_HOOKS;

    // Plugin hooks
    if (!empty($_HOOKS[$hook]) && is_array($_HOOKS[$hook]))
        foreach($_HOOKS[$hook] as $hookfunc)
            $args = call_user_func($hookfunc, $args);

    return $args;
}

// Do breadcrumbs as mod:action=Name/mod=Name/mod/=Name ($lbl = true -> last bc is link)
function make_breadcrumbs($bc, $lbl = false)
{
    $ex = explode('/', $bc);
    $bc = array();
    $cn = count($ex);

    foreach ($ex as $i => $v)
    {
        // simply bc
        if (preg_match('~^[\w\=\: ]*$~', $v))
        {
            list($link, $desc) = explode('=', $v, 2);
            list($link, $action) = explode(':', $link);

            // detect whitespaces
            if (!$desc) $desc = $link;
            if ($action) $link .= '&amp;action='.$action;

            if ( $i < $cn - 1 || $lbl)
                 $bc[] = '<a style="font-size: 15px;" href="'.PHP_SELF.'?mod='.$link.'">'.$desc.'</a>';
            else $bc[] = $desc;

        }
    }

    return '<div style="margin: 16px 64px 12px 0; padding: 0 0 4px 0; font-size: 15px; border-bottom: 1px solid #cccccc;">'.implode(' / ', $bc).'</div>';
}

// ---------------------------------------------------------------------------------------------------------------------

// Category ID to Name [convert to category name from ID]
function catid2name($thecat)
{
    global $cat;

    $nice = array();
    $cats = spsep($thecat);
    foreach ($cats as $cn) $nice[] = $cat[ trim($cn) ];
    return (implode (', ', $nice));
}

function my_strip_tags($d) { return preg_replace('/<[^>]*>/', '', $d); }

// Only Allowed Tags There....
function hesc($html)
{
    global $config_xss_strict;

    // XSS Strict off
    if ($config_xss_strict == 0)
        return $html;

    if ( preg_match_all('~<([^>]+)>~s', $html, $sets, PREG_SET_ORDER) )
    {
        $allowed_tags = explode(',', 'a,i,b,u,p,h1,h2,h3,h4,h5,h6,hr,ul,ol,br,li,tr,th,td,tt,sub,sup,img,big,div,code,span,abbr,code,acronym,address,blockquote,center,strike,strong,table,thead,object,iframe,param,embed');
        $events       = explode(',', 'onblur,onchange,onclick,ondblclick,onfocus,onkeydown,onkeypress,onkeyup,onload,onmousedown,onmousemove,onmouseout,onmouseover,onmouseup,onreset,onselect,onsubmit,onunload');

        foreach ($sets as $vs)
        {
            $disable  = false;
            list($tag) = explode(' ', strtolower($vs[1]), 2);
            $mtag = $tag[0] == '/'? substr($tag, 1) : $tag;

            // Very hard filter: only allowed tags
            if ($config_xss_strict == 2)
            {
                $disable = 1;
                if (in_array($mtag, $allowed_tags) == false) $disable = 2;
            }
            else
            {
                if (in_array($mtag, $allowed_tags) == false) $disable = 2;
                elseif (preg_match_all('~on\w+~i', $vs[0], $evt, PREG_SET_ORDER))
                    foreach ($evt as $ie) if (in_array($ie[0], $events)) { $disable = 1; break; }
            }

            if ($disable == 1) $html = str_replace($vs[0], '<'.$tag.'>', $html);
            if ($disable == 2) $html = str_replace($vs[0], false, $html);
        }
    }

    return $html;
}

// Make category icons
function caticon( $cats, $cat_icon, $cat )
{
    $cats = trim($cats);
    if (empty($cats)) return false;

    $result = false;
    foreach ( spsep($cats) as $cid )
    {
        if ($cat_icon[$cid])
            $result .= getpart( 'category_icon', array( $cat[ $cid ], $cat_icon[$cid] ) );
    }

    return $result;
}

/**
 * Create a web friendly URL slug from a string.
 *
 * Although supported, transliteration is discouraged because
 * 1) most web browsers support UTF-8 characters in URLs
 * 2) transliteration causes a loss of information
 *
 * @author Sean Murphy <sean@iamseanmurphy.com>
 * @copyright Copyright 2012 Sean Murphy. All rights reserved.
 * @license http://creativecommons.org/publicdomain/zero/1.0/
 *
 * @param string $str
 * @param array $options
 * @return string
 */
function url_slug($str, $options = array()) {
    // Make sure string is in UTF-8 and strip invalid UTF-8 characters
    $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

    $defaults = array(
        'delimiter' => '-',
        'limit' => null,
        'lowercase' => true,
        'replacements' => array(),
        'transliterate' => false,
    );

    // Merge options
    $options = array_merge($defaults, $options);

    $char_map = array(
        // Latin
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
        'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
        'ß' => 'ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
        'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
        'ÿ' => 'y',

        // Latin symbols
        '©' => '(c)',

        // Greek
        'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
        'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
        'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
        'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
        'Ϋ' => 'Y',
        'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
        'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
        'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
        'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
        'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

        // Turkish
        'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
        'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',

        // Russian
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
        'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
        'Я' => 'Ya',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
        'я' => 'ya',

        // Ukrainian
        'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
        'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

        // Czech
        'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
        'Ž' => 'Z',
        'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
        'ž' => 'z',

        // Polish
        'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
        'Ż' => 'Z',
        'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
        'ż' => 'z',

        // Latvian
        'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
        'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
        'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
        'š' => 's', 'ū' => 'u', 'ž' => 'z'
    );

    // Make custom replacements
    $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

    // Transliterate characters to ASCII
    if ($options['transliterate']) {
        $str = str_replace(array_keys($char_map), $char_map, $str);
    }

    // Replace non-alphanumeric characters with our delimiter
    $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

    // Remove duplicate delimiters
    $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

    // Truncate slug to max. characters
    $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

    // Remove delimiter from ends
    $str = trim($str, $options['delimiter']);

    return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
}

function titleToUrl($title)
{
    global $config_use_replacement;

    if($config_use_replacement == '0') return null;

    if(preg_match('|&#\d{0,4};|',trim($title)))
        $title = mb_convert_encoding($title, 'UTF-8', 'HTML-ENTITIES');

    return url_slug($title, array('delimiter' => '_', 'transliterate' => true, 'limit' => 40));
}

// Short Story or fullstory replacer -----------------------------------------------------------------------------------
function template_replacer_news($news_arr, $output)
{
    // Predefined Globals
    global $config_timestamp_active, $config_http_script_dir, $config_comments_popup, $config_comments_popup_string,
           $config_full_popup, $config_full_popup_string, $rss_news_include_url, $my_names, $my_start_from, $cat, $action,
           $cat_icon, $archive, $name_to_nick, $template, $user_query, $member_db, $_SESS, $PHP_SELF;

    // Short Story not exists
    if (empty($news_arr[NEW_FULL]) and (strpos($output, '{short-story}') === false) )
        $news_arr[NEW_FULL] = $news_arr[NEW_SHORT];

    $output = more_fields($news_arr[NEW_MF], $output);

    // Date Formatting [year, month, day, hour, minute, date=$config_timestamp_active]
    list($output, $news_arr) = hook('template_replacer_news_before', array($output, $news_arr));

    $output      = embedateformat($news_arr[NEW_ID], $output);

    // Replace news content
    $output      = str_replace("{title}",           hesc($news_arr[NEW_TITLE]), $output);
    $output      = str_replace("{author}",          $my_names[$news_arr[NEW_USER]] ? $my_names[$news_arr[NEW_USER]] : $news_arr[NEW_USER], $output);
    $output      = str_replace("{author-name}",     hesc($name_to_nick[$news_arr[NEW_USER]]), $output);
    $output      = str_replace("{short-story}",     hesc($news_arr[NEW_SHORT]), $output);
    $output      = str_replace("{full-story}",      hesc($news_arr[NEW_FULL]), $output);

    // Replace system information
    $output      = str_replace("{avatar-url}",      $news_arr[NEW_AVATAR], $output);
    $output      = str_replace("{category}",        hesc(catid2name($news_arr[NEW_CAT])), $output);
    $output      = str_replace("{category-url}",    linkedcat($news_arr[NEW_CAT]), $output);
    $output      = str_replace("{page-views}",      false, $output);
    $output      = str_replace("{phpself}",         $PHP_SELF, $output);
    $output      = str_replace("{index-link}",      '<a href="'.$PHP_SELF.'">'.lang('Go back').'</a>', $output);
    $output      = str_replace("{back-previous}",   '<a href="javascript:history.go(-1)">Go back</a>', $output);
    $output      = str_replace("{cute-http-path}",  $config_http_script_dir, $output);
    $output      = str_replace("{news-id}",         $news_arr[NEW_ID], $output);
    $output      = str_replace("{category-id}",     $news_arr[NEW_CAT], $output);
    $output      = str_replace("{comments-num}",    countComments($news_arr[NEW_ID], $archive), $output);
    $output      = str_replace("{archive-id}",      $archive, $output);
    $output      = str_replace("{category-icon}",   caticon( $news_arr[NEW_CAT], $cat_icon, $cat ), $output);
    $mf_for_avatar = options_extract($news_arr[NEW_MF]);
    if(array_key_exists('_avatar_width', $mf_for_avatar)) $width_for_avatar = 'width:'.$mf_for_avatar['_avatar_width'].';';
    if(array_key_exists('_avatar_height', $mf_for_avatar)) $height_for_avatar = 'height:'.$mf_for_avatar['_avatar_height'].';';
    $output      = str_replace("{avatar}",          $news_arr[NEW_AVATAR]? '<img alt="" src="'.$news_arr[NEW_AVATAR].'" style="border: none;'.$width_for_avatar.$height_for_avatar.'" />' : '', $output);

    $output      = preg_replace('/\[loggedin\](.*?)\[\/loggedin\]/is', empty($_SESS['user']) ? '' : '\\1', $output);

    // social plugins
    $output      = str_replace('{fb-comments}', show_social_code('fb', $news_arr), $output);
    $output      = str_replace('{fb-like}', show_social_code('fb-like', $news_arr), $output);
    $output      = str_replace('{twitter}', show_social_code('twitter', $news_arr), $output);

    // in RSS we need the date in specific format
    if ($template == 'rss')
    {
        $output = str_replace("{date}", date("r", $news_arr[0]), $output);
        $output = str_replace("{rss-news-include-url}", $rss_news_include_url ? $rss_news_include_url : $config_http_script_dir.'/router.php', $output);
    }
    else
    {
        $output = str_replace("{date}", date($config_timestamp_active, $news_arr[NEW_ID]), $output);
    }

    // Star Rating
    if ( empty($archive) )
         $output = str_replace("{star-rate}", rating_bar($news_arr[NEW_ID], $news_arr[NEW_RATE]), $output);
    else $output = str_replace("{star-rate}", false, $output);

    // Mail Exist in mailist ---------------------------------------------------- [mail]...[/mail]
    if ( !empty($my_mails[ $news_arr[NEW_USER] ]) )
         $output = str_replace( array("[mail]", '[/mail]'), array('<a href="mailto:'.$my_mails[ $news_arr[NEW_USER] ].'">', ''), $output);
    else $output = str_replace( array("[mail]", '[/mail]'), '', $output);

    // By click to comments - popup window -------------------------------------- [com-link]...[/com-link]
    if ( $config_comments_popup == "yes" )
    {
         $URL    = build_uri('subaction,id,ucat,start_from,template,archive', array('showcomments', $news_arr[NEW_ID], $news_arr[NEW_CAT], $my_start_from));
         $output = str_replace(array('[com-link]',
                                     '[/com-link]'),
                               array('<a href="#" onclick="window.open(\''.$config_http_script_dir.'/router.php'.$URL.'\', \'News\', \''.$config_comments_popup_string.'\'); return false;">',
                                     '</a>'), $output);
    }
    else
    {
        if($archive)
            $URL = RWU( 'archreadcomm', $PHP_SELF . build_uri('subaction,id,ucat,title,template,archive', array('showcomments', $news_arr[NEW_ID], $news_arr[NEW_CAT], titleToUrl($news_arr[NEW_TITLE]))) );
        else
            $URL = RWU( 'readcomm', $PHP_SELF . build_uri('subaction,id,ucat,title,template', array('showcomments', $news_arr[NEW_ID], $news_arr[NEW_CAT], titleToUrl($news_arr[NEW_TITLE]))) );
        $output = str_replace(array("[com-link]", '[/com-link]'), array("<a href=\"$URL\">", '</a>'), $output);
    }

    // Open link --------------------------------------------------------------- [link]...[/link]
    $URL     = build_uri('subaction,id,start_from,ucat,archive,template', array('showfull',$news_arr[NEW_ID],$my_start_from,$news_arr[NEW_CAT]));
    $URL    .= "&amp;#disqus_thread";

    $output  = preg_replace('/\[link target\=([a-z0-9_]+?)\](.*?)\[\/link\]/is', '<a href="'.$PHP_SELF.$URL.'" target="\\1">\\2</a>', $output);
    $output  = str_replace(array("[link]", "[/link]"), array('<a href="'.$PHP_SELF.$URL.'">', "</a>"), $output);

    // With Action = showheadlines -------------------------------------------- [full-link]...[/full-link]
    if ($news_arr[NEW_FULL] or $action == "showheadlines")
    {
        if ($config_full_popup == "yes")
        {
            $URL = build_uri('subaction,id,archive,template', array('showfull',$news_arr[NEW_ID],$archive,$template));

            // Popup string
            $attrlink = "href='#' onclick=\"window.open('$config_http_script_dir/router.php{$URL}', '_News', '$config_full_popup_string'); return false;\"";
            $output = preg_replace('/\[full\-link target\=([a-z0-9_]+?)\](.*?)\[\/full\-link\]/is', "<a $attrlink target='\\1'>\\2</a>", $output);
            $output = str_replace('[full-link]', "<a $attrlink>", $output);
        }
        else
        {
            $template = ($template == 'Default') ? '' : $template;
            if ($archive)
                $URL  = RWU( 'archreadmore', $PHP_SELF . build_uri('subaction,id,archive,ucat,title,template', array('showfull', $news_arr[0], $archive, $news_arr[NEW_CAT],titleToUrl($news_arr[NEW_TITLE]),$template)) . "&amp;$user_query" );
            else
                $URL  = RWU( 'readmore', $PHP_SELF . build_uri('subaction,id,ucat,title,template', array('showfull', $news_arr[0], $news_arr[NEW_CAT],titleToUrl($news_arr[NEW_TITLE]),$template)) . "&amp;$user_query" );

            // Target string
            $attrlink = "href='{$URL}'";
            $output = preg_replace('/\[full\-link target\=([a-z0-9_]+?)\](.*?)\[\/full\-link\]/is', "<a $attrlink target='\\1'>\\2</a>", $output);
            $output = str_replace("[full-link]", "<a $attrlink>", $output);
        }

        $output = str_replace("[/full-link]", "</a>", $output);
    }
    else
    {
        $output = preg_replace('~\[full-link.*?\].*?\[/full-link\]~si', '<!-- no full story-->', $output);
    }

    // Admin can edit for news ------------------------------------------------ [edit]...[/edit]
    $DREdit = false;
    if (empty($_SESS['user']) == false)
    {
        $member_db = user_search($_SESS['user']);
        if (in_array($member_db[UDB_ACL], array(ACL_LEVEL_ADMIN, ACL_LEVEL_JOURNALIST)))
        {
            $url    = '/index.php'.build_uri('mod,action,id,source', array('editnews','editnews',$news_arr[NEW_ID], $archive));
            $output = preg_replace('/\[edit\]/i', '<a target="_blank" href="'.$config_http_script_dir.$url.'">', $output);
            $output = preg_replace('/\[\/edit\]/i', '</a>', $output);
            $DREdit = true;
        }
    }

    // If not used, replace [edit]..[/edit]
    if ($DREdit == false) $output = preg_replace('~\[edit\].*?\[/edit\]~si', '', $output);

    list($output, $news_arr) = hook('template_replacer_news_middle', array($output, $news_arr));
    $output                  = replace_news("show", $output);
    list($output)            = hook('template_replacer_news_after', array($output, $news_arr));

    return $output;
}

// Extra Articles Fields
function more_fields($mf, $output)
{
    global $cfg;

    // if use more fields
    if ( !empty($cfg['more_fields']) && is_array($cfg['more_fields']) )
    {
        $artmore = explode(';', $mf);
        $isused = array();
        foreach ($artmore as $v)
        {
            list ($a, $b) = explode('=', $v, 2);
            $output = str_replace('{'.$a.'}', hesc($b), $output );
            $isused[$a] = true;
        }
        //delete unused fields
        if(count($isused) != count($cfg['more_fields']))
        {
            foreach ($cfg['more_fields'] as $fname => $ftitle)
            {
                if($isused[$fname]) continue;
                $output = str_replace('{'.$fname.'}', '', $output );
            }
        }
    }
    return $output;
}

function create_avatar_size_in_mf($avatar_option, $mfname, $mfvalue)
{
    global $cfg;

    if(!preg_match('/^\d{0,5}(?:px|%)?$/', trim($avatar_option)))
        return false;

    if($avatar_option && !isset($cfg['more_fields'][$mfname]))
    {
        $cfg['more_fields'][$mfname] = $mfvalue;

        // save
        $fx = fopen(SERVDIR.'/cdata/conf.php', 'w');
        fwrite($fx, "<?php die(); ?>\n" . serialize($cfg) );
        fclose($fx);
    }
    return true;
}


/*
 * Log, base on multifiles and md5 tells about day & hour for user login
 * Array search slice
 */
function add_to_log($username, $action)
{
    global $config_userlogs, $config_time_adjust;

    // Sanitize username
    $username = preg_replace('/[^a-z0-9_\- ]/i', '', $username);

    // User logs is disabled
    if ($config_userlogs == '0') return false;

    // authorization stat
    $locked = false;
    $need_time = time() + $config_time_adjust*60;
    $flog = SERVDIR.'/cdata/log/log_'.date('Y_m', $need_time).'.php';

    // create log file if not exists
    if ( !file_exists($flog) )
    {
        @fclose(@fopen($flog,'w'));
        @chmod ($flog, 0666);
    }

    if ( !file_exists($flog) ) return false;

    // add to log
    $log = fopen(SERVDIR.'/cdata/log/log_'.date('Y_m', $need_time).'.php', 'a');
    flock($log, LOCK_EX);
    fwrite($log, $need_time.'|'.serialize(array('user' => $username, 'action' => $action, 'time' => $need_time, 'ip' => $_SERVER['REMOTE_ADDR']))."\n");
    flock($log, LOCK_UN);
    fclose($log);

    return true;
}

// User-defined for date formatting
function format_date($time, $type = false)
{
    global $cfg, $config_date_adjust;

    // type format - since current time
    if ($type == 'since' || $type == 'since-short')
    {
        $dists = array
        (
            ' year(s) ' => 3600*24*365,
            ' month(s) ' => 3600*24*31,
            ' d. ' => 3600*24,
            ' h. ' => 3600,
            ' m. ' => 60,
        );

        $ago    = 'ago';
        $rd     = false;
        $need_time = time() + $config_date_adjust*60;
        $dist   = $need_time - $time;
        if ($dist < 0)
        {
            $ago  = 'after';
            $dist = -$dist;
        }

        $mids = $dist;
        foreach ($dists as $i => $v)
        {
            if ($dist > $v)
            {
                $X     = floor( $dist / $v );
                $rd   .= $X.$i;
                $dist -= $X * $v;
            }
        }

        $rd .= ($rd? '' : '0 m' ).' '.$ago. (($type == 'since' && $mids > 24*3600) ? ' at '.date('Y-m-d H:i', $need_time) : '');
        return $rd;
    }

    if (!isset($cfg['format_date'])) return date('r', $time);

    return $time;
}

/*
 * id=0...n-1
 * pt=1 (is digit), =0 (is ...)
 * cr=1 (current)
 */
function pagination($count, $per = 25, $current = 0, $spread = 5)
{

    $lists = array();
    $pages = (floor($count / $per) + (($count % $per) ?  1 : 0)) - 1;

    // check bounds
    $_ps = (($current - $spread) >= 0)? ($current - $spread) : 0;
    $_pe = (($current + $spread) <= $pages)? ($current + $spread) : $pages;

    if ($_ps)
    {
        $lists[] = array( 'id' => 0, 'pt' => 1, 'cr' => 0 );
        $lists[] = array( 'id' => $_ps - 1, 'pt' => 0, 'cr' => 0 );
    }

    for ($i = $_ps; $i <= $_pe; $i++)
    {
        $lists[] = array( 'id' => $i,
                          'pt' => 1,
                          'cr' => ($i == $current)? 1 : 0,
        );
    }
    if ($_pe < $pages)
    {
        $lists[] = array( 'id' => $_pe + 1, 'pt' => 0, 'cr' => 0 );
        $lists[] = array( 'id' => $pages, 'pt' => 1, 'cr' => 0 );
    }

    return $lists;
}

// make full URI (left & right parts)
function build_uri($left, $right, $html = 1)
{
    global $QUERY_STRING;

    $URI = $DDR = array();
    list ($left, $adds) = explode(':', $left);

    $ex = spsep($left);
    $uq = spsep($adds);

    // Main parameters get from
    if (!empty($left) && is_array($ex)) foreach ($ex as $i => $v)
    {
        // Value present in enum
        if (!empty($right[$i])) $URI[ $v ] = $right[$i];

        // Enum not present, but in GLOBALS is set
        elseif (!isset($right[$i]) && !empty($GLOBALS[$v])) $URI[$v] = $GLOBALS[$v];
    }

    // Enum not present, but in GLOBALS is set
    if (!empty($adds) && is_array($uq))
        foreach ($uq as $v) if (!empty($GLOBALS[$v])) $URI[ $v ] = $GLOBALS[$v];

    // Import at url $QUERY_STRING
    $QUERY_STRING = str_replace('&amp;', '&', $QUERY_STRING);
    foreach ( spsep($QUERY_STRING, '&') as $qs )
    {
        list($k, $v) = explode('=', $qs, 2);
        if ($k && $v) $URI[$k] = $v;
    }

    // Encode new query
    foreach ($URI as $i => $v)
    {
        $DDR[] = urlencode($i)."=".str_replace('%2C', ',', urlencode($v));
    }

    $DDU = implode(($html?'&amp;':'&'), $DDR);
    if ($DDU == false) return '?c';

    // Return true link
    return '?'.$DDU;
}

function embedateformat($timestamp, $output)
{
    // Months
    if ( preg_match_all('~{month(\|.*?)?}~i', $output, $monthd, PREG_SET_ORDER) )
    {
        foreach ($monthd as $v)
            if (empty($v[1])) $output = str_replace($v[0], date('F', $timestamp), $output);
            else
            {
                $monthlist = spsep(substr($v[1], 1));
                $output = str_replace($v[0], $monthlist[date('n', $timestamp)-1], $output);
            }
    }

    // Others parameters
    $output     = str_replace('{weekday}', date('l', $timestamp), $output);
    $output     = str_replace("{year}",    date("Y", $timestamp), $output);
    $output     = str_replace("{day}",     date("d", $timestamp), $output);
    $output     = str_replace("{hours}",   date("H", $timestamp), $output);
    $output     = str_replace("{minite}",  date("i", $timestamp), $output);

    $output     = str_replace("{since}",   format_date($timestamp, 'since-short'), $output);

    return $output;
}

// SHA256::hash --------------------------------------------------------------------------------------------------------
/*
 *  Based on http://csrc.nist.gov/cryptval/shs/sha256-384-512.pdf
 *
 *  © Copyright 2005 Developer's Network. All rights reserved.
 *  This is licensed under the Lesser General Public License (LGPL)
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 */

function SHA256_sum()
{
    $T = 0;
    for($x = 0, $y = func_num_args(); $x < $y; $x++)
    {
        $a = func_get_arg($x);
        $c = 0;
        for($i = 0; $i < 32; $i++)
        {
            //    sum of the bits at $i
            $j = (($T >> $i) & 1) + (($a >> $i) & 1) + $c;
            //    carry of the bits at $i
            $c = ($j >> 1) & 1;
            //    strip the carry
            $j &= 1;
            //    clear the bit
            $T &= ~(1 << $i);
            //    set the bit
            $T |= $j << $i;
        }
    }
    return $T;
}

function SHA256_hash($str)
{
    $chunks = null;
    $M = strlen($str);                //    number of bytes
    $L1 = ($M >> 28) & 0x0000000F;    //    top order bits
    $L2 = $M << 3;                    //    number of bits
    $l = pack('N*', $L1, $L2);
    $k = $L2 + 64 + 1 + 511;
    $k -= $k % 512 + $L2 + 64 + 1;
    $k >>= 3;                           //    convert to byte count
    $str .= chr(0x80) . str_repeat(chr(0), $k) . $l;
    preg_match_all( '#.{64}#', $str, $chunks );
    $chunks = $chunks[0];

    // H(0)
    $hash = array
    (
        (int)0x6A09E667, (int)0xBB67AE85,
        (int)0x3C6EF372, (int)0xA54FF53A,
        (int)0x510E527F, (int)0x9B05688C,
        (int)0x1F83D9AB, (int)0x5BE0CD19,
    );


    // Compute
    $vars = 'abcdefgh';
    $K = null;

    $a = $b = $c = $d = $e = $f = $h = $g = false;
    if($K === null)
    {
        $K = array(
            (int)0x428A2F98, (int)0x71374491, (int)0xB5C0FBCF, (int)0xE9B5DBA5,
            (int)0x3956C25B, (int)0x59F111F1, (int)0x923F82A4, (int)0xAB1C5ED5,
            (int)0xD807AA98, (int)0x12835B01, (int)0x243185BE, (int)0x550C7DC3,
            (int)0x72BE5D74, (int)0x80DEB1FE, (int)0x9BDC06A7, (int)0xC19BF174,
            (int)0xE49B69C1, (int)0xEFBE4786, (int)0x0FC19DC6, (int)0x240CA1CC,
            (int)0x2DE92C6F, (int)0x4A7484AA, (int)0x5CB0A9DC, (int)0x76F988DA,
            (int)0x983E5152, (int)0xA831C66D, (int)0xB00327C8, (int)0xBF597FC7,
            (int)0xC6E00BF3, (int)0xD5A79147, (int)0x06CA6351, (int)0x14292967,
            (int)0x27B70A85, (int)0x2E1B2138, (int)0x4D2C6DFC, (int)0x53380D13,
            (int)0x650A7354, (int)0x766A0ABB, (int)0x81C2C92E, (int)0x92722C85,
            (int)0xA2BFE8A1, (int)0xA81A664B, (int)0xC24B8B70, (int)0xC76C51A3,
            (int)0xD192E819, (int)0xD6990624, (int)0xF40E3585, (int)0x106AA070,
            (int)0x19A4C116, (int)0x1E376C08, (int)0x2748774C, (int)0x34B0BCB5,
            (int)0x391C0CB3, (int)0x4ED8AA4A, (int)0x5B9CCA4F, (int)0x682E6FF3,
            (int)0x748F82EE, (int)0x78A5636F, (int)0x84C87814, (int)0x8CC70208,
            (int)0x90BEFFFA, (int)0xA4506CEB, (int)0xBEF9A3F7, (int)0xC67178F2
        );
    }

    $W = array();
    for($i = 0, $numChunks = sizeof($chunks); $i < $numChunks; $i++)
    {
        //    initialize the registers
        for($j = 0; $j < 8; $j++)
            ${$vars{$j}} = $hash[$j];

        //    the SHA-256 compression function
        for($j = 0; $j < 64; $j++)
        {
            if($j < 16)
            {
                $T1  = ord($chunks[$i][$j*4]) & 0xFF; $T1 <<= 8;
                $T1 |= ord($chunks[$i][$j*4+1]) & 0xFF; $T1 <<= 8;
                $T1 |= ord($chunks[$i][$j*4+2]) & 0xFF; $T1 <<= 8;
                $T1 |= ord($chunks[$i][$j*4+3]) & 0xFF;
                $W[$j] = $T1;
            }
            else
            {
                $W[$j] = SHA256_sum(((($W[$j-2] >> 17) & 0x00007FFF) | ($W[$j-2] << 15)) ^ ((($W[$j-2] >> 19) & 0x00001FFF) | ($W[$j-2] << 13)) ^ (($W[$j-2] >> 10) & 0x003FFFFF), $W[$j-7], ((($W[$j-15] >> 7) & 0x01FFFFFF) | ($W[$j-15] << 25)) ^ ((($W[$j-15] >> 18) & 0x00003FFF) | ($W[$j-15] << 14)) ^ (($W[$j-15] >> 3) & 0x1FFFFFFF), $W[$j-16]);
            }

            $T1 = SHA256_sum($h, ((($e >> 6) & 0x03FFFFFF) | ($e << 26)) ^ ((($e >> 11) & 0x001FFFFF) | ($e << 21)) ^ ((($e >> 25) & 0x0000007F) | ($e << 7)), ($e & $f) ^ (~$e & $g), $K[$j], $W[$j]);
            $T2 = SHA256_sum(((($a >> 2) & 0x3FFFFFFF) | ($a << 30)) ^ ((($a >> 13) & 0x0007FFFF) | ($a << 19)) ^ ((($a >> 22) & 0x000003FF) | ($a << 10)), ($a & $b) ^ ($a & $c) ^ ($b & $c));
            $h = $g;
            $g = $f;
            $f = $e;
            $e = SHA256_sum($d, $T1);
            $d = $c;
            $c = $b;
            $b = $a;
            $a = SHA256_sum($T1, $T2);
        }

        //    compute the next hash set
        for($j = 0; $j < 8; $j++)
            $hash[$j] = SHA256_sum(${$vars{$j}}, $hash[$j]);
    }

    // HASH HEX
    $str = '';
    reset($hash);
    do { $str .= sprintf('%08x', current($hash)); } while(next($hash));

    return $str;
}

// Auto-Archives News
function ResynchronizeAutoArchive()
{
    global $config_auto_archive, $config_notify_email,$config_notify_archive,$config_notify_status, $config_date_adjust;

    $count_news = count(file(SERVDIR."/cdata/news.txt"));
    if($count_news > 1)
    {
        if ($config_auto_archive == "yes")
        {

            $now['year'] = date("Y", time() + $config_date_adjust*60);
            $now['month'] = date("n", time() + $config_date_adjust*60);

            $db_content = file(SERVDIR."/cdata/auto_archive.db.php");
            list($last_archived['year'], $last_archived['month']) = explode("|", $db_content[0] );

            $tmp_now_sum = $now['year'] . sprintf("%02d", $now['month']) ;
            $tmp_last_sum = (int)$last_archived['year'] . sprintf("%02d", (int)$last_archived['month']) ;

            if($tmp_now_sum > $tmp_last_sum)
            {
                $error = FALSE;
                $arch_name = time() + $config_date_adjust*60;

                if (!copy(SERVDIR."/cdata/news.txt", SERVDIR."/cdata/archives/$arch_name.news.arch"))          { $error = lang("Cannot copy news.txt from cdata/ to cdata/archives"); }
                if (!copy(SERVDIR."/cdata/comments.txt", SERVDIR."/cdata/archives/$arch_name.comments.arch"))  { $error = lang("Cannot copy comments.txt from cdata/ to cdata/archives"); }

                $handle = fopen(SERVDIR."/cdata/news.txt","w") or $error = lang("Cannot open news.txt");
                fclose($handle);

                $handle = fopen(SERVDIR."/cdata/comments.txt","w") or $error = lang("Cannot open comments.txt");
                fclose($handle);

                $fp = fopen(SERVDIR."/cdata/auto_archive.db.php", "w");
                if ($fp)
                {
                    flock ($fp, LOCK_EX);

                    if  (!$error )
                         fwrite($fp, $now['year']."|".$now['month']."\n");
                    else fwrite($fp, "0|0|$error\n");

                    foreach($db_content as $line) fwrite($fp, $line);

                    flock ($fp, LOCK_UN);
                    fclose($fp);

                    if ($config_notify_archive == "yes" and $config_notify_status == "active")
                        send_mail($config_notify_email, lang("CuteNews - AutoArchive was Performed"), lang("CuteNews has performed the AutoArchive function.")."\n$count_news ".lang("News Articles were archived.")."\n$error");

                }
            }
        }
    }
}

// Refreshes the Postponed News file.
function ResynchronizePostponed()
{
    global $config_notify_postponed,$config_notify_status,$config_notify_email, $config_date_adjust;

    $all_postponed_db = file(SERVDIR."/cdata/postponed_news.txt");
    if (!empty($all_postponed_db))
    {
        $new_postponed_db = fopen(SERVDIR."/cdata/postponed_news.txt", "w");
        if ($new_postponed_db)
        {
            $now_date = time() + $config_date_adjust*60;
            flock ($new_postponed_db, LOCK_EX);

            foreach ($all_postponed_db as $p_line)
            {
                $p_item_db = explode("|", $p_line);
                if ($p_item_db[0] <= $now_date)
                {
                    // Item is old and must be Activated, add it to news.txt
                    $all_active_db      = file(SERVDIR."/cdata/news.txt");
                    $active_news_file   = fopen(SERVDIR."/cdata/news.txt", "w");

                    if ($active_news_file)
                    {
                        flock ($active_news_file, LOCK_EX);
                        fwrite($active_news_file, $p_line);
                        foreach ($all_active_db as $active_line) fwrite($active_news_file, $active_line);
                        flock ($active_news_file, LOCK_UN);
                        fclose($active_news_file);

                        if($config_notify_postponed == "yes" and $config_notify_status == "active")
                            send_mail( $config_notify_email, lang("CuteNews - Postponed article was Activated"), lang("CuteNews has activated the article").' '.$p_item_db[2]);

                    }
                }
                else
                {
                    // Item is still postponed
                    fwrite($new_postponed_db,"$p_line");
                }
            }

            flock ($new_postponed_db, LOCK_UN);
            fclose($new_postponed_db);
        }
    }
}

// Format the size of given file
function formatsize($file_size)
{
    if($file_size >= 1073741824)    $file_size = round($file_size / 1073741824 * 100) / 100 . " Gb";
    elseif($file_size >= 1048576)   $file_size = round($file_size / 1048576 * 100) / 100 . " Mb";
    elseif($file_size >= 1024)      $file_size = round($file_size / 1024 * 100) / 100 . " Kb";
    else                            $file_size = $file_size . " B";
    return $file_size;
}

// Format the Query_String for CuteNews purpuses index.php?
function cute_query_string($q_string, $strips, $type="get")
{
    foreach($strips as $key) $strips[$key] = true;

    $my_q = false;
    $var_value = explode("&", $q_string);

    foreach ($var_value as $var_peace)
    {
        $parts = explode("=", $var_peace);
        if($strips[$parts[0]] != true and $parts[0] != "")
        {
            if( $type == "post" )
                 $my_q .= "<input type=\"hidden\" name=\"".htmlspecialchars($parts[0])."\" value=\"".htmlspecialchars($parts[1])."\" />\n";
            else $my_q .= "$var_peace&amp;";
        }
    }

    if ( substr($my_q, -5) == "&amp;" ) $my_q = substr($my_q, 0, -5);
    return $my_q;
}

// Flood Protection Function
function flooder($ip, $comid)
{
    global $config_flood_time;

    $result = false;
    $old_db = file(SERVDIR."/cdata/flood.db.php");
    $new_db = fopen(SERVDIR."/cdata/flood.db.php", 'w');

    if ($new_db)
    {
        flock($new_db, LOCK_EX);
        $result = false;
        foreach ($old_db as $old_db_line)
        {
            $old_db_arr = explode("|", $old_db_line);
            if (($old_db_arr[0] + $config_flood_time) > time() )
            {
                fwrite($new_db, $old_db_line);
                if($old_db_arr[1] == $ip and $old_db_arr[2] == $comid) $result = true;
            }
        }
        flock($new_db, LOCK_UN);
        fclose($new_db);
    }
    return $result;
}

// nocache string by referer
function make_nocache()
{
    $referer = $_SERVER['HTTP_REFERER'];
    $referer = preg_replace('/[\&\?]nocache\=\d+/i', '', $_SERVER['HTTP_REFERER']);
    $cacheId = mt_rand(1000,9999).mt_rand(1000,9999);
    if (strpos($referer, '?')) $referer .= '&nocache='.$cacheId; else $referer .= '?nocache='.$cacheId;

    return htmlspecialchars($referer);
}

// Displays message to user
function msg($type, $title, $text, $back = false, $bc = false)
{
    echoheader($type, $title, $bc);

    // Back By Referef
    if ($back == '#GOBACK')
        $back = '| <a href="'.make_nocache().'">'.lang('Go back').'</a>';

    echo proc_tpl('msg', array('text' => $text, 'back' => $back));
    echofooter();
    die();
}

// Displays header skin
function echoheader($image, $header_text, $bread_crumbs = false)
{
    global $is_loged_in, $skin_header, $lang_content_type, $skin_menu, $skin_prefix, $config_version_name;

    if ($is_loged_in == true )
         $skin_header = preg_replace("/{menu}/", $skin_menu, $skin_header);
    else $skin_header = preg_replace("/{menu}/", "<div style='padding: 5px;'>$config_version_name</div>", $skin_header);

    $skin_header = get_skin($skin_header);
    $skin_header = str_replace('{title}', ($header_text? $header_text.' / ' : ''). 'CuteNews', $skin_header);
    $skin_header = str_replace("{image-name}", $skin_prefix.$image, $skin_header);
    $skin_header = str_replace("{header-text}", $header_text, $skin_header);
    $skin_header = str_replace("{content-type}", $lang_content_type, $skin_header);
    $skin_header = str_replace("{breadcrumbs}", $bread_crumbs, $skin_header);

    echo $skin_header;
}

// Displays footer skin
function echofooter()
{
    global $is_loged_in, $skin_footer, $lang_content_type, $skin_menu, $config_version_name;

    if ($is_loged_in == TRUE)
         $skin_footer = str_replace("{menu}", $skin_menu, $skin_footer);
    else $skin_footer = str_replace("{menu}", " &nbsp; ".$config_version_name, $skin_footer);

    $skin_footer = get_skin($skin_footer);
    $skin_footer = str_replace("{content-type}", $lang_content_type, $skin_footer);

    echo $skin_footer;
}

// And the duck fly away.
function b64dck()
{
    $cr = bd_config('e2NvcHlyaWdodHN9');
    $shder = bd_config('c2tpbl9oZWFkZXI=');
    $sfter = bd_config('c2tpbl9mb290ZXI=');

    global $$shder,$$sfter;
    $HDpnlty = bd_config('PGNlbnRlcj48aDE+Q3V0ZU5ld3M8L2gxPjxhIGhyZWY9Imh0dHA6Ly9jdXRlcGhwLmNvbSI+Q3V0ZVBIUC5jb208L2E+PC9jZW50ZXI+PGJyPg==');
    $FTpnlty = bd_config('PGNlbnRlcj48ZGl2IGRpc3BsYXk9aW5saW5lIHN0eWxlPVwnZm9udC1zaXplOiAxMXB4XCc+UG93ZXJlZCBieSA8YSBzdHlsZT1cJ2ZvbnQtc2l6ZTogMTFweFwnIGhyZWY9XCJodHRwOi8vY3V0ZXBocC5jb20vY3V0ZW5ld3MvXCIgdGFyZ2V0PV9ibGFuaz5DdXRlTmV3czwvYT4gqSAyMDA1ICA8YSBzdHlsZT1cJ2ZvbnQtc2l6ZTogMTFweFwnIGhyZWY9XCJodHRwOi8vY3V0ZXBocC5jb20vXCIgdGFyZ2V0PV9ibGFuaz5DdXRlUEhQPC9hPi48L2Rpdj48L2NlbnRlcj4=');
    if(!stristr($$shder,$cr) and !stristr($$sfter,$cr))
    {
        $$shder = $HDpnlty.$$shder;
        $$sfter = $$sfter.$FTpnlty;
    }
}
// Count How Many Comments Have a Specific Article
function CountComments($id, $archive = FALSE)
{
    $result = "0";
    if  ($archive and ($archive != "postponed" and $archive != "unapproved"))
         $all_comments = file(SERVDIR."/cdata/archives/${archive}.comments.arch");
    else $all_comments = file(SERVDIR."/cdata/comments.txt");

    foreach ($all_comments as $comment_line)
    {
        $comment_arr_1 = explode("|>|", $comment_line);
        if($comment_arr_1[0] == $id)
        {
            $comment_arr_2 = explode("||", $comment_arr_1[1]);
            $result = count($comment_arr_2)-1;
        }
    }
    return $result;
}

// insert smilies for adding into news/comments
function insertSmilies($insert_location, $break_location = FALSE, $admincp = FALSE, $wysiwyg = FALSE)
{
    global $config_http_script_dir, $config_smilies;

    $i          = 0;
    $output     = false;
    $smilies    = spsep($config_smilies);

    foreach($smilies as $smile)
    {
        $i++;
        $smile = trim($smile);
        if ($admincp)
        {
            if ( $wysiwyg )
                 $output .= "<a href=# onclick=\"document.getElementById('$insert_location').contentWindow.document.execCommand('InsertImage', false, '$config_http_script_dir/skins/emoticons/$smile.gif'); return false;\"><img style=\"border: none;\" alt=\"$smile\" src=\"$config_http_script_dir/skins/emoticons/$smile.gif\" /></a>";
            else $output .= "<a href=# onclick=\"javascript:document.getElementById('$insert_location').value += ' :$smile:'; return false;\"><img style=\"border: none;\" alt=\"$smile\" src=\"$config_http_script_dir/skins/emoticons/$smile.gif\" /></a>";
        }
        else
        {
            $output .= "<a href=\"javascript:insertext(':$smile:','$insert_location')\"><img style=\"border: none;\" alt=\"$smile\" src=\"$config_http_script_dir/skins/emoticons/$smile.gif\" /></a>";
        };

        if ( isset($break_location) && (int)$break_location > 0 && $i%$break_location == 0 )
             $output .= "<br />";
        else $output .= "&nbsp;";
    }

    return $output;
}

// Replaces comments charactars
function replace_comment($way, $sourse)
{
    global $HTML_SPECIAL_CHARS, $config_http_script_dir, $config_smilies, $config_utf8html;

    $sourse = stripslashes(trim($sourse));
    $find = $replace = array();

    if ($way == "add")
    {
        $find = array( "'\"'", "'\''", "'<'", "'>'", "'\|'", "'\n'", "'\r'", );
        $replace = array( "&quot;", "&#039;", "&lt;", "&gt;", "&#124;", " <br />", "", );
    }
    elseif ($way == "show")
    {
        $find = array
        (
            '~\[b\](.*?)\[/b\]~i',
            '~\[i\](.*?)\[/i\]~i',
            '~\[u\](.*?)\[/u\]~i',
            '~\[quote=(.*?)\](.*?)\[/quote\]~',
            '~\[quote\](.*?)\[/quote\]~',
        );

        $replace = array
        (
            "<strong>\\1</strong>",
            "<em>\\1</em>",
            "<span style=\"text-decoration: underline;\">\\1</span>",
            "<blockquote><div style=\"font-size: 13px;\">quote (\\1):</div><hr style=\"border: 1px solid #ACA899;\" /><div>\\2</div><hr style=\"border: 1px solid #ACA899;\" /></blockquote>",
            "<blockquote><div style=\"font-size: 13px;\">quote:</div><hr style=\"border: 1px solid #ACA899;\" /><div>\\1</div><hr style=\"border: 1px solid #ACA899;\" /></blockquote>",
        );

        $smilies_arr = spsep($config_smilies);
        foreach($smilies_arr as $smile)
        {
            $smile      = trim($smile);
            $find[]     = "':$smile:'";
            $replace[]  = "<img style=\"border: none;\" alt=\"$smile\" src=\"$config_http_script_dir/skins/emoticons/$smile.gif\" />";
        }
    }

    // Replace hook
    list($find, $replace) = hook('core:replace_comment', array($find, $replace));

    // Replace action
    $sourse  = preg_replace($find, $replace, $sourse);

    // UTF8 to HTML entity
    if ($config_utf8html == 0)
    {
        foreach ($HTML_SPECIAL_CHARS as $key => $value)
            $sourse = str_replace($key,$value,$sourse);
    }

    return $sourse;
}

// Hello skin!
function get_skin($skin)
{
    $licensed = false;
    if (!file_exists(SERVDIR.'/cdata/reg.php')) $stts = base64_decode('KHVucmVnaXN0ZXJlZCk=');
    else
    {
        include (SERVDIR.'/cdata/reg.php');
        if (isset($reg_site_key) == false) $reg_site_key = false;

        if (preg_match('/\\A(\\w{6})-\\w{6}-\\w{6}\\z/', $reg_site_key, $mmbrid))
        {
            if ( !isset($reg_display_name) or !$reg_display_name or $reg_display_name == '')
                 $stts = "<!-- (-$mmbrid[1]-) -->";
            else $stts = "<label title='(-$mmbrid[1]-)'>". base64_decode('TGljZW5zZWQgdG86IA==').$reg_display_name.'</label>';
            $licensed = true;
        }
        else $stts = '!'.base64_decode('KHVucmVnaXN0ZXJlZCk=').'!';
    }

    $msn  = bd_config('c2tpbg==');
    $cr   = bd_config('e2NvcHlyaWdodHN9');
    $lct  = bd_config('PGRpdiBzdHlsZT0iZm9udC1zaXplOiA5cHgiPlBvd2VyZWQgYnkgPGEgc3R5bGU9ImZvbnQtc2l6ZTogOXB4IiBocmVmPSJodHRwOi8vY3V0ZXBocC5jb20vY3V0ZW5ld3MvIiB0YXJnZXQ9Il9ibGFuayI+Q3V0ZU5ld3Mge2N2ZXJzaW9ufTwvYT4gJmNvcHk7IDIwMDImbmRhc2g7MjAxMyA8YSBzdHlsZT0iZm9udC1zaXplOiA5cHgiIGhyZWY9Imh0dHA6Ly9jdXRlcGhwLmNvbS8iIHRhcmdldD0iX2JsYW5rIj5DdXRlUEhQPC9hPi48YnI+e2wtc3RhdHVzfTwvZGl2Pg==');
    $lct  = preg_replace("/{l-status}/", $stts, $lct);
    $lct  = preg_replace("/{cversion}/", VERSION, $lct);

    if ($licensed == true) $lct = false;
    $$msn = preg_replace("/$cr/", $lct, $$msn);

    return $$msn;
}

// Replaces news charactars
function replace_news($way, $sourse, $use_html = true)
{
    global $HTML_SPECIAL_CHARS, $config_allow_html_in_news, $config_allow_html_in_comments, $config_http_script_dir, $config_smilies, $config_use_wysiwyg, $config_utf8html;

    $sourse = trim(stripslashes($sourse));
    $find = $replace = array();

    if ($way == "show")
    {
        $find = array
        (
            /* 1 */  '~\[upimage=([^\]]*?) ([^\]]*?)\]~i',
            /* 2 */  '~\[upimage=(.*?)\]~i',
            /* 3 */  '~\[b\](.*?)\[/b\]~i',
            /* 4 */  '~\[i\](.*?)\[/i\]~i',
            /* 5 */  '~\[u\](.*?)\[/u\]~i',
            /* 7 */  '~\[color=(.*?)\](.*?)\[/color\]~i',
            /* 8 */  '~\[size=(.*?)\](.*?)\[/size\]~i',
            /* 9 */  '~\[font=(.*?)\](.*?)\[/font\]~i',
            /* 10 */ '~\[align=(.*?)\](.*?)\[/align\]~i',
            /* 12 */ '~\[image=(.*?)\]~i',
            /* 14 */ '~\[quote=(.*?)\](.*?)\[/quote\]~i',
            /* 15 */ '~\[quote\](.*?)\[/quote\]~i',
            /* 16 */ '~\[list\]~i',
            /* 17 */ '~\[/list\]~i',
            /* 18 */ '~\[\*\]~i',
            /* 19 */ '~{nl}~',
        );

        $replace = array
        (
            /* 1 */  "<img \\2 src=\"${config_http_script_dir}/skins/images/upskins/images/\\1\" style=\"border: none;\" alt=\"\" />",
            /* 2 */  "<img src=\"${config_http_script_dir}/skins/images/upskins/images/\\1\" style=\"border: none;\" alt=\"\" />",
            /* 3 */  "<strong>\\1</strong>",
            /* 4 */  "<em>\\1</em>",
            /* 5 */  "<span style=\"text-decoration: underline;\">\\1</span>",
            /* 7 */  "<span style=\"color: \\1;\">\\2</span>",
            /* 8 */  "<span style=\"font-size: \\1pt;\">\\2</span>",
            /* 9 */  "<span style=\"font-family: \\1;\">\\2</span>",
            /* 10 */ "<div style=\"text-align: \\1;\">\\2</div>",
            /* 12 */ "<img src=\"\\1\" style=\"border: none;\" alt=\"\" />",
            /* 14 */ "<blockquote><div style=\"font-size: 13px;\">quote (\\1):</div><hr style=\"border: 1px solid #ACA899;\" /><div>\\2</div><hr style=\"border: 1px solid #ACA899;\" /></blockquote>",
            /* 15 */ "<blockquote><div style=\"font-size: 13px;\">quote:</div><hr style=\"border: 1px solid #ACA899;\" /><div>\\1</div><hr style=\"border: 1px solid #ACA899;\" /></blockquote>",
            /* 16 */ "<ul>",
            /* 17 */ "</ul>",
            /* 18 */ "<li>",
            /* 19 */ "\n",
        );

        $smilies_arr = spsep($config_smilies);
        foreach ($smilies_arr as $smile)
        {
            $smile = trim($smile);
            $find[] = "~:$smile:~";
            $replace[] = '<img style="border: none;" alt="'.$smile.'" src="'.$config_http_script_dir.'/skins/emoticons/'.$smile.'.gif" />';
        }

        // word replacement additional
        $replaces = file(SERVDIR.'/cdata/replaces.php');
        unset($replaces[0]);
        foreach ($replaces as $v)
        {
            list ($f, $t) = explode('=', $v, 2);
            $find[] = '~'.str_replace('~', '\x7E', $f).'~is';
            $replace[] = $t;
        }
    }
    elseif ($way == "add")
    {
        $find       = array("~\|~", "~\r~" );
        $replace    = array("&#124;", "" );

        // With using HTML don't convert
        if ($use_html != true)
            $sourse = str_replace( array('<','>'), array('&lt;', '&gt;'), $sourse);

        // if wysywig is ckeditor, replace to <BR> not allowed
        if  ($config_use_wysiwyg == 'no')
             $sourse = str_replace("\n", "<br />", $sourse);
        else $sourse = str_replace("\n", "{nl}", $sourse);
    }
    elseif ($way == "admin")
    {
        $find = array("'{nl}'", "'<'", "'>'");
        $replace = array("\n", "&lt;", "&gt;");

        // replace <BR> to EOL for admin
        if ($config_use_wysiwyg == 'no')
            $sourse = str_replace('<br />', "\n", $sourse);
    }

    // Replace hook
    list($way, $find, $replace) = hook('core:replace_news', array($way, $find, $replace));

    // Replace all
    $sourse  = preg_replace($find, $replace, $sourse);

    // Convert UTF-8 to HTML Entities --> admin/save/show
    if ($config_utf8html == 0)
    {
        foreach ($HTML_SPECIAL_CHARS as $key => $value)
            $sourse = str_replace($key, $value, $sourse);
    }

    // Truncate text
    $sourse = preg_replace_callback('~\[truncate=(.*?)\](.*?)\[/truncate\]~i', 'clbTruncate', $sourse);

    return $sourse;
}

function rating_bar($id, $value = '1/1', $from = 1, $to = 5)
{
    global $_CACHE, $config_http_script_dir, $config_use_rater;
    if ( $config_use_rater == 0 ) return false;

    // only 1 times
    if ( empty($_CACHE['use_script_rater']) )
         $rate = proc_tpl('rater', array('cutepath' => $config_http_script_dir));
    else $rate = false;

    // increase rater
    $_CACHE['use_script_rater']++;

    // average ratings
    list ($cr, $ur) = explode('/', $value);
    if ($ur == 0) $ur = 1;
    $value = $cr / $ur;

    for ($i = $from; $i <= $to; $i++)
        if ($value < $i) $rate .= '<a href="#" id="'.$id.'_'.$i.'" onclick="rateIt('.$id.', '.$i.');">'.RATEN_SYMBOL.'</a>';
                    else $rate .= '<a href="#" id="'.$id.'_'.$i.'" onclick="rateIt('.$id.', '.$i.');">'.RATEY_SYMBOL.'</a>';

    return $rate;
}

// Upload avatar to server
function check_avatar($editavatar)
{
    global $config_http_script_dir;
    $avatar = array('is_loaded' => true, 'path' => $editavatar);

    // avatar not uploaded?
    if ( strpos($editavatar, $config_http_script_dir) === false)
    {
        // check if avatar always exists
        $Px = SERVDIR.'/uploads/'.md5($editavatar).'.jpeg';

        if ( !file_exists($Px) )
        {
            $fp = fopen($editavatar, 'r') or ($avatar = array('is_loaded' => false, 'path' => false, 'error_msg' => 'Cannot download the '.$editavatar.' file. Please check \'allow_url_fopen\' option in php.ini file.'));

            // may load file?
            if ($avatar['is_loaded'])
            {
                ob_start();
                fpassthru($fp);
                $img = ob_get_clean();
                fclose($fp);

                // save image
                $fp = fopen($Px, 'w');
                fwrite($fp, $img);
                fclose($fp);

                // check attributes of image
                $attrs = getimagesize($Px);
                if ( !isset($attrs[0]) || !isset($attrs[1]) || !$attrs[0] || !$attrs[1])
                {
                    unlink($Px);
                    $avatar = array('is_loaded' => false, 'path' => false, 'error_msg' => 'Wrong attributes of image');
                }
                else
                {
                    chmod($Px, 0644); // set no execution
                }
            }
        }

        // replace for absolute path
        if ($avatar['is_loaded'])
            $avatar['path'] = str_replace(SERVDIR, $config_http_script_dir, $Px);
    }
    else
    {
        // check - available at server?
        $Px = str_replace($config_http_script_dir, SERVDIR, $editavatar);
        if (!file_exists($Px)) $avatar = array('is_loaded' => false, 'path' => false, 'error_msg' => 'The image does not exist on the server.');
    }

    return $avatar;
}

function get_allowed_cats($member_db)
{

    // only show allowed categories
    $allowed_cats = array();
    $cat_lines    = array();
    $orig_cat_lines = file(SERVDIR."/cdata/category.db.php");
    foreach ($orig_cat_lines as $single_line)
    {
        $ocat_arr = explode("|", $single_line);
        $cat[ $ocat_arr[CAT_ID] ] = $ocat_arr[CAT_NAME];

        // If PERM=empty, allowed from All, else only for userlevel < PERM, or member is admin
        if ($member_db[UDB_ACL] == ACL_LEVEL_ADMIN or $member_db[UDB_ACL] <= $ocat_arr[CAT_PERM] or empty($ocat_arr[CAT_PERM]))
        {
            $cat_lines[] = $single_line;
            $allowed_cats[] = $ocat_arr[CAT_ID];
        }
    }
    return array($allowed_cats, $cat_lines, $cat);
}

// Make HTML code for postponed date
function make_postponed_date($gstamp = 0)
{
    global $config_date_adjust;
    $_dateD = $_dateM = $_dateY = false;

    // Use current timestamp if no present
    if ($gstamp == 0) $gstamp = time() + $config_date_adjust*60;

    $day    = date('j', $gstamp);
    $month  = date('n', $gstamp);
    $year   = date('Y', $gstamp);

    for ($i = 1; $i < 32; $i++)
    {
        if ($day == $i) $_dateD .= "<option selected value=$i>$i</option>";
        else            $_dateD .= "<option value=$i>$i</option>";
    }

    for ($i = 1; $i < 13; $i++)
    {
        $timestamp = mktime(0, 0, 0, $i, 1, 2003);
        if ($month == $i) $_dateM .= "<option selected value=$i>". date("M", $timestamp) ."</option>";
        else              $_dateM .= "<option value=$i>". date("M", $timestamp) ."</option>";
    }

    for ($i = 2003; $i < (date('Y') + 4); $i++)
    {
        if ($year == $i) $_dateY .= "<option selected value=$i>$i</option>";
        else             $_dateY .= "<option value=$i>$i</option>";
    }

    return array($_dateD, $_dateM, $_dateY, date('H', $gstamp), date('i', $gstamp));
}

// By $source get path for news/comments
function detect_source($source)
{
    if ($source == "")
    {
        $news_file = SERVDIR."/cdata/news.txt";
        $comm_file = SERVDIR."/cdata/comments.txt";
    }
    elseif($source == "postponed")
    {
        $news_file = SERVDIR."/cdata/postponed_news.txt";
        $comm_file = SERVDIR."/cdata/comments.txt";
    }
    elseif($source == "unapproved")
    {
        $news_file = SERVDIR."/cdata/unapproved_news.txt";
        $comm_file = SERVDIR."/cdata/comments.txt";
    }
    else
    {
        $source = intval($source);
        $news_file = SERVDIR."/cdata/archives/$source.news.arch";
        $comm_file = SERVDIR."/cdata/archives/$source.comments.arch";

        // If archive not detected
        if (!file_exists($news_file))
        {
            $news_file = SERVDIR."/cdata/news.txt";
            $comm_file = SERVDIR."/cdata/comments.txt";
        }
    }

    return array($news_file, $comm_file );
}

// Force relocation
function relocation($url)
{
    header("Location: $url");
    echo '<html><head><title>Redirect...</title><meta http-equiv="refresh" content="0;url='.htmlspecialchars($url).'"></head><body>'.lang('Please wait... Redirecting to ').htmlspecialchars($url).'...<br/><br/></body></html>';
    die();
}

// Extract all options
function options_extract($data)
{
    $options = array();
    $data = explode(';', trim($data));
    if (is_array($data) && !empty($data))
        foreach ($data as $_opt)
        {
            list ($a, $b) = explode('=', $_opt, 2);
            if ($a && $b) $options[ sunpack($a) ] = sunpack($b);
        }

    return $options;
}

// Do edit option
function edit_option($data, $name, $value)
{
    $options = options_extract($data);

    // Modify option
    if ($value !== false)                    $options[ spack($name) ] = spack($value);
    elseif (isset($options[ spack($name) ])) unset($options[ spack($name) ]);

    $data = array();
    foreach ($options as $i => $v)
    {
        $i = trim($i);
        $v = trim($v);
        if ($i && $v) $data[] = "$i=$v";
    }

    return join(';', $data);
}

function getpart($name, $data = array())
{
    global $PHP_SELF;

    if (func_num_args() == 2)
    {
        if (!is_array($data)) $data = array($data);
    }
    elseif (func_num_args() > 2)
    {
        $data = array();
        for ($i = 1; $i < func_num_args() + 1; $i++)
            $data[$i-1] = func_get_arg($i);
    }

    $parts = str_replace('{$PHP_SELF}', $PHP_SELF, read_tpl('micro'));
    if ( preg_match('~^'.$name.'\|(.*)$~m', $parts, $match) )
    {
        foreach ($data as $i => $v) $match[1] = str_replace('%'.($i+1), $v, $match[1]);
        return $match[1];
    }

    return false;
}

function make_order($by, $params, $data)
{
    $params .= ','.$by;
    $ordby   = isset($_REQUEST[$by]) && $_REQUEST[$by] ? $_REQUEST[$by] : 'asc';
    $ordby   = ($ordby == 'asc') ? 'desc' : 'asc';
    $data[]  = $ordby;

    if (isset($_REQUEST[$by])) $colorize = 'style="color: red;"' ; else $colorize = false;
    return ' <a '.$colorize.' href="'.PHP_SELF.build_uri($params, $data).'">'.(($ordby == 'desc')? '&#x25B4;' : '&#x25BE;').'</a>';
}

/* === HELPERS === */
function linkedcat($catids)
{
    $cat_url        = array();
    $art_cat_arr    = spsep($catids);
    if (count($art_cat_arr) == 1)
    {
        return "<a href='".PHP_SELF."?cid=".$catids."'>".catid2name($catids)."</a>";
    }
    else
    {
        foreach($art_cat_arr as $thiscat)
            $cat_url[] = "<a href='".PHP_SELF."?cid=".$thiscat."'>".catid2name($thiscat)."</a>&nbsp;";

        return implode(", ", $cat_url);
    }
}

function bd_config($str) { return base64_decode($str); }
function spack($s)   { return str_replace(array('{','|',';','=',"\n"), array("{I}","{kv}","{eq}","{eol}"), $s); }
function sunpack($s) { return str_replace(array("{I}","{kv}","{eq}","{eol}"), array('{','|',';','=',"\n"), $s); }
function clbTruncate($match) { return word_truncate($match[2], $match[1]);  }
function word_truncate($data, $length = 75) { return preg_replace('~^(.{'.$length.',}?)\s.*$~', '\\1\\2...', $data); }
function check_email($email) { return (preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $email)); }
function substru($str, $from, $len) { return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $from .'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'. $len .'}).*#s','$1', $str); }

$preg_sanitize_af = array();
$preg_sanitize_at = array();

// Sanitize regexp [$rev=true -- revert]
function preg_sanitize($s, $rev = false)
{
    global $preg_sanitize_af, $preg_sanitize_at;

    if (empty($preg_sanitize_af) && empty($preg_sanitize_at))
    {
        $codes = '/\\#!=~.|[]+*?()-{}$^';
        for ($i = 0; $i < strlen($codes); $i++)
        {
            $preg_sanitize_af[] = $codes[$i];
            $preg_sanitize_at[] = '\\x' . dechex(ord($codes[$i]));
        }
    }

    if ($rev)
         return str_replace($preg_sanitize_at, $preg_sanitize_af, $s);
    else return str_replace($preg_sanitize_af, $preg_sanitize_at, $s);
}

// Manual replacements in URLs --------------
// $type - apply template
function RWU($type = 'readmore', $url, $html = true)
{
    global $config_use_replacement;

    // Disable to use mod_rewrite ---> it is a safe way
    if ($config_use_replacement == '0') return $url;

    // get default template
    $tpl    = $GLOBALS["conf_rw_$type"];
    $layout = $GLOBALS["conf_rw_{$type}_layout"];
    $adds   = array();

    // If url contains PHP_SELF and html [&amp;], remove it
    $url = str_replace( '&amp;', '&', $url );
    if (preg_match('~.*?\?(.*)$~', $url, $ourl)) $url = $ourl[1];

    // Make parts with replace: if param not present, out it at query string
    $parts = explode('&', $url);
    foreach ($parts as $v)
    {
        list($c, $s) = explode('=', $v, 2);
        if (empty($c)) continue;

        /* If in template is %var template, replace it */
        if (strpos($tpl, '%'.$c) !== false) $tpl = str_replace('%'.$c, $s, $tpl);

        /* If in layout variable present key, skip */
        elseif (!preg_match('~'.$c.'\='.$s.'~i', $layout)) $adds[] = "$c=$s";
    }

    // Set unused as 0
    $tpl = preg_replace('~\%\w+~', '0', $tpl);

    if (count($adds)) $tpl .= '?'.implode(($html?'&amp;':'&'), $adds);
    return $tpl;
}

function set_default_val_for_rewrite()
{
    global $conf_rw_htaccess,$conf_rw_readmore, $conf_rw_readmore_layout, $conf_rw_archread,
           $conf_rw_archread_layout, $conf_rw_archreadmore, $conf_rw_archreadmore_layout,
           $conf_rw_readcomm, $conf_rw_readcomm_layout, $conf_rw_newspage, $conf_rw_newspage_layout,
           $conf_rw_commpage, $conf_rw_commpage_layout, $conf_rw_archreadcomm, $conf_rw_archreadcomm_layout,
           $conf_rw_archcommpage, $conf_rw_archcommpage_layout;

    if (empty($conf_rw_htaccess))
    {
        $conf_rw_htaccess = SERVDIR.'/.htaccess';
    }

    if (empty($conf_rw_readmore))           $conf_rw_readmore = '/news/view/%id/%title';
    if (empty($conf_rw_readmore_layout))    $conf_rw_readmore_layout = '/example2.php?subaction=showfull';

    if (empty($conf_rw_archread))           $conf_rw_archread = '/news/archive/%archive';
    if (empty($conf_rw_archread_layout))    $conf_rw_archread_layout = '/example2.php?subaction=list-archive';

    if (empty($conf_rw_archreadmore))       $conf_rw_archreadmore = '/news/archive/%archive/%id/%title';
    if (empty($conf_rw_archreadmore_layout))$conf_rw_archreadmore_layout = '/example2.php?subaction=showfull';

    if (empty($conf_rw_readcomm))           $conf_rw_readcomm = '/news/read/%id/%title/comment';
    if (empty($conf_rw_readcomm_layout))    $conf_rw_readcomm_layout = '/example2.php?subaction=showcomments';

    if (empty($conf_rw_newspage))           $conf_rw_newspage = '/news/read/%start_from';
    if (empty($conf_rw_newspage_layout))    $conf_rw_newspage_layout = '/example2.php';

    if (empty($conf_rw_commpage))           $conf_rw_commpage = '/news/read/%id/%title/comment/%comm_start_from';
    if (empty($conf_rw_commpage_layout))    $conf_rw_commpage_layout = '/example2.php?subaction=showcomments';

    if (empty($conf_rw_archreadcomm))       $conf_rw_archreadcomm = '/news/archive/%archive/%id/%title/comment';
    if (empty($conf_rw_archreadcomm_layout))$conf_rw_archreadcomm_layout = '/example2.php?subaction=showcomments';

    if (empty($conf_rw_archcommpage))       $conf_rw_archcommpage = '/news/archive/%archive/%id/%title/comment/%comm_start_from';
    if (empty($conf_rw_archcommpage_layout))$conf_rw_archcommpage_layout = '/example2.php?subaction=showcomments';
}

// Separate string to array: imporved "explode" function
function spsep($separated_string, $seps = ',')
{
    if (empty($separated_string) ) return array();
    if (strpos($separated_string, $seps) === false) return array( $separated_string );
    $ss = explode($seps, $separated_string);
    return $ss;
}

// Simply rewrite file with locking
function rewritefile($file, $data)
{
    if (is_array($data))
        $data = implode('', $data);

    $w = fopen(SERVDIR.$file, 'w');
    flock($w, LOCK_EX);
    fwrite($w, $data);
    flock($w, LOCK_UN);
    fclose($w);

    return true;
}

// Load database in GLOBALS as string
function load_database($dbname, $target, $reload = false)
{
    global $$dbname;

    if (empty($$dbname) or $reload)
        $$dbname = join('', file(SERVDIR.'/cdata/'.$target.'.php'));

    return $$dbname;
}

function user_search($user, $field = 'name')
{
    $user = preg_sanitize( $user );
    if ( empty($user) ) return false;

    $member_db = false;
    $users_db = load_database('users_db', 'users.db');

    if ($field == 'name')
    {
        if  (preg_match('~^[0-9]*?\|[0-9]*?\|'.$user.'\|.*$~m', $users_db, $c))
             $member_db = user_decode($c[0]);
    }
    elseif ($field == 'email')
    {
        if  (preg_match('~^[0-9]*?\|[0-9]*?\|.*?\|.*?\|.*?\|'.$user.'\|.*$~m', $users_db, $c))
            $member_db = user_decode($c[0]);
    }

    return $member_db;
}

function user_update($user, $member_db)
{
    $user = preg_sanitize( $user );
    if ( empty($user) ) return false;

    // Try to save new data from user
    $users_db = load_database('users_db', 'users.db');
    if  (preg_match('~^[0-9]*?\|[0-9]*?\|'.$user.'\|.*$~m', $users_db, $c))
    {
        foreach ($member_db as $i => $v) $member_db[$i] = spack($v);
        rewritefile('/cdata/users.db.php', str_replace($c[0], implode('|', $member_db), $users_db) );
    }
}

function user_add($member_db)
{
    $a = fopen(SERVDIR.'/cdata/users.db.php', 'a+');
    foreach ($member_db as $i => $v) $member_db[$i] = spack($v);
    fwrite($a, implode('|', $member_db)."\n");
    fclose($a);
}

function user_delete($user)
{
    $user = preg_sanitize( $user );
    if ( empty($user) ) return false;

    $users_db = load_database('users_db', 'users.db');
    if (preg_match('~^[0-9]*?\|[0-9]*?\|'.$user.'\|.*?$~im', $users_db, $c))
        $users_db = str_replace($c[0]."\n", '', $users_db);

    rewritefile('/cdata/users.db.php', $users_db );
}

// If user not banned, return false
function user_getban($ip, $stat = true)
{
    $users_ban = load_database('users_ban', 'ipban.db');

    // Check for masked IP if present that
    if (preg_match('~^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$~', $ip, $ei))
         $ip = '('.$ei[1].'|\*)\.('.$ei[2].'|\*)\.('.$ei[3].'|\*)\.('.$ei[4].'|\*)';
    else $ip = preg_sanitize($ip);

    if (empty($ip)) return false;
    if (preg_match('~^'.$ip.'\|.*$~im', $users_ban, $c))
    {
        $list = explode('|', $c[0]);

        // With expire time user has unblocked
        if ($list[2] && $list[2] < time())
        {
            user_remove_ban($ip);
            return false;
        }

        // Status message
        return $stat ? 'blocked' : $list;
    }
    else return false;
}

function user_addban($ip, $expire = false)
{
    $ip = str_replace(array('|', '"'), '', $ip);
    if (empty($ip)) return false;

    $users_ban = load_database('users_ban', 'ipban.db');
    if ( $bandata = user_getban($ip, false) )
    {
        if (preg_match('~^'.preg_sanitize($bandata[0]).'\|.*$~im', $users_ban, $c))
        {
            $bandata = explode('|', $c[0]);
            $bandata[1]++;
            $bandata[2] = $expire;
            $users_ban = str_replace($c[0], implode('|', $bandata), $users_ban);
        }
    }
    else
    {
        $users_ban = load_database('users_ban', 'ipban.db', true);
        $users_ban .= "$ip|0|$expire|\n";
        $bandata = array($ip, 0, $expire);
    }

    rewritefile('/cdata/ipban.db.php', $users_ban );
    return $bandata;
}

function user_remove_ban($ip)
{
    $ip = str_replace(array('|', '"'), '', $ip);
    if (empty($ip)) return false;

    $users_ban = load_database('users_ban', 'ipban.db');
    if (preg_match_all('~^'.preg_sanitize($ip).'\|.*$~im', $users_ban, $c, PREG_SET_ORDER))
        foreach ($c as $v) $users_ban = str_replace($v[0]."\n", '', $users_ban);

    return rewritefile('/cdata/ipban.db.php', $users_ban );
}

function user_decode($user_line)
{
    $member_db = explode('|', $user_line);
    foreach ($member_db as $i => $v) $member_db[$i] = sunpack($v, true);
    return $member_db;
}

/*  ---------- Sanitize: get POST vars (default) --------
    POST [def] only POST
    GET only GET
    POSTGET -- or POST or GET
    GETPOST -- or GET or POST
    REQUEST -- from REQUEST
    COOKIES -- from COOKIES
*/

function GET($var, $method = 'POST')
{
    $result = array();
    $vars   = spsep($var);
    $method = strtoupper($method);

    foreach ( $vars as $var )
    {
        $value = false;

        if ($method == 'POST' && isset($_POST[$var])) $value = $_POST[$var];
        elseif ($method == 'GET' && isset($_GET[$var])) $value = $_GET[$var];
        elseif ($method == 'POSTGET')
        {
            if (isset($_POST[$var])) $value = $_POST[$var];
            elseif (isset($_GET[$var])) $value = $_GET[$var];
        }
        elseif ($method == 'GETPOST')
        {
            if (isset($_GET[$var])) $value = $_GET[$var];
            elseif (isset($_POST[$var])) $value = $_POST[$var];
        }
        elseif ($method == 'REQUEST' && isset($_REQUEST[$var])) $value = $_REQUEST[$var];
        elseif ($method == 'COOKIE' && isset($_COOKIE[$var])) $value = $_COOKIE[$var];

        $result[] = $value;
    }
    return $result;
}

// GET Helper for single value
// $method[0] = * ---> htmlspecialchars ON
function REQ($var, $method = 'POST')
{
    if ($method[0] == '*')
    {
        list($value) = ( GET($var, substr($method, 1)) );
        return htmlspecialchars($value);
    }
    else
    {
        list($value) = GET($var, $method);
        return $value;
    }
}

// Create blank PHP file
function make_php($phpfile)
{
    $file = SERVDIR.'/cdata/'.$phpfile;
    if (file_exists($file))
    {
        $w = fopen($file, 'w');
        fwrite($w, '<'.'?php die("Access restricted"); ?>'."\n");
        fclose($w);
    }
}

function make_crypt_salt()
{
    $cfg = array();

    $salt = $cfg['crypt_salt'] = '';
    for ($j = 0; $j < 4; $j++)
    {
        for ($i = 0; $i < 64; $i++) $salt .= md5(mt_rand().uniqid(mt_rand()));
        $cfg['crypt_salt'] .= md5($salt);
    }

    $fx = fopen(SERVDIR.'/cdata/conf.php', 'w');
    fwrite($fx, "<?php die(); ?>\n" . serialize($cfg) );
    fclose($fx);

    return TRUE;
}

// Cutenews Self-Checking
function cn_selfcheck()
{
    $errors = array();

    $check_dirs = array
    (
        'cdata',
        'uploads',
        'cdata/archives',
        'cdata/backup',
        'cdata/cache',
        'cdata/log',
        'cdata/plugins',
    );

    // Check dirs
    foreach ($check_dirs as $dir)
    {
        // Try create file in cdata
        $test_file = SERVDIR.'/'.$dir.'/.test.html';
        fclose( fopen($test_file, 'w+') );

        // File exists?
        if (file_exists($test_file))
        {
            unlink($test_file);
        }
        else
        {
            $errors[] = array('perm' => '---', 'file' => SERVDIR.'/'.$dir, 'msg' => lang('<b>Directory not writable</b>'));
        }
    }

    $check_files  = array
    (
        '/cdata/auto_archive.db.php',
        '/cdata/category.db.php',
        '/cdata/cat.num.php',
        '/cdata/comments.txt',
        '/cdata/confirmations.php',
        '/cdata/db.ban.php',
        '/cdata/users.db.php',
        '/cdata/replaces.php',
        '/cdata/flood.db.php',
        '/cdata/csrf.php',

        '/cdata/newsid.txt',
        '/cdata/news.txt',
        '/cdata/postponed_news.txt',
        '/cdata/unapproved_news.txt',

        '/cdata/rss_config.php',
        '/cdata/config.php',
        '/cdata/conf.php',

        '/cdata/Default.tpl',
        '/cdata/Headlines.tpl',
        '/cdata/rss.tpl',
    );

    foreach ($check_files as $file)
    {
        $the_file = SERVDIR . $file;

        // Check exists
        if (file_exists($the_file))
        {
            // Check readable
            if (is_readable($the_file))
            {
                // FS. BEFORE
                clearstatcache($the_file);
                $fs0 = filesize($the_file);

                $af = fopen($the_file, 'a+');
                fwrite($af, "\n");
                fclose($af);

                // FS. AFTER
                clearstatcache($the_file);
                $fs1 = filesize($the_file);

                // REVERT
                $aw = fopen($the_file, 'a+');
                ftruncate($aw, $fs0);
                fclose($aw);

                // Check writable status: no change in filesize
                if ($fs0 == $fs1)
                {
                    $errors[] = array('perm' => decoct(fileperms($the_file)), 'file' => $the_file, 'msg' => lang('File not writable'));
                }
            }
            else
            {
                $errors[] = array('perm' => decoct(fileperms($the_file)), 'file' => $the_file, 'msg' => lang('File not writable'));
            }
        }
        else
        {
            $errors[] = array('perm' => '---', 'file' => $the_file, 'msg' => lang('Not exists'));
        }
    }


    return $errors;
}

// ------------- CSRF value -------------
// CSRF - 4 possible session vars

function CSRFMake()
{
    global $config_csrf, $_SESS;

    // no check CSRF
    if ($config_csrf == 0) return TRUE;

    $csrf = md5(mt_rand() . mt_rand() . mt_rand() . mt_rand() ) ;

    // Use storage for csrf
    $csrf_storage = SERVDIR.'/cdata/csrf.php';
    $a = fopen($csrf_storage, 'a+');
    fwrite($a, time().'|'.$csrf.'|'.preg_replace('/[^a-z0-9]/i', '_', $_SESS['user'])."\n");
    fclose($a);

    send_cookie(); // Set new CSRF by session
    return $csrf;
}

function CSRFCheck()
{
    global $config_csrf, $_SESS;

    // no check CSRF
    if ($config_csrf == 0) return TRUE;

    $user = $_SESS['user'];
    $csrf_storage = SERVDIR.'/cdata/csrf.php';

    $csrf_correct = 0;
    $csrf_code    = REQ('csrf_code');

    $rcheck = file($csrf_storage);
    foreach ($rcheck as $id => $vdata)
    {
        list($time, $csrf, $user_name) = explode('|', trim($vdata));

        // Check for correct user & csrf code -> unset
        if ($user_name == $user && $csrf == $csrf_code)
        {
            unset($rcheck[$id]);
            $csrf_correct = 1;
        }

        // 5-min limit for CSRF
        if ($time < time() - 300) unset($rcheck[$id]);
    }

    rewritefile('/cdata/csrf.php', '<'.'?php die(); ?>'."\n".join('', $rcheck));

    if ($csrf_correct == 0)
    {
        add_to_log($_SESS['user'], 'CSRF Missed '.$_SERVER['HTTP_REFERER']);
        msg("error", lang('Error!'), '<div>CSRF fail <a href="'.make_nocache().'">Go back</div>');
    }
}

function check_postponed_date($added_time, $all_db)
{
    if ( preg_match("~^".intval($added_time)."\|(.*)$~m", join('', $all_db), $match ) )
    {
        $added_time++;
        $added_time = check_postponed_date($added_time, $all_db);
    }

    return $added_time;
}

function show_social_code($name = 'fb', $news_arr)
{
    // External
    global $config_http_script_dir, $soc_categories;

    // Facebook
    global $config_use_fbcomments, $config_fb_inactive, $config_fb_comments, $config_fb_box_width, $config_fbcomments_color;
    global $config_use_fblike, $config_fblike_send_btn, $config_fblike_style, $config_fblike_width, $config_fblike_show_faces, $config_fblike_font;
    global $config_fblike_color, $config_fblike_verb;

    // Twitter
    global $config_use_twitter, $config_tw_url, $config_tw_text, $config_tw_via, $config_tw_recommended, $config_tw_show_count, $config_tw_hashtag;
    global $config_tw_lang, $config_tw_large;

    // allow use fb comments
    $soc_allowed = 1;

    if (!empty($soc_categories))
    {
        $tmp_fb_cats = spsep($soc_categories);
        $tmp_nw_cats = spsep($news_arr[NEW_CAT]);
        $soc_allowed = count(array_intersect($tmp_fb_cats, $tmp_nw_cats)) ? 1 : 0;
    }

    // Show FB comments
    if ($name == 'fb' && $config_use_fbcomments == 'yes' && $config_fb_inactive == 'yes' && $soc_allowed)
    {
        return '<div class="fb-comments cutenews-fb-comments" data-href="'.$config_http_script_dir.'/router.php?subaction=showfull&amp;id='.$news_arr[NEW_ID].'" data-num-posts="'.$config_fb_comments.'" data-width="'.$config_fb_box_width.'" data-colorscheme="'.$config_fbcomments_color.'"></div>';
    }
    // Show FB like
    elseif ($name == 'fb-like' && $config_use_fblike == 'yes' && $soc_allowed)
    {
        return '<div class="fb-like cutenews-fb-comments" data-send="'.($config_fblike_send_btn=="yes"?"true":"false").'" data-layout="'.$config_fblike_style.'" data-width="'.$config_fblike_width.'" data-show-faces="'.($config_fblike_show_faces=="yes"?"true":"false").'" data-font="'.$config_fblike_font.'" data-colorscheme="'.$config_fblike_color.'" data-action="'.$config_fblike_verb.'"></div>';
    }
    elseif ($name == 'twitter' && $config_use_twitter == 'yes' && $soc_allowed)
    {
        return '<div class="cutenews-twitter-send"><a href="https://twitter.com/share" class="twitter-share-button" data-url="'.trim($config_tw_url).'" data-text="'.trim($config_tw_text).'" data-via="'.trim($config_tw_via).'" data-related="'.trim($config_tw_recommended).'" data-count="'.$config_tw_show_count.'" data-hashtags="'.trim($config_tw_hashtag).'" data-lang="'.$config_tw_lang.'" data-size="'.($config_tw_large=="yes"?"large":"medium").'"></a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';
    }
}

?>