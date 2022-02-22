<?php
    require '__init.php';

    if(!class_exists('BarangayLeaders')) {
        class BarangayLeaders extends __init {

            // class variables
            public static $tab_singular         = 'BRGY. LEADER';
            public static $table                = 'ph_barangays';
            public static $primary_key          = 'ID';
            public static $primary_zero_allowed = false;
            public static $list_order           = 'ORDER BY `Description`';

            public static $params = array(
                'list'   => array(
                    'key'     => 'list_barangayleaders',
                    'enabled' => true
                ),
                'select' => array(
                    'key'     => 'select_barangayleaders',
                    'enabled' => true
                ),
                'search' => array(
                    'key'     => 'search_barangayleaders',
                    'enabled' => true
                )
            );


            /****************************************************************************************************
             * BarangayLeaders :: CONSTRUCTOR
             *
             * Initialize the BarangayLeaders object.
             * @access public
             *
             * @param int $barangay_leaders_id - the BarangayLeaders id
             * @param string $purpose - the usage of this operation
             * @param bool $metadata
             */
            public function __construct($barangay_leaders_id = 0, $purpose = '', $metadata = true) {
                $this->data = array(
                    'id' => 0,
                );

                $barangay_leaders_id = intval($barangay_leaders_id);
                if(($barangay_leaders_id > 0 || self::$primary_zero_allowed) && $purpose != 'for class usage') {
                    $purpose = 'getting BarangayLeaders info' . cascade_purpose($purpose);
                    $GLOBALS['query']  = "SELECT ";
                    $GLOBALS['query'] .= " `ID` ";
                    $GLOBALS['query'] .= "FROM ";
                    $GLOBALS['query'] .= " `" . self::$table . "` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= "     `" . self::$primary_key . "`=$barangay_leaders_id";
                    $GLOBALS['query'] .= " AND `MunCityID`=" . __init::$muncity_id;
                    $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if (has_no_db_error($purpose)) {
                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);

                            require INDEX . 'php/models/PhBarangay.php';
                            require INDEX . 'php/models/PhCitizen.php';
                            require INDEX . 'php/models/LeaderType.php';
                            $barangay = new PhBarangay($row['ID'], 'for getting barangay for leaders');

                            // get barangay leaders
                            $arr_leaders = array();
                            if($metadata) {
                                $GLOBALS['query']  = "SELECT ";
                                $GLOBALS['query'] .= " `leaders`.`ID`, ";
                                $GLOBALS['query'] .= " `leaders`.`CitizenID`, ";
                                $GLOBALS['query'] .= " `leaders`.`LeaderTypeID` ";
                                $GLOBALS['query'] .= "FROM ";
                                $GLOBALS['query'] .= " `leaders`, `ph_citizens` ";
                                $GLOBALS['query'] .= "WHERE ";
                                $GLOBALS['query'] .= "     `leaders`.`CitizenID` = `ph_citizens`.`ID` ";
                                $GLOBALS['query'] .= " AND `ph_citizens`.`BarangayID`=" . $row['ID'] . " ";
                                $GLOBALS['query'] .= "ORDER BY `ph_citizens`.`LastName`, `ph_citizens`.`FirstName`, `ph_citizens`.`MiddleName`";
                                $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                if(!has_no_db_error('for getting barangay leaders'))
                                    fin();
                                while($row2 = mysqli_fetch_assoc($result2)) {
                                    $barangay_leader = (new PhCitizen($row2['CitizenID'], 'for getting citizen data of leader'))->get_data();
                                    $barangay_leader['id']   = intval($row2['ID']);
                                    $barangay_leader['type'] = (new LeaderType($row2['LeaderTypeID'], 'for getting type of leader'))->get_data();

                                    // get number of followers
                                    $GLOBALS['query'] = "SELECT `ID` FROM `followers` WHERE `LeaderID`=" . $row2['ID'];
                                    $result3 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                    if(!has_no_db_error('for getting total followers of a leader'))
                                        fin();
                                    $barangay_leader['total_followers'] = mysqli_num_rows($result3);

                                    array_push($arr_leaders, $barangay_leader);
                                }
                            }
                            $this->data = array(
                                'id'       => $barangay_leaders_id,
                                'barangay' => $barangay->get_data(),
                                'leaders'  => $arr_leaders
                            );
                        }
                        else {
                            $GLOBALS['response']['error'] = get_class($this) .' NOT FOUND ' . $purpose;
                            fin();
                        }
                    }
                    else
                        fin();
                }
            }


            /****************************************************************************************************
             * BarangayLeaders :: get_item
             *
             * Get the data to be displayed on items list
             * @access public
             *
             * @return array
             */
            public function get_item() {
                $barangay = $this->get_data('barangay');

                return $this->get_item_helper(
                    $barangay['id'],
                    '',
                    $barangay['name'],
                    $barangay['muncity']['name'] . ', ' . $barangay['muncity']['province']['name'],
                    ''
                );
            }


            /****************************************************************************************************
             * BarangayLeaders :: get_form
             *
             * Generate form for BarangayLeaders object
             * @access public static
             *
             * @param array $data     - the item data
             * @param bool  $for_logs - for system logs or not
             *
             * @return array
             */
            public static function get_form($data, $for_logs) {
                $h  = "";
                $h .= "<h4 class='no-margin'><b>" . $data['barangay']['name'] . "</b> Barangay Leader" . (sizeof($data['leaders']) > 1 ? 's' : '') . ": <big><b class='text-success'>" . number_format(sizeof($data['leaders']), 0, '.', ',') . "</b></big></h4>";
                $h .= "<div class='table-responsive' style='overflow-y: hidden'>";
                    $h .= "<table class='table table-hover table-bordered mt-3'>";
                        $h .= "<thead>";
                            $h .= "<tr>";
                                $h .= "<th></th>";
                                $h .= "<th><h6><b>Name</h6></th>";
                                $h .= "<th class='text-center'><h6><b>Birth Date</b></h6></th>";
                                $h .= "<th class='text-center'><h6><b>Contact No.</b></h6></th>";
                                $h .= "<th class='text-center'><h6><b>Followers</b></h6></th>";
                                $h .= "<th class='text-center hidden-print'>";
                                    $h .= "<a href='" . HOST_ROOT . "php/models/BarangayLeaders.php?print&id=" . $data['barangay']['id'] . "&form=\"mode\":\"masterlist\"' target='_blank' class='btn btn-warning btn-sm text-black btn-print-all'>";
                                    $h .= "<span class='fa fa-fw fa-share'></span> MASTERLIST";
                                    $h .= "</a>";
                                $h .= "</th>";
                            $h .= "</tr>";
                        $h .= "</thead>";
                        $h .= "<tbody>";
                            for($i=0; $i<sizeof($data['leaders']); $i++) {
                                $leader = $data['leaders'][$i];
                                $h .= "<tr class='vertical-middle'>";
                                    $h .= "<td align='right'>" . ($i + 1) . ".</td>";
                                    $h .= "<td>";
                                        $h .= "<a href='dashboard.php?leaders-1-" . $leader['id'] . "' target='_blank' class='text-success'><b>" . $leader['full_name_3'] . "</b></a>";
                                        $h .= "<br><small>" . $leader['type']['title'] . "</small>";
                                    $h .= "</td>";
                                    $h .= "<td align='center' class='monospace'>" . $leader['date_of_birth_1'] . "</td>";
                                    $h .= "<td align='center'>" .$leader['contact_number'] . "</td>";
                                    $h .= "<td align='center'>" .$leader['total_followers'] . "</td>";
                                    $h .= "<td align='center' class='hidden-print'>";
                                        $h .= "<button class='btn btn-link btn-sm text-warning-dark btn-print-list' data-id='" . $leader['id'] . "'><span class='fa fa-fw fa-list'></span> PRINT LIST</button>";
                                    $h .= "</td>";
                                $h .= "</tr>";
                            }
                        $h .= "</tbody>";
                    $h .= "</table>";
                $h .= "</div>";

                $arr = array(
                    // form_data[0]
                    array(
                        'class' => 'form form-group-break',
                        'rows'  => array(
                            // form_data[5]['rows'][0]
                            array(
                                array(
                                    'grid'     => 'col-md-12',
                                    'type'     => 'div',
                                    'id'       => 'lbl-header',
                                    'value'    => $h
                                )
                            )
                        )
                    ),
                );

                return $arr;
            }


            /****************************************************************************************************
             * BarangayLeaders :: print_html
             *
             * Print barangay_leaders data as HTML
             * @access public static
             *
             * @param int $barangay_id  - the Barangay id
             * @param string $form_data - the form data to base the data to be printed
             *
             * @return string (html)
             *
             */
            public static function print_html($barangay_id, $form_data) {
                require INDEX . 'php/models/PhBarangay.php';
                $barangay = (new PhBarangay($barangay_id, 'for printing leaders'))->get_data();
                if(isset($form_data['mode'])) {
                    if($form_data['mode'] == 'masterlist') {
                        echo "<!DOCTYPE html>";
                        echo "<html lang='en'>";
                        echo "<head>";
                            require INDEX . "ui/imports.global.php";
                            echo "<style>* { color: #000000 !important; }</style>";
                            echo "<title>LEADERS (" . strtoupper($barangay['name']) . ") " . date('Y-m-d H:i:s', time()) . "</title>";
                        echo "</head>";
                        echo "<body style='background: white !important'>";
                            require INDEX . 'php/models/Leader.php';

                            // get all leaders
                            $GLOBALS['query']  = "SELECT ";
                            $GLOBALS['query'] .= " `leaders`.`ID` ";
                            $GLOBALS['query'] .= "FROM ";
                            $GLOBALS['query'] .= " `leaders`, `ph_citizens` ";
                            $GLOBALS['query'] .= "WHERE ";
                            $GLOBALS['query'] .= "     `leaders`.`CitizenID` = `ph_citizens`.`ID` ";
                            $GLOBALS['query'] .= " AND `ph_citizens`.`BarangayID`=" . $barangay_id . " ";
                            $GLOBALS['query'] .= "ORDER BY `ph_citizens`.`LastName`, `ph_citizens`.`FirstName`, `ph_citizens`.`MiddleName`";
                            $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                            if(has_no_db_error('for getting barangay leaders in a barangay')) {
                                $p = 0;
                                $t = mysqli_num_rows($result);
                                while($row = mysqli_fetch_assoc($result)) {
                                    $p += 1;
                                    echo "<div style='page-break-after: always; padding-bottom: 45px;'>";
                                        echo "<div style='padding: 15px; padding-bottom: 0'>";
                                            echo "<span><big>BARANGAY: <b class='text-uppercase'>" . $barangay['name'] . "</b></big></span>";
                                            echo "<span class='float-right'><b>$p</b> of $t</span>";
                                        echo "</div>";
                                        echo Leader::print_html($row['ID'], 'masterlist');
                                    echo "</div>";
                                }
                            }
                            else
                                echo "<p class='text-danger monospace p-4'><b>ERROR:</b> " . mysqli_error($GLOBALS['con']) . "</p>";

                        echo "</body>";
                        echo "</html lang='en'>";
                    }
                }
                else {
                    echo "<!DOCTYPE html>";
                    echo "<html lang='en'>";
                    echo "<head>";
                        require INDEX . "ui/imports.global.php";
                        echo "<style>* { color: #000000 !important; } .btn-print-all { display: none; }</style>";
                    echo "</head>";
                    echo "<body style='background: white !important'>";
                        $barangay_leaders = new BarangayLeaders($barangay_id, 'for printing');
                        $form = BarangayLeaders::get_form($barangay_leaders->get_data(), false);
                        $html = $form[0]['rows'][0][0]['value'];
                        echo "<div style='padding: 15px;'>";
                            echo "<p class='no-margin'>" . date('F d, Y h:i A', time()) . "</p>";
                            echo $html;
                        echo "</div>";
                        echo "<script>";
                            echo "window.print();";
                        echo "</script>";
                    echo "</body>";
                    echo "</html lang='en'>";
                }
                return true;
            }


            /****************************************************************************************************
             * BarangayLeaders :: search
             *
             * Search through BarangayLeaders records
             * @access public static
             *
             * @param string $q - the search query
             *
             */
            public static function search($q) {
                require INDEX . 'php/models/PhBarangay.php';
                PhBarangay::search($q, 'PhMuncity', __init::$muncity_id);
            }
        }

        if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
            $object = new BarangayLeaders(0, 'for class usage');
            require '__fin.php';
        }
    }
?>