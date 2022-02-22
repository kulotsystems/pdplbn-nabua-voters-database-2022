<?php
    require '__init.php';

    if(!class_exists('BarangayCitizens')) {
        class BarangayCitizens extends __init {

            // class variables
            public static $tab_singular         = 'BRGY. CITIZEN';
            public static $table                = 'ph_barangays';
            public static $primary_key          = 'ID';
            public static $primary_zero_allowed = false;
            public static $list_order           = 'ORDER BY `Description`';

            public static $params = array(
                'list'   => array(
                    'key'     => 'list_barangaycitizens',
                    'enabled' => true
                ),
                'select' => array(
                    'key'     => 'select_barangaycitizens',
                    'enabled' => true
                ),
                'search' => array(
                    'key'     => 'search_barangaycitizens',
                    'enabled' => true
                )
            );


            /****************************************************************************************************
             * BarangayCitizens :: CONSTRUCTOR
             *
             * Initialize the BarangayCitizens object.
             * @access public
             *
             * @param int $barangay_citizens_id - the BarangayCitizens id
             * @param string $purpose - the usage of this operation
             * @param bool $metadata
             */
            public function __construct($barangay_citizens_id = 0, $purpose = '', $metadata = true) {
                $this->data = array(
                    'id' => 0,
                );

                $barangay_citizens_id = intval($barangay_citizens_id);
                if(($barangay_citizens_id > 0 || self::$primary_zero_allowed) && $purpose != 'for class usage') {
                    $purpose = 'getting BarangayCitizens info' . cascade_purpose($purpose);
                    $GLOBALS['query']  = "SELECT ";
                    $GLOBALS['query'] .= " `ID` ";
                    $GLOBALS['query'] .= "FROM ";
                    $GLOBALS['query'] .= " `" . self::$table . "` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= "     `" . self::$primary_key . "`=$barangay_citizens_id";
                    $GLOBALS['query'] .= " AND `MunCityID`=" . __init::$muncity_id;
                    $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if (has_no_db_error($purpose)) {
                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);

                            require INDEX . 'php/models/PhBarangay.php';
                            require INDEX . 'php/models/PhCitizen.php';
                            $barangay = new PhBarangay($row['ID'], 'for getting barangay for citizens');

                            // get barangay citizens
                            $arr_citizens = array();
                            if($metadata) {
                                $GLOBALS['query']  = "SELECT ";
                                $GLOBALS['query'] .= " `ID` ";
                                $GLOBALS['query'] .= "FROM ";
                                $GLOBALS['query'] .= " `ph_citizens` ";
                                $GLOBALS['query'] .= "WHERE ";
                                $GLOBALS['query'] .= " `BarangayID`=" . $row['ID'] . " ";
                                $GLOBALS['query'] .= "ORDER BY `LastName`, `FirstName`, `MiddleName`";
                                $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                if(!has_no_db_error('for getting barangay leaders'))
                                    fin();
                                while($row2 = mysqli_fetch_assoc($result2)) {
                                    $barangay_citizen = (new PhCitizen($row2['ID'], 'for getting citizen data'))->get_data();
                                    array_push($arr_citizens, $barangay_citizen);
                                }
                            }

                            $this->data = array(
                                'id'       => $barangay_citizens_id,
                                'barangay' => $barangay->get_data(),
                                'citizens' => $arr_citizens
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
             * BarangayCitizens :: get_item
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
             * BarangayCitizens :: get_form
             *
             * Generate form for BarangayCitizens object
             * @access public static
             *
             * @param array $data     - the item data
             * @param bool  $for_logs - for system logs or not
             *
             * @return array
             */
            public static function get_form($data, $for_logs) {
                $h  = "<h4 class='no-margin'><b>" . $data['barangay']['name'] . "</b> Barangay Citizen" . (sizeof($data['citizens']) > 1 ? 's' : '') . ": <big><b class='text-success'>" . number_format(sizeof($data['citizens']), 0, '.', ',') . "</b></big></h4>";
                $h .= "<div class='table-responsive' style='overflow-y: hidden'>";
                    $h .= "<table class='table table-hover table-bordered'>";
                        $h .= "<thead>";
                            $h .= "<tr>";
                                $h .= "<th></th>";
                                $h .= "<th><h6><b>Name</b></h6></th>";
                                $h .= "<th class='text-center'><h6><b>Birth Date</b></h6></th>";
                                $h .= "<th class='text-center'><h6><b>Contact No.</b></h6></th>";
                                $h .= "<th class='text-center'><h6><b>Leader</b></h6></th>";
                            $h .= "</tr>";
                        $h .= "</thead>";
                        $h .= "<tbody>";
                            for($i=0; $i<sizeof($data['citizens']); $i++) {
                                $citizen = $data['citizens'][$i];
                                $h .= "<tr class='vertical-middle'>";
                                    $h .= "<td align='right'>" . ($i + 1) . ".</td>";
                                    $h .= "<td>";
                                        $h .= "<a href='dashboard.php?citizens-1-" . $citizen['id'] . "' target='_blank' class='text-success'><b>" . $citizen['full_name_3'] . "</b></a>";
                                        $h .= "<br>";
                                        $h .= "<small>";
                                            if($citizen['is_registered_voter'])
                                                $h .= "<span class='text-success'>Registered</span>";
                                            else
                                                $h .= "<span class='text-danger'>Unregistered</span>";
                                        $h .= "</small>";
                                    $h .= "</td>";
                                    $h .= "<td align='center' class='monospace'>" . $citizen['date_of_birth_1'] . "</td>";
                                    $h .= "<td align='center'>" . $citizen['contact_number'] . "</td>";
                                    $h .= "<td>" . (isset($citizen['leader']['leader_id']) ? "<a href='dashboard.php?leaders-1-" . $citizen['leader']['leader_id'] . "' target='_blank' class='text-success'>" .$citizen['leader']['full_name_3'] . "</a>"  : '') . "</td>";
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
             * BarangayCitizens :: print_html
             *
             * Print barangay_citizens data as HTML
             * @access public static
             *
             * @param int $barangay_id  - the Barangay id
             * @param string $form_data - the form data to base the data to be printed
             *
             * @return string (html)
             *
             */
            public static function print_html($barangay_id, $form_data) {
                echo "<!DOCTYPE html>";
                    echo "<html lang='en'>";
                    echo "<head>";
                        require INDEX . "ui/imports.global.php";
                        echo "<style>* { color: #000000 !important; }</style>";
                    echo "</head>";
                    echo "<body style='background: white !important'>";
                        $barangay_citizens = new BarangayCitizens($barangay_id, 'for printing');
                        $form = BarangayCitizens::get_form($barangay_citizens->get_data(), false);
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

                return true;
            }


            /****************************************************************************************************
             * BarangayCitizens :: search
             *
             * Search through BarangayCitizens records
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
            $object = new BarangayCitizens(0, 'for class usage');
            require '__fin.php';
        }
    }
?>