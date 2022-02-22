<?php

    define('DOMAINS'       , []);
    define('ROOT'          , 'database');
    define('HOST_DIR'      , 'database');
    define('HOST_IS_ONLINE', in_array($_SERVER['SERVER_NAME'], DOMAINS));
    define('HOST_PROTOCOL' , (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 || HOST_IS_ONLINE) ? "https://" : "http://");
    define('HOST_DOMAIN'   , HOST_PROTOCOL . ( !HOST_IS_ONLINE ? $_SERVER['SERVER_NAME'].'/'.ROOT.'/' : $_SERVER['SERVER_NAME'].'/'));
    define('HOST_ROOT'     , HOST_DOMAIN);

    define('APP_NAME'   , 'The');
    define('APP_NAME_2' , 'Database');
    define('APP_TITLE_1', 'Local Government Unit');
    define('APP_TITLE_2', 'Nabua, Camarines Sur');

    define('SYSTEM_JS_ROOT'    , HOST_DOMAIN . 'js/system/');
    define('CITIZEN_AVATAR_DIR', INDEX . 'img/citizens/');
    define('CITIZEN_AVATAR_URL', HOST_DOMAIN . 'img/citizens/');

    date_default_timezone_set('Asia/Manila');
    define('CURRENT_TIME', time());

?>