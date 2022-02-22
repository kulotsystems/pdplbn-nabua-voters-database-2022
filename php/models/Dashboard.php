<?php
    require '__init.php';

    if(!class_exists('Dashboard')) {
        class Dashboard extends __init {

            /****************************************************************************************************
             * Dashboard :: CONSTRUCTOR
             *
             * Initialize the Dashboard object.
             * @access public
             *
             * @param int $template_id - the Dashboard id
             * @param string $purpose - the usage of this operation
             * @param bool $metadata
             */
            public function __construct($template_id = 0, $purpose = '', $metadata = true) {
                $total_barangays = 0;
                $total_citizens  = 0;
                $total_leaders   = 0;

                $arr_barangays = array();
                $GLOBALS['query'] = "SELECT `ID`, `Description` FROM `ph_barangays` WHERE `MunCityID`=" . __init::$muncity_id . " ORDER BY `Description`";
                $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                if(has_no_db_error('for getting barangays for dashboard')) {
                    require INDEX . 'php/models/PhCitizen.php';

                    // DATA PER BARANGAY
                    while($row = mysqli_fetch_assoc($result)) {
                        $total_barangays += 1;
                        $barangay_id      = $row['ID'];
                        $barangay_name    = $row['Description'];

                        $GLOBALS['query'] = "SELECT `ID` FROM `ph_citizens` WHERE `BarangayID`=$barangay_id";
                        $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                        if(!has_no_db_error('for getting barangay citizens'))
                            fin();
                        $barangay_citizens = mysqli_num_rows($result2);
                        $total_citizens += $barangay_citizens;

                        $GLOBALS['query'] = "SELECT `ID` FROM `ph_citizens` WHERE `IsRegisteredVoter`=0 AND `BarangayID`=$barangay_id";
                        $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                        if(!has_no_db_error('for getting barangay citizens'))
                            fin();
                        $barangay_unregistered_citizens = mysqli_num_rows($result2);

                        $GLOBALS['query'] = "SELECT `leaders`.`ID` FROM `leaders`, `ph_citizens` WHERE `leaders`.`CitizenID`=`ph_citizens`.`ID` AND `ph_citizens`.`BarangayID`=$barangay_id";
                        $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                        if(!has_no_db_error('for getting barangay leaders'))
                            fin();
                        $barangay_leaders = mysqli_num_rows($result2);
                        $total_leaders += $barangay_leaders;

                        array_push($arr_barangays, array(
                            'id'       => intval($barangay_id),
                            'name'     => $barangay_name,
                            'citizens' => $barangay_citizens,
                            'registered_citizens'   => $barangay_citizens - $barangay_unregistered_citizens,
                            'unregistered_citizens' => $barangay_unregistered_citizens,
                            'leaders'  => $barangay_leaders,
                        ));
                    }

                    // NO BARANGAY CITIZENS
                    $no_barangay_citizens = array();
                    $no_barangay_unregistered_citizens = 0;
                    $GLOBALS['query'] = "SELECT `ID` FROM `ph_citizens` WHERE `BarangayID`=0 ORDER BY `LastName`, `FirstName`, `MiddleName`";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting citizens with no barangays'))
                        fin();
                    $no_barangay_citizens_total = mysqli_num_rows($result2);
                    $total_citizens += $no_barangay_citizens_total;
                    $records = 0;
                    while($row2 = mysqli_fetch_assoc($result2)) {
                        $records += 1;
                        $no_barangay_citizen = new PhCitizen($row2['ID'], 'for getting data of citizen with no barangay');
                        if(!$no_barangay_citizen->get_data('is_registered_voter'))
                            $no_barangay_unregistered_citizens += 1;
                        array_push($no_barangay_citizens, array(
                            'id'   => $no_barangay_citizen->get_data('id'),
                            'name' => $no_barangay_citizen->get_data('full_name_3')
                        ));
                        if($records >= 100)
                            break;
                    }

                    // NO BARANGAY LEADERS
                    $no_barangay_leaders = 0;
                    $GLOBALS['query'] = "SELECT `leaders`.`ID` FROM `leaders`, `ph_citizens` WHERE `leaders`.`CitizenID`=`ph_citizens`.`ID` AND `ph_citizens`.`BarangayID`=0";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting leaders with no barangays'))
                        fin();
                    $no_barangay_leaders = mysqli_num_rows($result2);
                    $total_leaders += $no_barangay_leaders;


                    // NO LEADER CITIZENS
                    $no_leader_citizens = array();
                    $GLOBALS['query'] = "SELECT `ID` FROM `ph_citizens` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= "(NOT EXISTS (SELECT `ID` FROM `followers` WHERE `CitizenID`=`ph_citizens`.`ID`)) ";
                    $GLOBALS['query'] .= "AND ";
                    $GLOBALS['query'] .= "(NOT EXISTS (SELECT `ID` FROM `leaders` WHERE `CitizenID`=`ph_citizens`.`ID`)) ";
                    $GLOBALS['query'] .= "ORDER BY `LastName`, `FirstName`, `MiddleName`";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting citizens with no leader'))
                        fin();
                    $no_leader_citizens_total = mysqli_num_rows($result2);
                    $records = 0;
                    while($row2 = mysqli_fetch_assoc($result2)) {
                        $records += 1;
                        $no_leader_citizen = new PhCitizen($row2['ID'], 'for getting data of citizen with no leader');
                        array_push($no_leader_citizens, array(
                            'id'   => $no_leader_citizen->get_data('id'),
                            'name' => $no_leader_citizen->get_data('full_name_3')
                        ));
                        if($records >= 100)
                            break;
                    }

                    // UNREGISTERED CITIZENS
                    $unregistered_citizens = array();
                    $GLOBALS['query'] = "SELECT `ID` FROM `ph_citizens` WHERE `IsRegisteredVoter`=0 ORDER BY `LastName`, `FirstName`, `MiddleName`";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting unregistered citizens'))
                        fin();
                    $unregistered_citizens_total = mysqli_num_rows($result2);
                    $records = 0;
                    while($row2 = mysqli_fetch_assoc($result2)) {
                        $records += 1;
                        $unregistered_citizen = new PhCitizen($row2['ID'], 'for getting data of unregistered citizen');
                        array_push($unregistered_citizens, array(
                            'id'   => $unregistered_citizen->get_data('id'),
                            'name' => $unregistered_citizen->get_data('full_name_3')
                        ));
                        if($records >= 100)
                            break;
                    }

                    // BARANGAY LEADERS
                    $total_leaders_barangay = 0;
                    $GLOBALS['query'] = "SELECT `ID` FROM `leaders` WHERE `LeaderTypeID`=1";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting leaders count from barangay'))
                        fin();
                    else
                        $total_leaders_barangay = mysqli_num_rows($result2);

                    // MUNICIPAL EMPLOYEE LEADERS
                    $total_leaders_employee = 0;
                    $GLOBALS['query'] = "SELECT `ID` FROM `leaders` WHERE `LeaderTypeID`=2";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting leaders count from municipal employees'))
                        fin();
                    else
                        $total_leaders_employee = mysqli_num_rows($result2);

                    // BOTH BARANGAY AND MUNICIPAL EMPLOYEE LEADERS
                    $total_leaders_both = 0;
                    $GLOBALS['query'] = "SELECT `ID` FROM `leaders` WHERE `LeaderTypeID`=3";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting leaders count from barangay or municipal employees'))
                        fin();
                    else
                        $total_leaders_both = mysqli_num_rows($result2);

                    // DEFAULT LEADERS
                    $default_leaders   = array();
                    $GLOBALS['query']  =  "SELECT ";
                    $GLOBALS['query'] .= "  `leaders`.`ID`, `leaders`.`CitizenID` ";
                    $GLOBALS['query'] .= "FROM ";
                    $GLOBALS['query'] .= "  `leaders`, `ph_citizens` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= "      `leaders`.`IsDefault`=1 ";
                    $GLOBALS['query'] .= "  AND `leaders`.`CitizenID`=`ph_citizens`.`ID` ";
                    $GLOBALS['query'] .= "ORDER BY ";
                    $GLOBALS['query'] .= "  `ph_citizens`.`LastName`, `ph_citizens`.`FirstName`, `ph_citizens`.`MiddleName`";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting default leaders'))
                        fin();
                    $default_leaders_total = mysqli_num_rows($result2);
                    $records = 0;
                    while($row2 = mysqli_fetch_assoc($result2)) {
                        $records += 1;
                        $default_leader_citizen = new PhCitizen($row2['CitizenID'], 'for getting data of default leader');
                        array_push($default_leaders, array(
                            'id'   => intval($row2['ID']),
                            'name' => $default_leader_citizen->get_data('full_name_3')
                        ));
                        if($records >= 100)
                            break;
                    }

                    // SUMMARY
                    $GLOBALS['response']['success']['data'] = array(
                        'total_barangays'                   => $total_barangays,
                        'total_citizens'                    => $total_citizens,
                        'total_leaders'                     => $total_leaders,
                        'total_leaders_from_barangay'       => $total_leaders_barangay,
                        'total_leaders_from_employees'      => $total_leaders_employee,
                        'total_leaders_from_both'           => $total_leaders_both,
                        'barangays'                         => $arr_barangays,
                        'no_barangay_citizens'              => $no_barangay_citizens,
                        'no_barangay_citizens_total'        => $no_barangay_citizens_total,
                        'no_barangay_leaders'               => $no_barangay_leaders,
                        'no_barangay_unregistered_citizens' => $no_barangay_unregistered_citizens,
                        'no_leader_citizens'                => $no_leader_citizens,
                        'no_leader_citizens_total'          => $no_leader_citizens_total,
                        'unregistered_citizens'             => $unregistered_citizens,
                        'unregistered_citizens_total'       => $unregistered_citizens_total,
                        'default_leaders'                   => $default_leaders,
                        'default_leaders_total'             => $default_leaders_total,
                    );
                }
            }
        }

        if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
            $object = new Dashboard();
            fin();
        }
    }
?>