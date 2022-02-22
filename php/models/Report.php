<?php
    require '__init.php';

    if(!class_exists('Report')) {
        class Report extends __init {

            // class variables
            public static $tab_singular         = 'REPORT';
            public static $table                = 'reports';
            public static $primary_key          = 'ID';
            public static $primary_zero_allowed = false;
            public static $list_order           = 'ORDER BY `ID`';

            public static $params = array(
                'list'   => array(
                    'key'     => 'list_report',
                    'enabled' => true
                ),
                'select' => array(
                    'key'     => 'select_report',
                    'enabled' => true
                ),
                'search' => array(
                    'key'     => 'search_report',
                    'enabled' => true
                )
            );


            /****************************************************************************************************
             * Report :: CONSTRUCTOR
             *
             * Initialize the Report object.
             * @access public
             *
             * @param int    $report_id   - the Report id
             * @param string $purpose     - the usage of this operation
             *
             */
            public function __construct($report_id = 0, $purpose = '') {
                $this->data = array(
                    'id' => 0,
                );

                $report_id = intval($report_id);
                if(($report_id > 0 || self::$primary_zero_allowed) && $purpose != 'for class usage') {
                    $purpose = 'getting Report info' . cascade_purpose($purpose);
                    $GLOBALS['query']  = "SELECT ";
                    $GLOBALS['query'] .= " `Title`, ";
                    $GLOBALS['query'] .= " `Description`, ";
                    $GLOBALS['query'] .= "  DATE_FORMAT(`CreatedAt`, '%M %e, %Y &middot; %h:%i %p') AS CreatedAt, ";
                    $GLOBALS['query'] .= "  DATE_FORMAT(`UpdatedAt`, '%M %e, %Y &middot; %h:%i %p') AS UpdatedAt ";
                    $GLOBALS['query'] .= "FROM ";
                    $GLOBALS['query'] .= " `" . self::$table . "` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= " `" . self::$primary_key . "`=$report_id";
                    $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if (has_no_db_error($purpose)) {
                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);

                            $text_class = 'default';
                            if($report_id == 1)
                                $text_class = 'primary';
                            if($report_id == 2)
                                $text_class = 'master';
                            if($report_id == 3)
                                $text_class = 'warning-dark';
                            if($report_id == 4)
                                $text_class = 'danger';

                            $this->data = array(
                                'id'           => $report_id,
                                'title'        => $row['Title'],
                                'text_class'   => $text_class,
                                'description'  => $row['Description'],
                                'date_created' => $row['CreatedAt'],
                                'date_updated' => $row['UpdatedAt']
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
             * Report :: get_item
             *
             * Get the data to be displayed on items list
             * @access public
             *
             * @return array
             */
            public function get_item() {
                return $this->get_item_helper(
                    $this->get_data('id'),
                    '',
                    "<span class='text-" . $this->get_data('text_class') . "'>" . $this->get_data('title') . "</span>",
                    "<span class='text-" . $this->get_data('text_class') . "'>" . $this->get_data('description') . "</span>",
                    ''
                );
            }


            /****************************************************************************************************
             * Report :: get_form
             *
             * Generate form for Report object
             * @access public static
             *
             * @param array $data     - the item data
             * @param bool  $for_logs - for system logs or not
             *
             * @return array
             */
            public static function get_form($data, $for_logs) {
                require INDEX . 'php/models/PhCitizen.php';
                $h = "";

                // NO BARANGAY
                if($data['id'] == 1) {
                    $GLOBALS['query'] = "SELECT `ID` FROM `ph_citizens` WHERE `BarangayID`=0 ORDER BY `LastName`, `FirstName`, `MiddleName`";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting citizens with no barangays'))
                        fin();
                    $total = mysqli_num_rows($result2);
                    $h .= "<h4 class='no-margin'>NO BARANGAY CITIZEN" . ($total > 1 ? 'S' : '') . ": <big><b class='text-" . $data['text_class'] . "'>" . number_format($total, 0, '.', ',') . "</b></big></h4>";

                    if($total > 0) {
                        $h .= "<div class='table-responsive' style='overflow-y: hidden'>";
                            $h .= "<table class='table table-hover table-bordered'>";
                                $h .= "<thead>";
                                    $h .= "<tr>";
                                        $h .= "<th></th>";
                                        $h .= "<th><h6 class='text-" . $data['text_class'] . "'><b>Name</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Birth Date</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Contact No.</b></h6></th>";
                                    $h .= "</tr>";
                                $h .= "</thead>";
                                $h .= "<tbody>";
                                    $ctr = 0;
                                    while($row2 = mysqli_fetch_assoc($result2)) {
                                        $ctr += 1;
                                        $citizen = (new PhCitizen($row2['ID'], 'for getting data of citizen with no barangay', false))->get_data();
                                        $h .= "<tr>";
                                            $h .= "<td align='right' class='text-" . $data['text_class'] . "'>$ctr.</td>";
                                            $h .= "<td>";
                                                $h .= "<a href='dashboard.php?citizens-1-" . $citizen['id'] . "' target='_blank' class='text-" . $data['text_class'] . "'><b>" . $citizen['full_name_3'] . "</b></a>";
                                            $h .= "</td>";
                                        $h .= "<td align='center' class='monospace text-" . $data['text_class'] . "'>" . $citizen['date_of_birth_1'] . "</td>";
                                        $h .= "<td align='center' class='text-" . $data['text_class'] . "'>" . $citizen['contact_number'] . "</td>";
                                        $h .= "</tr>";
                                    }
                                $h .= "<tbody>";
                            $h .= "</table>";
                        $h .= "</div>";
                    }
                }
				
				// NO LEADER
                else if($data['id'] == 2) {
                    $GLOBALS['query'] = "SELECT `ID` FROM `ph_citizens` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= "(NOT EXISTS (SELECT `ID` FROM `followers` WHERE `CitizenID`=`ph_citizens`.`ID`)) ";
                    $GLOBALS['query'] .= "AND ";
                    $GLOBALS['query'] .= "(NOT EXISTS (SELECT `ID` FROM `leaders` WHERE `CitizenID`=`ph_citizens`.`ID`)) ";
                    $GLOBALS['query'] .= "ORDER BY `LastName`, `FirstName`, `MiddleName`";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting citizens with no leader'))
                        fin();
                    $total = mysqli_num_rows($result2);
                    $h .= "<h4 class='no-margin'>NO LEADER CITIZEN" . ($total > 1 ? 'S' : '') . ": <big><b class='text-" . $data['text_class'] . "'>" . number_format($total, 0, '.', ',') . "</b></big></h4>";

                    if($total > 0) {
                        $h .= "<div class='table-responsive' style='overflow-y: hidden'>";
                            $h .= "<table class='table table-hover table-bordered'>";
                                $h .= "<thead>";
                                    $h .= "<tr>";
                                        $h .= "<th></th>";
                                        $h .= "<th><h6 class='text-" . $data['text_class'] . "'><b>Name</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Birth Date</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Contact No.</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Barangay</b></h6></th>";
                                    $h .= "</tr>";
                                $h .= "</thead>";
                                $h .= "<tbody>";
                                    $ctr = 0;
                                    while($row2 = mysqli_fetch_assoc($result2)) {
                                        $ctr += 1;
                                        $citizen = (new PhCitizen($row2['ID'], 'for getting data of citizen with no leader', false))->get_data();
                                        $h .= "<tr>";
                                            $h .= "<td align='right' class='text-" . $data['text_class'] . "'>$ctr.</td>";
                                            $h .= "<td>";
                                                $h .= "<a href='dashboard.php?citizens-1-" . $citizen['id'] . "' target='_blank' class='text-" . $data['text_class'] . "'><b>" . $citizen['full_name_3'] . "</b></a>";
                                            $h .= "</td>";
                                        $h .= "<td align='center' class='monospace text-" . $data['text_class'] . "'>" . $citizen['date_of_birth_1'] . "</td>";
                                        $h .= "<td align='center' class='text-" . $data['text_class'] . "'>" . $citizen['contact_number'] . "</td>";
                                        $h .= "<td align='center' class='text-" . $data['text_class'] . "'>" . $citizen['barangay']['name'] . "</td>";
                                        $h .= "</tr>";
                                    }
                                $h .= "<tbody>";
                            $h .= "</table>";
                        $h .= "</div>";
                    }
                }
				
				// DEFAULT LEADERS
                else if($data['id'] == 3) {
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
                    $total = mysqli_num_rows($result2);
                    $h .= "<h4 class='no-margin'>DEFAULT LEADER" . ($total > 1 ? 'S' : '') . ": <big><b class='text-" . $data['text_class'] . "'>" . number_format($total, 0, '.', ',') . "</b></big></h4>";

                    if($total > 0) {
                        $h .= "<div class='table-responsive' style='overflow-y: hidden'>";
                            $h .= "<table class='table table-hover table-bordered'>";
                                $h .= "<thead>";
                                    $h .= "<tr>";
                                        $h .= "<th></th>";
                                        $h .= "<th><h6 class='text-" . $data['text_class'] . "'><b>Name</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Birth Date</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Contact No.</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Barangay</b></h6></th>";
                                    $h .= "</tr>";
                                $h .= "</thead>";
                                $h .= "<tbody>";
                                    $ctr = 0;
                                    while($row2 = mysqli_fetch_assoc($result2)) {
                                        $ctr += 1;
                                        $citizen = (new PhCitizen($row2['CitizenID'], 'for getting data of default leader', false))->get_data();
                                        $h .= "<tr>";
                                            $h .= "<td align='right' class='text-" . $data['text_class'] . "'>$ctr.</td>";
                                            $h .= "<td>";
                                                $h .= "<a href='dashboard.php?leaders-1-" . intval($row2['ID']) . "' target='_blank' class='text-" . $data['text_class'] . "'><b>" . $citizen['full_name_3'] . "</b></a>";
                                            $h .= "</td>";
                                        $h .= "<td align='center' class='monospace text-" . $data['text_class'] . "'>" . $citizen['date_of_birth_1'] . "</td>";
                                        $h .= "<td align='center' class='text-" . $data['text_class'] . "'>" . $citizen['contact_number'] . "</td>";
                                        $h .= "<td align='center' class='text-" . $data['text_class'] . "'>" . $citizen['barangay']['name'] . "</td>";
                                        $h .= "</tr>";
                                    }
                                $h .= "<tbody>";
                            $h .= "</table>";
                        $h .= "</div>";
                    }
                }
				
				// UNREGISTERED
                else if($data['id'] == 4) {
                    $GLOBALS['query'] = "SELECT `ID` FROM `ph_citizens` WHERE `IsRegisteredVoter`=0 ORDER BY `LastName`, `FirstName`, `MiddleName`";
                    $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(!has_no_db_error('for getting unregistered citizens'))
                        fin();
                    $total = mysqli_num_rows($result2);
                    $h .= "<h4 class='no-margin'>UNREGISTERED CITIZEN" . ($total > 1 ? 'S' : '') . ": <big><b class='text-" . $data['text_class'] . "'>" . number_format($total, 0, '.', ',') . "</b></big></h4>";

                    if($total > 0) {
                        $h .= "<div class='table-responsive' style='overflow-y: hidden'>";
                            $h .= "<table class='table table-hover table-bordered'>";
                                $h .= "<thead>";
                                    $h .= "<tr>";
                                        $h .= "<th></th>";
                                        $h .= "<th><h6 class='text-" . $data['text_class'] . "'><b>Name</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Birth Date</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Contact No.</b></h6></th>";
                                        $h .= "<th class='text-center'><h6 class='text-" . $data['text_class'] . "'><b>Barangay</b></h6></th>";
                                    $h .= "</tr>";
                                $h .= "</thead>";
                                $h .= "<tbody>";
                                    $ctr = 0;
                                    while($row2 = mysqli_fetch_assoc($result2)) {
                                        $ctr += 1;
                                        $citizen = (new PhCitizen($row2['ID'], 'for getting data of unregistered citizen', false))->get_data();
                                        $h .= "<tr>";
                                            $h .= "<td align='right' class='text-" . $data['text_class'] . "'>$ctr.</td>";
                                            $h .= "<td>";
                                                $h .= "<a href='dashboard.php?citizens-1-" . $citizen['id'] . "' target='_blank' class='text-" . $data['text_class'] . "'><b>" . $citizen['full_name_3'] . "</b></a>";
                                            $h .= "</td>";
                                        $h .= "<td align='center' class='monospace text-" . $data['text_class'] . "'>" . $citizen['date_of_birth_1'] . "</td>";
                                        $h .= "<td align='center' class='text-" . $data['text_class'] . "'>" . $citizen['contact_number'] . "</td>";
                                        $h .= "<td align='center' class='text-" . $data['text_class'] . "'>" . $citizen['barangay']['name'] . "</td>";
                                        $h .= "</tr>";
                                    }
                                $h .= "<tbody>";
                            $h .= "</table>";
                        $h .= "</div>";
                    }
                }


                $arr = array(
                    array(
                        'class' => 'form form-group-break',
                        'rows'  => array(
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
             * Report :: search
             *
             * Search through Report records
             * @access public static
             *
             * @param string $q - the search query
             *
             */
            public static function search($q) {
                $q = mysqli_real_escape_string($GLOBALS['con'], $q);
                $GLOBALS['query']  = "SELECT ";
                $GLOBALS['query'] .= " `" . self::$primary_key . "` ";
                $GLOBALS['query'] .= "FROM ";
                $GLOBALS['query'] .= " `" . self::$table . "` ";
                $GLOBALS['query'] .= "WHERE ";
                $GLOBALS['query'] .= "   `" . self::$primary_key . "`=" . intval($q) . " ";
                $GLOBALS['query'] .= " OR `Title` LIKE '%$q%' ";
                $GLOBALS['query'] .= " OR `Description` LIKE '%$q%' ";
                $GLOBALS['query'] .= self::$list_order;
                parent::search_helper(get_class());
            }
        }

        if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
            $object = new Report(0, 'for class usage');
            require '__fin.php';
        }
    }
?>