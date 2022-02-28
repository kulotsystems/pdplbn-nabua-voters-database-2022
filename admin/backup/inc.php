<?php

    if(!defined('INDEX')) {
        echo '<b>INDEX</b> is undefined!';
        exit();
    }

    $page = '';
    function fin() {
        echo '<div style="font-family: sans-serif">';
        echo '<h1 style="color: red; margin-bottom: 0">ACCESS DENIED!</h1>';
        echo '<p style=" margin-top: 0"><big>You should be logged in to the system to perform this operation.</big></p>';
        echo '</div>';
        exit();
    }
    require INDEX . "php/__init.php";
    require INDEX . "php/admin.user_verifier.php";

    /****************************************************************************************************
     * EXPORT MySQL Database
     * https://stackoverflow.com/questions/22195493/export-mysql-database-using-php
     *
     * @param $host
     * @param $user
     * @param $pass
     * @param $name
     * @param $tables
     * @param $except
     * @param $crypt
     * @param $is_encrypting
     */
    function export_database($host, $user, $pass, $name, $tables, $except, $crypt, $is_encrypting = true) {
        $mysqli = new mysqli($host, $user, $pass, $name);
        $mysqli->select_db($name);
        $mysqli->query("SET NAMES 'utf8'");

        $target_tables = $tables;
        foreach ($target_tables as $table) {
            $query = 'SELECT * FROM ' . $table;
            if(in_array($table, $except))
                $query .= " WHERE 0";
            else if($table == 'ph_citizens')
                $query .= " WHERE `ID` NOT IN(12662, 15502, 15505, 18263)";
            else if($table == 'followers')
                $query .= " WHERE `ID` NOT IN(11729, 14422, 14426, 16971)";

            $result = $mysqli->query($query);
            $fields_amount = $result->field_count;
            $fields = $result->fetch_fields();
            $rows_num = $mysqli->affected_rows;
            $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
            $table_mline = $res->fetch_row();

            $content = (!isset($content) ? '' : $content) . "\n\n" . $table_mline[1] . ";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
                while ($row = $result->fetch_row()) { //when started (and every after 100 command cycle):
                    if ($st_counter % 100 == 0 || $st_counter == 0) {
                        $content .= "\nINSERT INTO " . $table . " VALUES";
                    }
                    $content .= "\n(";
                    for ($j = 0; $j < $fields_amount; $j++) {
                        $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                        if (isset($row[$j])) {
                            $cell_value = $row[$j];
                            if(isset($crypt[$table])) {
                                if(in_array($fields[$j]->name, $crypt[$table])) {
                                    if($is_encrypting)
                                        $cell_value = encrypt($cell_value);
                                    else
                                        $cell_value = decrypt($cell_value);
                                }
                            }
                            $content .= '"' . $cell_value . '"';
                        } else {
                            $content .= '""';
                        }
                        if ($j < ($fields_amount - 1)) {
                            $content .= ',';
                        }
                    }
                    $content .= ")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
                        $content .= ";";
                    } else {
                        $content .= ",";
                    }
                    $st_counter = $st_counter + 1;
                }
            }
            $content .= "\n\n\n";
        }
        $backup_name = $name . "[" . strtolower($GLOBALS['arr_admin_details']['last_name']) . "]" . "-" . date('Y-m-d-H-i-s', time()) . ".sql";
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
        echo $content;
        exit();
    }

    /****************************************************************************************************
     * CONFIGURATION
     *
     */
    require INDEX . 'php/db/open.php';
    require INDEX . 'php/db/close.php';
    $tables = [
        'ph_regions'  ,
        'ph_provinces',
        'ph_muncities',
        'ph_barangays',
        'ph_citizens' ,
        'user_types'  ,
        'users'       ,
        'leader_types',
        'leaders'     ,
        'followers',
        'reports',
        'activity_logs'
    ];
    $except = [
        'activity_logs'
    ];
    $crypt = [
        'ph_citizens' => [
            'FirstName',
            'MiddleName',
            'LastName'
        ]
    ];
?>