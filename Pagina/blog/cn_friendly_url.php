<?php

    function createGetFromQuery($query)
    {
        if(!empty($query))
        {
            $params = explode('&', $query);
            foreach ($params as $param)
            {
                $option = explode('=', $param);
                $_REQUEST[urldecode($option[0])] = $_GET[urldecode($option[0])] = urldecode($option[1]);
            }
        }
    }


    if (isset($_GET['rew'])) {
        require_once("core/init.php");

        // Default values -----------------
        set_default_val_for_rewrite();

        $layout_parts = parse_url($conf_rw_readmore_layout);
        $file = ($layout_parts['path'][0] == '/') ? substr($layout_parts['path'], 1) : $layout_parts['path'];

        foreach ($GLOBALS as $idv => $rwa)
        if (is_string($rwa) && substr($idv, 0, 8) == 'conf_rw_' && !preg_match('~(layout|htaccess)~i', $idv))
        {
            $rwb = array();

            // Make arr of options
            if ( preg_match_all('~\%\w+~', $rwa, $c, PREG_SET_ORDER ))
                foreach ($c as $v)
                    $keyarr[] = substr($v[0], 1);

            // Clear leading slash
            if ($rwa[0] == '/') $rwa = substr($rwa, 1);
            $rwa = '^'.preg_replace('~\%\w+~', '([0-9a-zA-Z_]+)', $rwa);

            if(preg_match('|'.$rwa.'$|', $_GET['rew'], $match))
            {
                if(!empty($_SERVER['REQUEST_URI']))
                    createGetFromQuery(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));

                $layout_parts = parse_url($GLOBALS[ 'conf_rw_'.substr($idv, 8).'_layout' ]);
                $file = ($layout_parts['path'][0] == '/') ? substr($layout_parts['path'], 1) : $layout_parts['path'];

                createGetFromQuery($layout_parts['query']);

                $len = count($match);
                for ($i = 1; $i < $len; $i++)
                    $_REQUEST[$keyarr[$i-1]] = $_GET[$keyarr[$i-1]] = $match[$i];

                break;
            }
            unset($keyarr);
        }
        $_SERVER['PHP_SELF'] = '/'.$file;
        $PHP_SELF = '/'.$file;
        include($file);
    }
?>