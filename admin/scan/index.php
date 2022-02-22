<?php

    define('INDEX'  , '../../');
    $page = '';
    require INDEX . "php/__init.php";
    require INDEX . "php/admin.user_verifier.php";
    require INDEX . "php/db/open.php";

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
        require INDEX . 'ui/imports.global.php';
        echo '<title>DATA SCAN</title>';
        echo '<style>* { color: #000000 !important; }</style>';
        echo '<script src="../../ui/pages/assets/plugins/jquery/jquery-1.11.1.min.js"></script>';
    echo '</head>';
    echo '<body style="background: white !important">';
        echo '<div class="container mt-2">';
            echo "<h1 align='center'><b class='text-success'><span class='fa fa-fw fa-spinner fa-pulse text-success'></span> DATA SCAN</b></h1>";

            function fin() {
                echo '<div style="font-family: sans-serif">';
                echo '<h1 style="color: red; margin-bottom: 0">ACCESS DENIED!</h1>';
                echo '<p style=" margin-top: 0"><big>You should be logged in to the system to perform this operation.</big></p>';
                echo '</div>';
                exit();
            }


            /****************************************************************************************************
             * Check possible redundant citizen prior to Sep. 30 update
             * (Added vowels check Oct. 25)
             *
             */
            echo "<div style='font-family: monospace'>";
                $query  = "SELECT `ph_citizens`.`ID`, `ph_citizens`.`LastName`, `ph_citizens`.`FirstName`, `ph_citizens`.`MiddleName`, `ph_citizens`.`NameSuffix`, `ph_citizens`.`DateOfBirth`, `ph_citizens`.`ContactNumber`, `ph_barangays`.`Description` AS Barangay  ";
                $query .= "FROM `ph_citizens`, `ph_barangays` ";
                $query .= "WHERE `ph_citizens`.`BarangayID`=`ph_barangays`.`ID` ";
                $query .= "ORDER BY `ph_citizens`.`ID` ";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con))
                    echo '<p style="color: red;">' . mysqli_error($con) . '</p>';
                else {
                    $checked = [];
                    echo '<table class="table table-borderless table-hover">';
                        echo '<thead>';
                            echo '<tr>';
                                echo '<th colspan="5"><h4><b>POSSIBLE REDUNDANT CITIZENS</b></h4></th>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<th style="width: 15px;"></th>';
                                echo '<th>FULL NAME</th>';
                                echo '<th class="text-center">BIRTH DATE</th>';
                                echo '<th class="text-center">CONTACT NUMBER</th>';
                                echo '<th class="text-center">BARANGAY</th>';
                            echo '</tr>';
                        echo '</thead>';
                        echo '<tbody class="text-uppercase">';
                            $proceed = true;
                            $ctr = 0;
                            while($row = mysqli_fetch_assoc($result)) {
                                $citizen_id  = intval($row['ID']);
                                $lastname    = $row['LastName'];
                                $firstname   = $row['FirstName'];
                                $middle_name = $row['MiddleName'];
                                $suffix      = $row['NameSuffix'];
                                $birth_date  = $row['DateOfBirth'];
                                $contact_num = $row['ContactNumber'];
                                $barangay    = $row['Barangay'];

                                if(!in_array($citizen_id, $checked)) {
                                    // check similar name
                                    $query  = "SELECT ";
                                    $query .= " `ph_citizens`.`ID`, ";
                                    $query .= " `LastName`, ";
                                    $query .= " `FirstName`, ";
                                    $query .= " `MiddleName`, ";
                                    $query .= " `NameSuffix`, ";
                                    $query .= " `DateOfBirth`, ";
                                    $query .= " `ContactNumber`, ";
                                    $query .= " `Description` AS Barangay ";
                                    $query .= "FROM ";
                                    $query .= " `ph_citizens`, `ph_barangays` ";
                                    $query .= "WHERE ";
                                    $query .= "     `ph_citizens`.`ID` > $citizen_id ";
                                    $query .= " AND `ph_citizens`.`BarangayID`=`ph_barangays`.`ID` ";
                                    $query .= " AND ";
                                    $query .= " ( ";
                                    $query .= "   ( ";
                                    $query .= "         `FirstName`='$firstname' ";
                                    $query .= "     AND `LastName`='$lastname' ";
                                    $query .= "   ) ";
                                    $query .= " OR ";
                                    $query .= "   ( ";
                                    $query .= "         REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(`FirstName`)), 'A', ''), 'E', ''), 'I', ''), 'O', ''), 'U', '') = '" . str_replace('U', '', str_replace('O', '', str_replace('I', '', str_replace('E', '', str_replace('A', '', strtoupper(trim($firstname))))))) . "'";
                                    $query .= "     AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(`LastName`)), 'A', ''), 'E', ''), 'I', ''), 'O', ''), 'U', '') = '" . str_replace('U', '', str_replace('O', '', str_replace('I', '', str_replace('E', '', str_replace('A', '', strtoupper(trim($lastname))))))) . "'";
                                    $query .= "   ) ";
                                    $query .= " ) ";
                                    $query .= " AND TRIM(REPLACE(`NameSuffix`, '.', '')) = TRIM(REPLACE('$suffix', '.', '')) ";
                                    $query .= " AND ( ";
                                    $query .= "   ('$middle_name'  = '' AND `MiddleName` = '') ";
                                    $query .= "   OR ";
                                    $query .= "   ('$middle_name'  = '' AND `MiddleName` != '') ";
                                    $query .= "   OR ";
                                    $query .= "   ('$middle_name' != '' AND `MiddleName` = '') ";
                                    $query .= "   OR ";
                                    $query .= "   ('$middle_name' != '' AND `MiddleName` = '$middle_name') ";
                                    $query .= "   OR ";
                                    $query .= "   ( CHAR_LENGTH(TRIM(REPLACE('$middle_name', '.', ''))) = 1 AND MID(`MiddleName`, 1, 1) = '" . substr($middle_name, 0, 1) . "') ";
                                    $query .= "   OR ";
                                    $query .= "   ( CHAR_LENGTH(TRIM(REPLACE(`MiddleName`, '.', ''))) = 1 AND MID('$middle_name', 1, 1) = MID(`MiddleName`, 1, 1)) ";
                                    $query .= " ) ";
                                    $query .= " AND ( ";
                                    $query .= "   ('$birth_date' = '0000-00-00' AND `DateOfBirth` = '0000-00-00') ";
                                    $query .= "   OR ";
                                    $query .= "   ('$birth_date' = '0000-00-00' AND `DateOfBirth` != '0000-00-00') ";
                                    $query .= "   OR ";
                                    $query .= "   ('$birth_date' != '0000-00-00' AND `DateOfBirth` = '0000-00-00') ";
                                    $query .= "   OR ";
                                    $query .= "   ('$birth_date' != '0000-00-00' AND `DateOfBirth` = '$birth_date') ";
                                    $query .= " ) ";
                                    $result2 = mysqli_query($con, $query);
                                    if(mysqli_error($con)) {
                                        echo "<p style='color: red;'>" . mysqli_error($con) . "</p>";
                                        $proceed = false;
                                        break;
                                    }
                                    else {
                                        if(mysqli_num_rows($result2) > 0) {
                                            $ctr += 1;
                                            echo '<tr>';
                                                echo '<td align="right">' . $ctr . '.</td>';
                                                echo '<td><a href="../dashboard.php?citizens-1-' . $citizen_id . '" target="_blank" class="text-success text-bold">' . $lastname . ', ' . $firstname . ' ' . $middle_name . ' ' . $suffix . '</a></td>';
                                                echo '<td class="text-center">' . (date('M. d, Y', strtotime($birth_date))) . '</td>';
                                                echo '<td class="text-center">' . $contact_num . '</td>';
                                                echo '<td class="text-center">' . $barangay . '</td>';
                                            echo '</tr>';
                                            while($row2 = mysqli_fetch_assoc($result2)) {
                                                array_push($checked, intval($row2['ID']));
                                                echo '<tr>';
                                                    echo '<td></td>';
                                                    echo '<td><a href="../dashboard.php?citizens-1-' . $row2['ID'] . '" target="_blank" class="text-success text-bold">' . $row2['LastName'] . ', ' . $row2['FirstName'] . ' ' . $row2['MiddleName'] . ' ' . $row2['NameSuffix'] . '</a></td>';
                                                    echo '<td class="text-center">' . (date('M. d, Y', strtotime($row2['DateOfBirth'])))   . '</td>';
                                                    echo '<td class="text-center">' . $row2['ContactNumber'] . '</td>';
                                                    echo '<td class="text-center">' .  $row2['Barangay']     . '</td>';
                                                echo '</tr>';

                                                echo '<tr style="height: 1px; !important;"><td colspan="5"></td></tr>';
                                            }
                                        }
                                    }
                                }

                                if(!$proceed)
                                    break;
                            }
                            if($ctr <= 0) {
                                echo '<tr>';
                                echo '<td colspan="5" class="text-success">(NONE)</td>';
                                echo '</tr>';
                            }

                        echo '</tbody>';
                    echo '</table>';
                }
            echo '</div>';


            /****************************************************************************************************
             * Check citizen with multiple contact numbers
             *
             */
            echo '<div style="font-family: monospace">';
                $query  = "SELECT `ID`, `LastName`, `FirstName`, `MiddleName`, `NameSuffix`, `ContactNumber` ";
                $query .= "FROM `ph_citizens` ";
                $query .= "WHERE `ContactNumber` LIKE '%/%' OR CHAR_LENGTH(`ContactNumber`) >= 14";
                $result = mysqli_query($con, $query);
                if(mysqli_error($con))
                    echo '<p style="color: red;">' . mysqli_error($con) . '</p>';
                else {
                    echo '<table class="table table-borderless table-hover">';
                        echo '<thead>';
                            echo '<tr>';
                                echo '<th colspan="3"><h4><b>CITIZENS WITH UNUSUAL CONTACT NUMBER</b></h4></th>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<th style="width: 15px;"></th>';
                                echo '<th>FULL NAME</th>';
                                echo '<th>CONTACT NUMBER</th>';
                            echo '</tr>';
                        echo '</thead>';
                        echo '<tbody class="text-uppercase">';
                            if(mysqli_num_rows($result) <= 0) {
                                echo '<tr>';
                                echo '<td colspan="3" class="text-success">(NONE)</td>';
                                echo '</tr>';
                            }
                            else {
                                $ctr = 0;
                                while($row = mysqli_fetch_assoc($result)) {
                                    $ctr += 1;
                                    echo '<tr>';
                                        echo '<td align="right">' . $ctr . '.</td>';
                                        echo '<td><a href="../dashboard.php?citizens-1-' . $row['ID'] . '" target="_blank" class="text-success text-bold">' . $row['LastName'] . ', ' . $row['FirstName'] . ' ' . $row['MiddleName'] . ' ' . $row['NameSuffix'] . '</a></td>';
                                        echo '<td>' . $row['ContactNumber'] . '</td>';
                                    echo '</tr>';
                                }
                            }
                        echo '<tbody>';
                    echo '</table>';
                }
            echo '</div>';
        echo '</div>';
        echo '<script>';
        echo 'var spinner = $(".fa.fa-spinner"); ';
        echo 'spinner.removeClass("fa-spinner");';
        echo 'spinner.removeClass("fa-pulse");';
        echo 'spinner.addClass("fa-search");';
        echo '</script>';
    echo '</body>';
    echo '</html>';
    require INDEX . "php/db/close.php";
?>