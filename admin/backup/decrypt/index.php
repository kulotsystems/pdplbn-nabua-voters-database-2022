<?php

    /****************************************************************************************************
     * PERFORM DATABASE DECRYPTION AND RETRIEVAL
     * Imported database to look for: 'database_backup'
     *
     */
    define('INDEX'  , '../../../');
    define('DB_NAME', 'database_backup');
    require INDEX . 'admin/backup/inc.php';


    /****************************************************************************************************
     * DECRYPTION FUNCTION
     *
     */
    function decrypt($data) {
        $private_key = file_get_contents('private.pem');
        if (openssl_private_decrypt(base64_decode($data), $decrypted, $private_key))
            $data = $decrypted;
        else
            $data = '';

        return $data;
    }

    export_database($db_host, $db_user, $db_pass, DB_NAME, $tables, $except, $crypt, false);

?>