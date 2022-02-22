<?php
    require '__init.php';

    if(!class_exists('Leader')) {
        class Leader extends __init {

            // class variables
            public static $tab_singular         = 'LEADER';
            public static $table                = 'leaders';
            public static $primary_key          = 'ID';
            public static $primary_zero_allowed = false;
            public static $list_order           = 'ORDER BY `ID` DESC';

            public static $params = array(
                'list'   => array(
                    'key'     => 'list_leader',
                    'enabled' => true
                ),
                'create' => array(
                    'key'     => 'create_leader',
                    'enabled' => true
                ),
                'select' => array(
                    'key'     => 'select_leader',
                    'enabled' => true
                ),
                'update' => array(
                    'key'     => 'update_leader',
                    'enabled' => true
                ),
                'delete' => array(
                    'key'     => 'delete_leader',
                    'enabled' => true
                ),
                'search' => array(
                    'key'     => 'search_leader',
                    'enabled' => true
                )
            );


            /****************************************************************************************************
             * Leader :: CONSTRUCTOR
             *
             * Initialize the Leader object.
             * @access public
             *
             * @param int $leader_id - the Leader id
             * @param string $purpose - the usage of this operation
             * @param bool $metadata
             */
            public function __construct($leader_id = 0, $purpose = '', $metadata = true) {
                $this->data = array(
                    'id' => 0,
                );

                $leader_id = intval($leader_id);
                if(($leader_id > 0 || self::$primary_zero_allowed) && $purpose != 'for class usage') {
                    $purpose = 'getting Leader info' . cascade_purpose($purpose);
                    $GLOBALS['query']  = "SELECT ";
                    $GLOBALS['query'] .= " `CitizenID`, ";
                    $GLOBALS['query'] .= " `LeaderTypeID`, ";
                    $GLOBALS['query'] .= "  DATE_FORMAT(`CreatedAt`, '%M %e, %Y &middot; %h:%i %p') AS CreatedAt, ";
                    $GLOBALS['query'] .= "  DATE_FORMAT(`UpdatedAt`, '%M %e, %Y &middot; %h:%i %p') AS UpdatedAt ";
                    $GLOBALS['query'] .= "FROM ";
                    $GLOBALS['query'] .= " `" . self::$table . "` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= " `" . self::$primary_key . "`=$leader_id";
                    $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if (has_no_db_error($purpose)) {
                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);

                            require INDEX . 'php/models/PhCitizen.php';
                            require INDEX . 'php/models/LeaderType.php';
                            $citizen     = new PhCitizen($row['CitizenID'], $purpose);
                            $leader_type = new LeaderType($row['LeaderTypeID'], $purpose);

                            $arr_followers = array();

                            if($metadata) {
                                // get followers
                                $arr_followers = array(
                                    array(
                                        'id'      => 0,
                                        'citizen' => (new PhCitizen(0, 'for creating default follower of Leader'))->get_data()
                                    )
                                );
                                $GLOBALS['query']  = "SELECT ";
                                $GLOBALS['query'] .= " `followers`.`ID`, ";
                                $GLOBALS['query'] .= " `followers`.`CitizenID` ";
                                $GLOBALS['query'] .= "FROM ";
                                $GLOBALS['query'] .= " `followers`, `ph_citizens` ";
                                $GLOBALS['query'] .= "WHERE ";
                                $GLOBALS['query'] .= "     `followers`.`CitizenID` = `ph_citizens`.`ID` ";
                                $GLOBALS['query'] .= " AND `followers`.`LeaderID`  = $leader_id ";
                                $GLOBALS['query'] .= "ORDER BY ";
                                $GLOBALS['query'] .= " `ph_citizens`.`LastName`, `ph_citizens`.`FirstName`, `ph_citizens`.`MiddleName`";
                                $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                if(has_no_db_error('for getting followers of Leader')) {
                                    while($row2 = mysqli_fetch_assoc($result2)) {
                                        $follower = new PhCitizen($row2['CitizenID'], 'for getting followers of Leader');
                                        array_push($arr_followers, array(
                                            'id'      => $row2['ID'],
                                            'citizen' => $follower->get_data()
                                        ));
                                    }
                                }
                                else
                                    fin();
                            }

                            $this->data = array(
                                'id'           => $leader_id,
                                'citizen'      => $citizen->get_data(),
                                'leader_type'  => $leader_type->get_data(),
                                'followers'    => $arr_followers,
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
             * Leader :: get_item
             *
             * Get the data to be displayed on items list
             * @access public
             *
             * @return array
             */
            public function get_item() {
                $citizen     = $this->get_data('citizen');
                $leader_type = $this->get_data('leader_type');

                return $this->get_item_helper(
                    $this->get_data('id'),
                    $citizen['avatar'],
                    $citizen['full_name_3'],
                    $leader_type['title'] . ($citizen['barangay']['name'] != '' ? ' &middot; ' : '') . $citizen['barangay']['name'],
                    ''
                );
            }


            /****************************************************************************************************
             * Leader :: create
             *
             * Create Leader item for create.js
             * @access public static
             *
             */
            public static function create() {
                $value = $GLOBALS['arr_admin_details']['citizen_id'];
                $GLOBALS['query'] = "INSERT INTO `" . self::$table . "`(`CitizenID`, `IsDefault`) VALUES('$value', 1)";
                mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                if (has_no_db_error('for creating new Leader')) {
                    parent::create_helper(new Leader(mysqli_insert_id($GLOBALS['con']), 'for getting newly created Leader'));
                }
            }


            /****************************************************************************************************
             * Leader :: get_form
             *
             * Generate form for Leader object
             * @access public static
             *
             * @param array $data     - the item data
             * @param bool  $for_logs - for system logs or not
             *
             * @return array
             */
            public static function get_form($data, $for_logs) {
                $arr = array(
                    array(
                        'class' => 'form form-group-attached',
                        'rows'  => array(
                            // form_data[0]['rows'][0]
                            array(
                                array(
                                    'grid'     => 'col-md-8',
                                    'type'     => 'searchbox',
                                    'model'    => 'PhCitizen',
                                    'id'       => 'srch-citizen',
                                    'label'    => 'LEADER',
                                    'avatar'   => isset($data['citizen']['avatar']) ? $data['citizen']['avatar'] : '',
                                    'value'    => isset($data['citizen']['full_name_3']) ? $data['citizen']['full_name_3'] : '',
                                    'key'      => isset($data['citizen']['id']) ? $data['citizen']['id'] : '',
                                    'disabled' => $for_logs
                                ),
                                array(
                                    'grid'     => 'col-md-4',
                                    'type'     => 'searchbox',
                                    'role'     => 'dropdown',
                                    'model'    => 'LeaderType',
                                    'id'       => 'srch-leadertype',
                                    'label'    => 'LEADER TYPE',
                                    'value'    => isset($data['leader_type']['title']) ? $data['leader_type']['title'] : '',
                                    'key'      => isset($data['leader_type']['id']) ? $data['leader_type']['id'] : 0,
                                    'disabled' => $for_logs,
                                    'sync_num' => '1'
                                )
                            )
                        )
                    ),

                    array(
                        'class' => 'form forms form-followers',
                        'forms' => array()
                    ),

                    array(
                        'class' => 'form form-group-break',
                        'rows'  => array(
                            // form_data[1]['rows'][0]
                            array(
                                array(
                                    'grid'     => 'col-md-12 offset-md-3',
                                    'type'     => 'label',
                                    'id'       => 'lbl-add-follower',
                                    'value'    => !$for_logs ? '<span class="text-success btn-span btn-add-follower" data-ref=".form.form-followers"><span class="fa fa-plus-circle fa-fw"></span>' . ' ADD MEMBER</span>' : ''
                                )
                            )
                        )
                    ),
                );

                // populate follower form
                if(isset($data['followers'])) {
                    for($i=0; $i<sizeof($data['followers']); $i++) {
                        $follower = $data['followers'][$i];
                        array_push($arr[1]['forms'], array(
                            'class' => 'form form-group-break' . ($i <= 0 ? ' hidden' : ''),
                            'rows'  => array(
                                array(
                                    array(
                                        'grid'     => 'col-md-6 offset-md-3',
                                        'type'     => 'label',
                                        'id'       => 'lbl-follower-' . $i,
                                        'value'    => (!$for_logs ? '<span class="btn-span btn-remove-follower"><span class="fa fa-times-circle fa-fw text-danger"></span></span>' : '') . ' #<span class="n-follower">' . $i . '</span> MEMBER'
                                    )
                                )
                            )
                        ));

                        $index = intval($follower['id']);
                        array_push($arr[1]['forms'], array(
                            'class' => 'form form-group-attached' . ($i <= 0 ? ' hidden' : ''),
                            'rows'  => array(
                                array(
                                    array(
                                        'grid'     => 'col-md-6 offset-md-3',
                                        'type'     => 'searchbox',
                                        'model'    => 'PhCitizen',
                                        'id'       => 'srch-follower-' . $index,
                                        'label'    => 'MEMBER',
                                        'avatar'   => isset($follower['citizen']['avatar']) ? $follower['citizen']['avatar'] : '',
                                        'value'    => isset($follower['citizen']['full_name_3']) ? $follower['citizen']['full_name_3'] : '',
                                        'key'      => isset($follower['citizen']['id']) ? $follower['citizen']['id'] : '',
                                        'disabled' => $for_logs
                                    ),
                                )
                            )
                        ));
                    }
                }

                return $arr;
            }


            /****************************************************************************************************
             * Leader :: print_html
             *
             * Print leader data as HTML
             * @access public static
             *
             * @param int $leader_id    - the Leader id
             * @param string $form_data - the form data to base the data to be printed
             *
             * @return string (html)
             *
             */
            public static function print_html($leader_id, $form_data) {
                if($form_data != 'masterlist') {
                    echo "<!DOCTYPE html>";
                    echo "<html lang='en'>";
                    echo "<head>";
                        require INDEX . "ui/imports.global.php";
                        echo "<style>* { color: #000000 !important; }</style>";
                    echo "</head>";
                    echo "<body style='background: white !important'>";
                }
                    $leader = (new Leader($leader_id, 'for printing'))->get_data();
                    echo "<div style='padding: 15px;" . ($form_data == 'masterlist' ? ' padding-top: 0' : '') . "'>";
                        echo "<p class='no-margin'>" . date('F d, Y h:i A', time()) . "</p>";
                        echo "<table class='table table-bordered'>";
                            echo "<thead>";
                                /*echo "<tr>";
                                    echo "<td colspan='6' class='no-padding padding-left-15 padding-right-15'><h3><b>LEADER</b></h3></td>";
                                echo "</tr>";*/
                                echo "<tr>";
                                    echo "<th></th>";
                                    echo "<th><h6><b>Name</b></h6></th>";
                                    echo "<th class='text-center'><h6><b>Birth Date</b></h6></th>";
                                    echo "<th class='text-center'><h6><b>Contact No.</b></h6></th>";
                                    echo "<th class='text-center'><h6><b>Barangay</b></h6></th>";
                                    echo "<th class='text-center'><h6><b>Signature</b></h6></th>";
                                echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                                echo "<tr class='vertical-middle'>";
                                    echo "<td class='bg-active' align='right'></td>";
                                    echo "<td class='bg-active'>";
                                        echo "<b>" . $leader['citizen']['full_name_3'] . "</b>";
                                        echo "<br>";
                                        echo "<small>" . $leader['leader_type']['title'] . "</small>";
                                    echo "</td>";
                                    echo "<td class='bg-active monospace' align='center'><b>" . $leader['citizen']['date_of_birth_1'] . "</b></td>";
                                    echo "<td class='bg-active' align='center'><b>" . $leader['citizen']['contact_number'] . "</b></td>";
                                    echo "<td class='bg-active' align='center'><b>" . $leader['citizen']['barangay']['name'] . "</b></td>";
                                    echo "<td class='bg-active' align='center'></td>";
                                echo "</tr>";
                                echo "<tr class='vertical-middle'>";
                                    echo "<td colspan='6' class='no-padding padding-left-15 padding-right-15'>";
                                        echo "<h4><b>MEMBERS: " . (sizeof($leader['followers']) > 1 ? (sizeof($leader['followers']) - 1) : '') . "</b></h4>";
                                    echo "</td>";
                                echo "</tr>";
                                if(sizeof($leader['followers']) <= 1) {
                                    echo "<tr>";
                                        echo "<td colspan='6' class='no-padding padding-left-15 padding-right-15'>";
                                            echo "<h6><b>(NONE)</b></h6>";
                                        echo "</td>";
                                    echo "</tr>";
                                }
                                else {
                                    for($i=1; $i<sizeof($leader['followers']); $i++) {
                                        $follower = $leader['followers'][$i];
                                        echo "<tr class='vertical-middle tr-follower'>";
                                            echo "<td align='right'>" . $i . ".</td>";
                                            echo "<td>";
                                                echo "<b>" . $follower['citizen']['full_name_3'] . "</b>";
                                                if(!$follower['citizen']['is_registered_voter']) {
                                                    echo "<br>";
                                                    echo "<small class='text-danger'>UNREGISTERED</small>";
                                                }
                                            echo "</td>";
                                            echo "<td align='center' class='monospace'>" . $follower['citizen']['date_of_birth_1'] . "</td>";
                                            echo "<td align='center'>" . $follower['citizen']['contact_number'] . "</td>";
                                            echo "<td align='center'>" . $follower['citizen']['barangay']['name'] . "</td>";
                                            echo "<td align='center'></td>";
                                        echo "</tr>";
                                    }
                                }

                            echo "</tbody>";
                        echo "</table>";
                    echo "</div>";
                if($form_data != 'masterlist') {
                    echo "<script>";
                        echo "window.print();";
                    echo "</script>";
                    echo "</body>";
                    echo "</html lang='en'>";
                }
            }


            /****************************************************************************************************
             * Leader :: update
             *
             * Update Leader item for update.js
             * @access public static
             *
             */
            public static function update() {
                $leader_id  = intval($_POST[self::$params['update']['key']]);
                $leader     = new Leader($leader_id, 'for update');
                $form_data  = $_POST['data'];
                $citizen_id = mysqli_real_escape_string($GLOBALS['con'], strip_tags(trim($form_data[0]['rows'][0]['srch-citizen'])));
                $leader_type_id = intval($form_data[0]['rows'][0]['srch-leadertype']);

                // check if $citizen_id is available
                $is_leader_available = true;
                // if($citizen_id != 1) {
                    $GLOBALS['query']  = "SELECT ";
                    $GLOBALS['query'] .= " `ID` ";
                    $GLOBALS['query'] .= "FROM ";
                    $GLOBALS['query'] .= " `leaders` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= "    `ID` != $leader_id ";
                    $GLOBALS['query'] .= "AND `CitizenID`=$citizen_id";
                    $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if(has_no_db_error('for checking if citizen is already a leader'))
                        $is_leader_available = mysqli_num_rows($result) <= 0;
                    else
                        fin();
                // }
                if($is_leader_available) {
                    // get followers
                    $got_all_followers = false;
                    $citizen_ids       = array();
                    $i = 2;
                    while(!$got_all_followers) {
                        if($i < sizeof($form_data)) {
                            $form = $form_data[$i];
                            if($form['class'] == 'form-group-attached') {
                                $follower_citizen_id = array_values($form['rows'][0])[0];
                                if($follower_citizen_id > 0)
                                    array_push($citizen_ids, $follower_citizen_id);
                            }
                            else {
                                if(isset($form['rows'][0]['lbl-add-follower'])) {
                                    $got_all_followers = true;
                                    break;
                                }
                            }
                            $i += 1;
                        }
                        else
                            $got_all_followers = true;
                    }

                    // remove duplicate followers
                    $citizen_ids = array_unique($citizen_ids);

                    // check if all followers are available
                    $are_followers_available = true;
                    foreach ($citizen_ids as $follower_citizen_id) {
                        $GLOBALS['query'] = "SELECT `ID` FROM `leaders` WHERE `CitizenID`=$follower_citizen_id";
                        $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                        if(has_no_db_error('for checking if follower is also a leader')) {
                            while($row = mysqli_fetch_assoc($result)) {
                                require INDEX . 'php/models/PhCitizen.php';
                                $existing_leader   = new PhCitizen($follower_citizen_id, 'for getting existing leader data');
                                $GLOBALS['response']['success']['message'] = 'CONFLICT';
                                $GLOBALS['response']['success']['sub_message'] = 'You cannot add [LEADER] <b>' . $existing_leader->get_data('full_name_3') . '</b> as a MEMBER';
                                $are_followers_available = false;
                                break;
                            }
                        }
                        if($are_followers_available) {
                            $GLOBALS['query']  = "SELECT ";
                            $GLOBALS['query'] .= " `LeaderID` ";
                            $GLOBALS['query'] .= "FROM ";
                            $GLOBALS['query'] .= " `followers` ";
                            $GLOBALS['query'] .= "WHERE ";
                            $GLOBALS['query'] .= "     `CitizenID`=$follower_citizen_id ";
                            $GLOBALS['query'] .= " AND `LeaderID`!=$leader_id";
                            $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                            if(has_no_db_error('for checking if follower is already taken')) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    require INDEX . 'php/models/PhCitizen.php';
                                    $existing_leader   = new PhCitizen($row['LeaderID'], 'for getting existing leader data');
                                    $existing_follower = new PhCitizen($follower_citizen_id, 'for getting existing follower data');
                                    $GLOBALS['response']['success']['message']     = 'DUPLICATE MEMBER!';
                                    $GLOBALS['response']['success']['sub_message'] = '[MEMBER] <b>' . $existing_follower->get_data('full_name_3') . '</b> ' .
                                        'was already recruited by ' .
                                        '[LEADER] <b>' . $existing_leader->get_data('full_name_3') . '</b>';
                                    $are_followers_available = false;
                                    break;
                                }
                            }
                        }
                    }

                    if($are_followers_available) {
                        // delete previous followers
                        $GLOBALS['query'] = "DELETE FROM `followers` WHERE `LeaderID`=$leader_id";
                        mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                        if(has_no_db_error('for deleting previous followers upon updating Leader data')) {

                            // insert new followers
                            $GLOBALS['query']  = "INSERT INTO `followers`(`LeaderID`, `CitizenID`) ";
                            $GLOBALS['query'] .= "VALUES ";
                            for($i=0; $i<sizeof($citizen_ids); $i++) {
                                $GLOBALS['query'] .= "(" . $leader_id . ", " . $citizen_ids[$i] . ")";
                                if($i < sizeof($citizen_ids)-1)
                                    $GLOBALS['query'] .= ",";
                                $GLOBALS['query'] .= " ";
                            }
                            if(sizeof($citizen_ids) > 0)
                                mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                            if(has_no_db_error('for inserting new followers upon updating Leader data')) {
                                // remove leader as a follower
                                $GLOBALS['query']  = "DELETE FROM `followers` WHERE `CitizenID`=$citizen_id";
                                mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                if(has_no_db_error('for removing leader as a follower')) {
                                    // determine if leader is default
                                    $is_default       = '0';
                                    $GLOBALS['query'] = "SELECT `CitizenID` FROM `users` WHERE `CitizenID`=$citizen_id";
                                    $result_user      = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                    if(!has_no_db_error('for getting if leader has a user account'))
                                        fin();
                                    if(mysqli_num_rows($result_user) > 0) {
                                        if(sizeof($citizen_ids) <= 0)
                                            $is_default = '1';
                                    }

                                    // update Leader data
                                    $GLOBALS['query']  = "UPDATE `" . self::$table . "` ";
                                    $GLOBALS['query'] .= "SET ";
                                    $GLOBALS['query'] .= " `CitizenID`=$citizen_id, ";
                                    $GLOBALS['query'] .= " `LeaderTypeID`=$leader_type_id, ";
                                    $GLOBALS['query'] .= " `IsDefault`=$is_default ";
                                    $GLOBALS['query'] .= "WHERE ";
                                    $GLOBALS['query'] .= " `" . self::$primary_key . "`=$leader_id";
                                    mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                    if (has_no_db_error('for updating Leader data')) {
                                        parent::update_helper(new Leader($leader_id, 'for getting newly updated Leader'), $leader->get_item());
                                    }
                                }
                            }
                        }
                    }
                }
                else {
                    $GLOBALS['response']['success']['message']     = 'DUPLICATE FOUND!';
                    $GLOBALS['response']['success']['sub_message'] = 'The selected citizen is already a leader.<br>Please search for a different one.';
                    fin();
                }
            }


            /****************************************************************************************************
             * Leader :: delete
             *
             * Delete Leader item for delete.js
             * @access public
             *
             */
            public function delete() {
                if(false) {
                    // TODO :: Related records check
                }
                else
                    parent::delete_helper($this);
            }


            /****************************************************************************************************
             * Leader :: search
             *
             * Search through Leader records
             * @access public static
             *
             * @param string $q - the search query
             *
             */
            public static function search($q) {
                $q = mysqli_real_escape_string($GLOBALS['con'], $q);

                require INDEX . 'php/models/PhCitizen.php';
                require INDEX . 'php/models/PhBarangay.php';

                $GLOBALS['query']  = "SELECT ";
                $GLOBALS['query'] .= " `" . self::$table . "`.`" . self::$primary_key . "` ";
                $GLOBALS['query'] .= "FROM ";
                $GLOBALS['query'] .= " `" . self::$table . "`, ";
                $GLOBALS['query'] .= " `" . PhCitizen::$table . "`, ";
                $GLOBALS['query'] .= " `" . PhBarangay::$table . "` ";
                $GLOBALS['query'] .= "WHERE ";
                $GLOBALS['query'] .= "     `" . PhCitizen::$table . "`.`" . PhBarangay::$foreign_key . "`=`" . PhBarangay::$table . "`.`" . PhBarangay::$primary_key . "` ";
                $GLOBALS['query'] .= " AND `" . PhCitizen::$table . "`.`" . PhCitizen::$primary_key . "`=`" . self::$table . "`.`" . PhCitizen::$foreign_key . "` ";
                $GLOBALS['query'] .= " AND ";
                $GLOBALS['query'] .= " ( ";
                $GLOBALS['query'] .= "      `" . PhCitizen::$table . "`.`" . PhCitizen::$primary_key . "`=" . intval($q) . " ";
                $GLOBALS['query'] .= "   OR `" . PhCitizen::$table . "`.`FirstName` LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR `" . PhCitizen::$table . "`.`MiddleName` LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR `" . PhCitizen::$table . "`.`LastName` LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . PhCitizen::$table . "`.`FirstName`, ' ', `" . PhCitizen::$table . "`.`LastName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . PhCitizen::$table . "`.`FirstName`, ' ', `" . PhCitizen::$table . "`.`MiddleName`, ' ', `" . PhCitizen::$table . "`.`LastName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . PhCitizen::$table . "`.`FirstName`, ' ', MID(`" . PhCitizen::$table . "`.`MiddleName`, 1, 1), '. ', `" . PhCitizen::$table . "`.`LastName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . PhCitizen::$table . "`.`LastName`, ' ', `" . PhCitizen::$table . "`.`FirstName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . PhCitizen::$table . "`.`LastName`, ', ', `" . PhCitizen::$table . "`.`FirstName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . PhCitizen::$table . "`.`LastName`, ', ', `" . PhCitizen::$table . "`.`FirstName`, ' ', `" . PhCitizen::$table . "`.`MiddleName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . PhCitizen::$table . "`.`LastName`, ', ', `" . PhCitizen::$table . "`.`FirstName`, ' ', MID(`" . PhCitizen::$table . "`.`MiddleName`, 1, 1), '.') LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR `" . PhBarangay::$table . "`.`Description` LIKE '%$q%' ";
                $GLOBALS['query'] .= " ) ";
                $GLOBALS['query'] .= "ORDER BY ";
                $GLOBALS['query'] .= " `" . PhCitizen::$table . "`.`LastName`, `" . PhCitizen::$table . "`.`FirstName`, `" . PhCitizen::$table . "`.`MiddleName` ";
                parent::search_helper(get_class());
            }
        }

        if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
            $object = new Leader(0, 'for class usage');
            require '__fin.php';
        }
    }
?>