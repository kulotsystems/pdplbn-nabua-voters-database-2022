<?php

    $arr_admin_details = array();
    if(isset($_SESSION[SESSION_ADMIN_USERNAME]) && isset($_SESSION[SESSION_ADMIN_PASSWORD])) {
        $username = $_SESSION[SESSION_ADMIN_USERNAME];
        $password = $_SESSION[SESSION_ADMIN_PASSWORD];

        $query  = "SELECT `users`.`CitizenID`, `users`.`UserTypeID`, `user_types`.`Acronym`, `user_types`.`Title`, `user_types`.`Access` ";
        $query .= "FROM `users`, `user_types` ";
        $query .= "WHERE `users`.`Username`='$username' AND ";
        $query .= "`users`.`Password`='$password' AND ";
        $query .= "`users`.`IsActive`=1 AND ";
        $query .= "`users`.`UserTypeID`=`user_types`.`ID`";

        $result = mysqli_query($con, $query);
        if(mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            // get citizen information
            $query  = "SELECT `LastName`, `FirstName`, `MiddleName`, `Avatar` ";
            $query .= "FROM `ph_citizens` WHERE `ID`=" . $row['CitizenID'];
            $result2 = mysqli_query($con, $query);
            if(mysqli_error($con))
                echo mysqli_error($con);

            if(mysqli_num_rows($result2) > 0) {
                $row2 = mysqli_fetch_assoc($result2);
                $arr_admin_details = array(
                    'citizen_id'        => intval($row['CitizenID']),
                    'last_name'         => utf8_encode(ucwords(strtolower(trim(addslashes( str_replace("?", "�", utf8_decode($row2['LastName']))))))),
                    'first_name'        => utf8_encode(ucwords(strtolower(trim(addslashes( str_replace("?", "�", utf8_decode($row2['FirstName']))))))),
                    'middle_name'       => utf8_encode(ucwords(strtolower(trim(addslashes( str_replace("?", "�", utf8_decode($row2['MiddleName']))))))),
                    'avatar'            => $row2['Avatar'],
                    'user_type_id'      => utf8_encode($row['UserTypeID']),
                    'user_type_acronym' => utf8_encode($row['Acronym']),
                    'user_type_title'   => utf8_encode($row['Title']),
                    'user_type_access'  => json_decode($row['Access'])
                );
            }
        }
    }

    if(sizeof($arr_admin_details) > 0) {
        if($page == 'index') {
            echo "<script>window.open('dashboard.php?home', '_self');</script>";
            header("Location: dashboard.php?home");
            exit();
        }
    }
    else {
        unset($_SESSION[SESSION_ADMIN_USERNAME]);
        unset($_SESSION[SESSION_ADMIN_PASSWORD]);

        if($page != '') {
            // include mode: inline
            if($page != "index") {
                if($page == 'permit' || $page == 'invoice') {
                    echo 'ACCESS DENIED!';
                    exit();
                }
                else {
                    header("Location: index.php");
                    echo "<script>window.open('index.php', '_self');</script>";
                    exit();
                }
            }
        }
        else {
            // include mode: ajax
            $response['error'] = 'SESSION HAS EXPIRED';
            fin();
        }
    }

?>