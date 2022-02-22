<?php
    require '__init.php';

    if(!class_exists('PhCitizen')) {
        class PhCitizen extends __init {

            // class variables
            public static $tab_singular         = 'CITIZEN';
            public static $table                = 'ph_citizens';
            public static $primary_key          = 'ID';
            public static $foreign_key          = 'CitizenID';
            public static $primary_zero_allowed = false;
            public static $list_order           = 'ORDER BY `ID` DESC';

            public static $params = array(
                'list'     => array(
                    'key'     => 'list_phcitizen',
                    'enabled' => true
                ),
                'create'   => array(
                    'key'     => 'create_phcitizen',
                    'enabled' => true
                ),
                'select'   => array(
                    'key'     => 'select_phcitizen',
                    'enabled' => true
                ),
                'update'   => array(
                    'key'     => 'update_phcitizen',
                    'enabled' => true
                ),
                'delete'   => array(
                    'key'     => 'delete_phcitizen',
                    'enabled' => true
                ),
                'search'   => array(
                    'key'     => 'search_phcitizen',
                    'enabled' => true
                ),
                'callback' => array(
                    'key'     => 'callback_phcitizen',
                    'enabled' => true
                )
            );


            /****************************************************************************************************
             * PhCitizen :: CONSTRUCTOR
             *
             * Initialize the citizen object.
             * @access public
             *
             * @param int $citizen_id - the citizen id
             * @param string $purpose - the usage of this operation
             * @param bool $metadata
             */
            public function __construct($citizen_id = 0, $purpose = '', $metadata = true) {
                $this->data = array(
                    'id'          => 0,
                    'first_name'  => '[CITIZEN_FIRSTNAME]',
                    'middle_name' => '[CITIZEN_MIDDLENAME]',
                    'last_name'   => '[CITIZEN_LASTNAME]',
                    'full_name_1' => '',
                    'full_name_2' => '',
                    'full_name_3' => '',
                    'full_name_4' => '',
                    'avatar'      => CITIZEN_AVATAR_URL.'___.jpg'
                );

                $citizen_id = intval($citizen_id);
                if(($citizen_id > 0 || self::$primary_zero_allowed) && $purpose != 'for class usage') {
                    $purpose = 'getting citizen info' . cascade_purpose($purpose);
                    $GLOBALS['query'] = "SELECT ";
                    $GLOBALS['query'] .= " `Title`, ";
                    $GLOBALS['query'] .= " `FirstName`, ";
                    $GLOBALS['query'] .= " `MiddleName`, ";
                    $GLOBALS['query'] .= " `LastName`, ";
                    $GLOBALS['query'] .= " `NameSuffix`, ";
                    $GLOBALS['query'] .= " DATE_FORMAT(`DateOfBirth`, '%M %e, %Y') AS DateOfBirth, ";
                    $GLOBALS['query'] .= " `ContactNumber`, ";
                    $GLOBALS['query'] .= " `StreetAddress`, ";
                    $GLOBALS['query'] .= " `BarangayID`, ";
                    $GLOBALS['query'] .= " `Avatar`, ";
                    $GLOBALS['query'] .= " `IsRegisteredVoter` ";
                    $GLOBALS['query'] .= "FROM ";
                    $GLOBALS['query'] .= " `" . self::$table . "` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= " `" . self::$primary_key . "`=$citizen_id";

                    $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if (has_no_db_error($purpose)) {
                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);

                            // process full_name
                            $title       = $row['Title'];      // MR.
                            $first_name  = mb_strtoupper($row['FirstName']);  // JUAN
                            $middle_name = mb_strtoupper($row['MiddleName']); // REYES
                            $last_name   = mb_strtoupper($row['LastName']);   // DELA CRUZ
                            $name_suffix = mb_strtoupper($row['NameSuffix']); // JR.

                            $full_name_1 = ''; // MR. JUAN R. DELA CRUZ JR.
                            $full_name_2 = ''; // MR. JUAN REYES DELA CRUZ JR.
                            $full_name_3 = ''; // DELA CRUZ, JUAN R., JR.
                            $full_name_4 = ''; // DELA CRUZ, JUAN REYES, JR.
                            if($title != '') {
                                $full_name_1 .= $title . ' ';
                                $full_name_2 .= $title . ' ';
                            }
                            $full_name_1 .= $first_name;
                            $full_name_2 .= $first_name;
                            $full_name_3 .= $last_name . ', ' . $first_name;
                            $full_name_4 .= $full_name_3;
                            if($middle_name != '') {
                                $middle_initial = substr($middle_name, 0, 1);
                                $full_name_1 .= ' ' . $middle_initial . '. ';
                                $full_name_2 .= ' ' . $middle_name . ' ';

                                $full_name_3 .= ' ' . $middle_initial . '.';
                                $full_name_4 .= ' ' . $middle_name;

                            }
                            $full_name_1 .= $last_name;
                            $full_name_2 .= $last_name;
                            if($name_suffix != '') {
                                $full_name_1 .= ' ' . $name_suffix;
                                $full_name_2 .= ' ' . $name_suffix;
                                $full_name_3 .= ', ' . $name_suffix;
                                $full_name_4 .= ', ' . $name_suffix;
                            }


                            // process avatar
                            $avatar = $row['Avatar'];
                            if($avatar == null || $avatar == '')
                                $avatar = '___.jpg';
                            else {
                                if(!file_exists(CITIZEN_AVATAR_DIR . $avatar)) {
                                    $avatar = '___.jpg';
                                }
                            }

                            // process address
                            require INDEX . 'php/models/PhBarangay.php';
                            $barangay = new PhBarangay($row['BarangayID'], $purpose);
                            $muncity  = $barangay->get_data('muncity');
                            $province = $muncity['province'];

                            $address_line_1   = '';
                            $barangay_address = '';
                            if ($row['StreetAddress'] != '') {
                                $address_line_1 .= $row['StreetAddress'];
                                if(strpos(strtolower($row['StreetAddress']), ' st.') < 0) {
                                    $address_line_1 .= ' St.,';
                                }
                                $address_line_1 .= ', ';
                            }
                            $address_line_1   .= $barangay->get_data('name');
                            $barangay_address .= $barangay->get_data('name') . ", ";
                            $address_line_2    = $muncity['name'] . ', ' . $province['name'];
                            $barangay_address .= $muncity['name'] . ', ' . $province['name'];

                            $leader    = (new PhCitizen(0, 'for getting default citizen'))->get_data();
                            $is_leader = false;
                            if($metadata) {
                                $GLOBALS['query']  = "SELECT ";
                                $GLOBALS['query'] .= " `leaders`.`ID`, ";
                                $GLOBALS['query'] .= " `leaders`.`CitizenID` ";
                                $GLOBALS['query'] .= "FROM ";
                                $GLOBALS['query'] .= " `leaders`, `followers` ";
                                $GLOBALS['query'] .= "WHERE ";
                                $GLOBALS['query'] .= "     `leaders`.`ID` = `followers`.`LeaderID` ";
                                $GLOBALS['query'] .= " AND `followers`.`CitizenID`=$citizen_id";
                                $result2 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                if(has_no_db_error('for getting leader of a follower')) {
                                    while ($row2 = mysqli_fetch_assoc($result2)) {
                                        $leader = (new PhCitizen($row2['CitizenID'], 'for getting leader of a follower'))->get_data();
                                        $leader['leader_id'] = $row2['ID'];
                                        break;
                                    }
                                }
                                else
                                    fin();

                                // check if citizen is also a leader
                                $GLOBALS['query'] = "SELECT `ID` FROM `leaders` WHERE `CitizenID`=$citizen_id";
                                $result3 = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                if(has_no_db_error('for checking if citizen is also a leader'))
                                    $is_leader = mysqli_num_rows($result3) > 0;
                                else
                                    fin();
                            }

                            // modify item subtitle
                            $barangay_name = $barangay->get_data('name');
                            $status = 'Registered';
                            $class  = 'success';
                            if(!$row['IsRegisteredVoter']) {
                                $status = 'Unregistered';
                                $class  = 'danger';
                            }
                            $item_subtitle = '<b class="text-' . $class . '">' . $status . '</b> ' . ($barangay_name != '' ? '&middot;' : '') . ' ' . $barangay_name;

                            $this->data = array(
                                'id'               => $citizen_id,
                                'title'            => $title,
                                'first_name'       => $first_name,
                                'middle_name'      => $middle_name,
                                'last_name'        => $last_name,
                                'name_suffix'      => $name_suffix,
                                'full_name_1'      => $full_name_1,
                                'full_name_2'      => $full_name_2,
                                'full_name_3'      => $full_name_3,
                                'full_name_4'      => $full_name_4,

                                'avatar'           => CITIZEN_AVATAR_URL.$avatar,
                                'avatar_filename'  => $row['Avatar'],

                                'address_line1'    => $address_line_1,
                                'address_line2'    => $address_line_2,
                                'full_address'     => $address_line_1 . ', ' . $address_line_2,
                                'barangay_address' => $barangay_address,

                                'street_address'   => $row['StreetAddress'],
                                'barangay'         => $barangay->get_data(),

                                'date_of_birth'    => $row['DateOfBirth'] == '' ? '' : $row['DateOfBirth'],
                                'date_of_birth_1'  => $row['DateOfBirth'] == '' ? '' : date('M. d, Y', strtotime($row['DateOfBirth'])),
                                'contact_number'   => $row['ContactNumber'],

                                'leader'              => $leader,
                                'is_leader'           => $is_leader,
                                'is_registered_voter' => $row['IsRegisteredVoter'] ? 1 : 0,
                                'item_subtitle'       => $item_subtitle
                            );
                        }
                    }
                    else
                        fin();
                }
            }


            /****************************************************************************************************
             * PhCitizen :: get_item
             *
             * Get the data to be displayed on items list
             * @access public
             *
             * @return array
             */
            public function get_item() {
                $full_name = $this->get_data('full_name_3');
                $last_name = $this->get_data('last_name');
                if(strlen($last_name) > 2) {
                    if(substr($last_name, 0, 2) == '@[')
                        $full_name = $last_name;
                }
                return $this->get_item_helper(
                    $this->get_data('id'),
                    $this->get_data('avatar'),
                    $full_name,
                    $this->get_data('item_subtitle'),
                    ''
                );
            }


            /****************************************************************************************************
             * PhCitizen :: create
             *
             * Create PhCitizen item for create.js
             * @access public static
             *
             */
            public static function create() {
                // get last primary key
                $max_primary_key  = 0;
                $GLOBALS['query'] = "SELECT `" . self::$primary_key . "` FROM `" . self::$table . "` ORDER BY `" . self::$primary_key . "` DESC LIMIT 1";
                $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                if(has_no_db_error('for getting last primary key')) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $max_primary_key = intval($row[self::$primary_key]) + 1;
                        break;
                    }
                    $lastname = '@[' . $GLOBALS['arr_admin_details']['last_name'] . '] #' . $max_primary_key;
                    $firstname = '';
                    $GLOBALS['query'] = "INSERT INTO `" . self::$table . "`(`FirstName`, `LastName`) VALUES('$firstname', '$lastname')";
                    mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if (has_no_db_error('for creating new PhCitizen')) {
                        parent::create_helper(new PhCitizen(mysqli_insert_id($GLOBALS['con']), 'for getting newly created PhCitizen'));
                    }
                }
            }


            /****************************************************************************************************
             * PhCitizen :: get_form
             *
             * Generate form for PhCitizen object
             * @access public static
             *
             * @param array $data     - the item data
             * @param bool  $for_logs - for system logs or not
             *
             * @return array
             */
            public static function get_form($data, $for_logs) {
                $arr = array(
                    // form_data[0]
                    array(
                        'class' => 'form form-group-img',
                        'rows'  => array(
                            // form_data[0]['rows'][0]
                            array(
                                array(
                                    'grid'     => 'col-md-12',
                                    'type'     => 'img_upload',
                                    'alt_text' => 'CITIZEN PHOTO',
                                    'id'       => 'img-citizen-avatar',
                                    'value'    => isset($data['avatar']) ? $data['avatar'] : '',
                                    'size'     => '148px',
                                    'dir'      => 'img/citizens',
                                    'default'  => '___.jpg',
                                    'f_type'   => 'image',
                                    'readonly' => $for_logs
                                )
                            )
                        )
                    ),

                    // form_data[3]
                    array(
                        'class' => 'form form-group-break',
                        'rows'  => array(
                            // form_data[3]['rows'][0]
                            array(
                                array(
                                    'grid'     => 'col-md-12',
                                    'type'     => 'label',
                                    'id'       => 'lbl-full-name',
                                    'value'    => 'PERSONAL INFORMATION'
                                )
                            )
                        )
                    ),

                    // form_data[4]
                    array(
                        'class' => 'form form-group-attached',
                        'rows'  => array(
                            // form_data[4]['rows'][0]
                            array(
                                /*array(
                                    'grid'     => 'col-md-2',
                                    'type'     => 'text',
                                    'id'       => 'txt-title',
                                    'label'    => 'TITLE',
                                    'value'    => isset($data['title']) ? $data['title'] : '',
                                    'class'    => 'text-uppercase',
                                    'readonly' => $for_logs
                                ),*/
                                array(
                                    'grid'     => 'col-md-3',
                                    'type'     => 'text',
                                    'id'       => 'txt-lastname',
                                    'label'    => 'LAST NAME',
                                    'value'    => isset($data['last_name']) ? $data['last_name'] : '',
                                    'class'    => 'text-uppercase',
                                    'readonly' => $for_logs
                                ),
                                array(
                                    'grid'     => 'col-md-3',
                                    'type'     => 'text',
                                    'id'       => 'txt-firstname',
                                    'label'    => 'FIRST NAME',
                                    'value'    => isset($data['first_name']) ? $data['first_name'] : '',
                                    'class'    => 'text-uppercase',
                                    'readonly' => $for_logs
                                ),
                                array(
                                    'grid'     => 'col-md-2',
                                    'type'     => 'text',
                                    'id'       => 'txt-middlename',
                                    'label'    => 'MIDDLE NAME',
                                    'value'    => isset($data['middle_name']) ? $data['middle_name'] : '',
                                    'class'    => 'text-uppercase',
                                    'readonly' => $for_logs
                                ),
                                array(
                                    'grid'     => 'col-md-1',
                                    'type'     => 'text',
                                    'id'       => 'txt-name-suffix',
                                    'label'    => 'EXT.',
                                    'value'    => isset($data['name_suffix']) ? $data['name_suffix'] : '',
                                    'class'    => 'text-uppercase',
                                    'readonly' => $for_logs
                                ),
                                array(
                                    'grid'     => 'col-md-3',
                                    'type'     => 'toggle',
                                    'id'       => 'tog-isregisteredvoter',
                                    'label'    => 'REGISTERED VOTER',
                                    'value'    => isset($data['is_registered_voter']) ? $data['is_registered_voter'] : 0,
                                    'disabled' => $for_logs
                                )
                            ),

                            array(
                                array(
                                    'grid'     => 'col-md-6',
                                    'type'     => $for_logs ? 'text' : 'date',
                                    'id'       => 'txt-birth-date',
                                    'label'    => 'BIRTH DATE',
                                    'value'    => isset($data['date_of_birth']) ? $data['date_of_birth'] : '',
                                    'end'      => date('F d, Y', strtotime("-18 year", strtotime(__init::$election_date))),
                                    'readonly' => $for_logs
                                ),
                                array(
                                    'grid'     => 'col-md-6',
                                    'type'     => 'text',
                                    'id'       => 'txt-contact-number',
                                    'label'    => 'CONTACT NUMBER',
                                    'value'    => isset($data['contact_number']) ? $data['contact_number'] : '',
                                    'class'    => 'text-uppercase',
                                    'readonly' => $for_logs
                                ),
                            )
                        )
                    ),

                    // form_data[7]
                    array(
                        'class' => 'form form-group-break',
                        'rows'  => array(
                            // form_data[5]['rows'][0]
                            array(
                                array(
                                    'grid'     => 'col-md-12',
                                    'type'     => 'label',
                                    'id'       => 'lbl-home-address',
                                    'value'    => 'ADDRESS'
                                )
                            )
                        )
                    ),

                    // form_data[8]
                    array(
                        'class' => 'form form-group-attached',
                        'rows' => array(
                            // form_data[6]['rows'][0]
                            array(
                                array(
                                    'grid'     => 'col-md-12',
                                    'type'     => 'text',
                                    'id'       => 'txt-street-address',
                                    'label'    => 'STREET ADDRESS',
                                    'value'    => isset($data['street_address']) ? $data['street_address'] : '',
                                    'class'    => 'text-uppercase',
                                    'readonly' => $for_logs
                                ),
                            ),

                            // form_data[6]['rows'][1]
                            array(
                                array(
                                    'grid'     => 'col-md-6',
                                    'type'     => 'searchbox',
                                    'role'     => 'dropdown',
                                    'model'    => 'PhBarangay',
                                    'id'       => 'srch-barangay',
                                    'ref'      => '#srch-muncity',
                                    'label'    => 'BARANGAY',
                                    'value'    => isset($data['barangay']['name']) ? $data['barangay']['name'] : '',
                                    'key'      => isset($data['barangay']['id']) ? $data['barangay']['id'] : 0,
                                    'disabled' => $for_logs
                                ),
                                array(
                                    'grid'     => 'col-md-6',
                                    'type'     => 'searchbox',
                                    'role'     => 'dropdown',
                                    'model'    => 'PhMuncity',
                                    'id'       => 'srch-muncity',
                                    'ref'      => '#srch-province',
                                    'label'    => 'CITY / MUNICIPALITY',
                                    'value'    => isset($data['barangay']['muncity']['name']) ? $data['barangay']['muncity']['name'] : '',
                                    'key'      => isset($data['barangay']['muncity']['id']) ? $data['barangay']['muncity']['id'] : 0,
                                    'disabled' => true
                                )
                            )
                        )
                    ),

                    array(
                        'class' => 'form form-group-break',
                        'rows'  => array(
                            // form_data[5]['rows'][0]
                            array(
                                array(
                                    'grid'     => 'col-md-12',
                                    'type'     => 'label',
                                    'id'       => 'lbl-leader',
                                    'value'    => $data['is_leader'] ? '<span class="text-success"><span class="fa fa-fw fa-info-circle"></span> CITIZEN IS ALREADY A LEADER</span>' : (!$for_logs ? '<span class="btn-span btn-clear-leader"><span class="fa fa-times-circle fa-fw text-danger"></span></span> LEADER' : '')
                                )
                            )
                        )
                    ),

                    array(
                        'class' => 'form form-group-attached',
                        'rows' => array(
                            array(
                                array(
                                    'grid'     => 'col-md-6',
                                    'type'     => 'searchbox',
                                    'model'    => 'Leader',
                                    'id'       => 'srch-leader',
                                    'label'    => 'LEADER',
                                    'avatar'   => isset($data['leader']['avatar']) ? $data['leader']['avatar'] : CITIZEN_AVATAR_URL.'___.jpg',
                                    'value'    => isset($data['leader']['full_name_3']) ? $data['leader']['full_name_3'] : '',
                                    'key'      => isset($data['leader']['leader_id']) ? $data['leader']['leader_id'] : '',
                                    'disabled' => $for_logs || $data['is_leader']
                                )
                            )
                        )
                    )
                );

                return $arr;
            }


            /****************************************************************************************************
             * PhCitizen :: update
             *
             * Update PhCitizen item for update.js
             * @access public static
             *
             */
            public static function update() {
                $citizen_id = intval($_POST[self::$params['update']['key']]);
                $citizen    = new PhCitizen($citizen_id, 'for update');
                $form_data = $_POST['data'];

                // get remaining data
                $avatar         = mysqli_real_escape_string($GLOBALS['con'], strip_tags(trim($form_data[0]['rows'][0]['img-citizen-avatar'])));
                $firstname      = mysqli_real_escape_string($GLOBALS['con'], strip_tags(trim($form_data[2]['rows'][0]['txt-firstname'])));
                $middle_name    = mysqli_real_escape_string($GLOBALS['con'], strip_tags(trim($form_data[2]['rows'][0]['txt-middlename'])));
                $lastname       = mysqli_real_escape_string($GLOBALS['con'], strip_tags(trim($form_data[2]['rows'][0]['txt-lastname'])));
                $suffix         = mysqli_real_escape_string($GLOBALS['con'], strip_tags(trim($form_data[2]['rows'][0]['txt-name-suffix'])));
                $is_registered  = intval($form_data[2]['rows'][0]['tog-isregisteredvoter']);
                $birthdate      = trim($form_data[2]['rows'][1]['txt-birth-date']);
                $birth_date     = $birthdate != '' ? date('Y-m-d', strtotime($birthdate)) : '0000-00-00';
                $contact_number = mysqli_real_escape_string($GLOBALS['con'], strip_tags(trim($form_data[2]['rows'][1]['txt-contact-number'])));
                $street         = mysqli_real_escape_string($GLOBALS['con'], strip_tags(trim($form_data[4]['rows'][0]['txt-street-address'])));
                $barangay_id    = intval($form_data[4]['rows'][1]['srch-barangay']);
                $leader_id      = intval($form_data[6]['rows'][0]['srch-leader']);

                // check birthdate
                if($birth_date != '0000-00-00') {
                    $voting_birth_date = date('Y-m-d', strtotime("-18 year", strtotime(__init::$election_date)));
                    if(strtotime($birth_date) > strtotime($voting_birth_date)) {
                        $GLOBALS['response']['success']['message'] = 'INVALID BIRTH DATE';
                        $GLOBALS['response']['success']['sub_message'] = 'This citizen is not allowed to vote on <b class="text-success">' . __init::$election_date . ' </b>. The minimum voting birth date is <b>' . date('F d, Y', strtotime($voting_birth_date)) . '</b>.';
                        fin();
                    }
                }

                // check name
                $GLOBALS['query']  = "SELECT ";
                $GLOBALS['query'] .= " `" . self::$primary_key . "` ";
                $GLOBALS['query'] .= "FROM ";
                $GLOBALS['query'] .= " `" . self::$table . "` ";
                $GLOBALS['query'] .= "WHERE ";
                $GLOBALS['query'] .= "     `" . self::$primary_key . "`!=$citizen_id ";
                $GLOBALS['query'] .= " AND `FirstName`='$firstname' ";
                $GLOBALS['query'] .= " AND `LastName`='$lastname' ";
//                $GLOBALS['query'] .= " AND ";
//                $GLOBALS['query'] .= " ( ";
//                $GLOBALS['query'] .= "   ( ";
//                $GLOBALS['query'] .= "         `FirstName`='$firstname' ";
//                $GLOBALS['query'] .= "     AND `LastName`='$lastname' ";
//                $GLOBALS['query'] .= "   ) ";
//                $GLOBALS['query'] .= " OR ";
//                $GLOBALS['query'] .= "   ( ";
//                $GLOBALS['query'] .= "         REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(`FirstName`)), 'A', ''), 'E', ''), 'I', ''), 'O', ''), 'U', '') = '" . str_replace('U', '', str_replace('O', '', str_replace('I', '', str_replace('E', '', str_replace('A', '', strtoupper(trim($firstname))))))) . "'";
//                $GLOBALS['query'] .= "     AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(`LastName`)), 'A', ''), 'E', ''), 'I', ''), 'O', ''), 'U', '') = '" . str_replace('U', '', str_replace('O', '', str_replace('I', '', str_replace('E', '', str_replace('A', '', strtoupper(trim($lastname))))))) . "'";
//                $GLOBALS['query'] .= "   ) ";
//                $GLOBALS['query'] .= " ) ";
                $GLOBALS['query'] .= " AND TRIM(REPLACE(`NameSuffix`, '.', '')) = TRIM(REPLACE('$suffix', '.', '')) ";
                $GLOBALS['query'] .= " AND ( ";
                $GLOBALS['query'] .= "   ('$middle_name'  = '' AND `MiddleName` = '') ";
                $GLOBALS['query'] .= "   OR ";
                $GLOBALS['query'] .= "   ('$middle_name'  = '' AND `MiddleName` != '') ";
                $GLOBALS['query'] .= "   OR ";
                $GLOBALS['query'] .= "   ('$middle_name' != '' AND `MiddleName` = '') ";
                $GLOBALS['query'] .= "   OR ";
                $GLOBALS['query'] .= "   ('$middle_name' != '' AND `MiddleName` = '$middle_name') ";
                $GLOBALS['query'] .= "   OR ";
                $GLOBALS['query'] .= "   ( CHAR_LENGTH(TRIM(REPLACE('$middle_name', '.', ''))) = 1 AND MID(`MiddleName`, 1, 1) = '" . substr($middle_name, 0, 1) . "') ";
                $GLOBALS['query'] .= "   OR ";
                $GLOBALS['query'] .= "   ( CHAR_LENGTH(TRIM(REPLACE(`MiddleName`, '.', ''))) = 1 AND MID('$middle_name', 1, 1) = MID(`MiddleName`, 1, 1)) ";
                $GLOBALS['query'] .= " ) ";
                $GLOBALS['query'] .= " AND ( ";
                $GLOBALS['query'] .= "   ('$birth_date'  = '0000-00-00' AND `DateOfBirth` = '0000-00-00') ";
                $GLOBALS['query'] .= "   OR ";
                $GLOBALS['query'] .= "   ('$birth_date'  = '0000-00-00' AND `DateOfBirth` != '0000-00-00') ";
                $GLOBALS['query'] .= "   OR ";
                $GLOBALS['query'] .= "   ('$birth_date' != '0000-00-00' AND `DateOfBirth` = '0000-00-00') ";
                $GLOBALS['query'] .= "   OR ";
                $GLOBALS['query'] .= "   ('$birth_date' != '0000-00-00' AND `DateOfBirth` = '$birth_date') ";
                $GLOBALS['query'] .= " ) ";
                $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                if(has_no_db_error('for checking if citizen name already exists')) {
                    if(mysqli_num_rows($result) > 0) {
                        $GLOBALS['response']['success']['message'] = 'ALREADY EXISTS!';
                        $GLOBALS['response']['success']['sub_message'] = 'The new citizen data you are about to add already exists in the database<!--.<br><br><b>REDUNDANCY CHECK BASIS:</b><ul><li>LAST NAME</li><li>FIRST NAME</li><li>NAME EXT. / SUFFIX</li><li>BIRTH DATE</li></ul>-->';
                    }
                    else {
                        if($citizen->get_data('is_leader') && $leader_id > 0) {
                            $GLOBALS['response']['success']['message'] = 'CONFLICT!';
                            $GLOBALS['response']['success']['sub_message'] = 'The selected citizen is also a LEADER. Therefore, he/she can\'t be under other leader.';
                            fin();
                        }

                        // delete previous leader
                        $GLOBALS['query'] = "DELETE FROM `followers` WHERE `CitizenID`=$citizen_id";
                        mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                        if(has_no_db_error('for deleting previous leader of follower')) {
                            // insert new leader
                            if($leader_id > 0) {
                                $GLOBALS['query'] = "SELECT `CitizenID` FROM `leaders` WHERE `ID`=$leader_id";
                                $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                if(has_no_db_error('for getting citizen info of chosen leader')) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        if($row['CitizenID'] == $citizen_id) {
                                            $GLOBALS['response']['success']['message'] = 'INVALID LEADER';
                                            $GLOBALS['response']['success']['sub_message'] = 'You cannot set LEADER to the same citizen.';
                                            fin();
                                        }
                                        break;
                                    }
                                    $GLOBALS['query'] = "INSERT INTO ";
                                    $GLOBALS['query'] .= " `followers`(`LeaderID`, `CitizenID`) ";
                                    $GLOBALS['query'] .= "VALUES (";
                                    $GLOBALS['query'] .= " $leader_id, $citizen_id";
                                    $GLOBALS['query'] .= ")";
                                    mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                                    if(!has_no_db_error('for inserting new leader of follower'))
                                        fin();
                                }
                                else
                                    fin();
                            }

                            // update PhCitizen data
                            $GLOBALS['query']  = "UPDATE ";
                            $GLOBALS['query'] .= " `" . self::$table . "` ";
                            $GLOBALS['query'] .= "SET ";
                            $GLOBALS['query'] .= " `FirstName`='$firstname', ";
                            $GLOBALS['query'] .= " `MiddleName`='$middle_name', ";
                            $GLOBALS['query'] .= " `LastName`='$lastname', ";
                            $GLOBALS['query'] .= " `NameSuffix`='$suffix', ";
                            $GLOBALS['query'] .= " `DateOfBirth`='$birth_date', ";
                            $GLOBALS['query'] .= " `ContactNumber`='$contact_number', ";
                            $GLOBALS['query'] .= " `StreetAddress`='$street', ";
                            $GLOBALS['query'] .= " `BarangayID`=$barangay_id, ";
                            $GLOBALS['query'] .= " `Avatar`='$avatar', ";
                            $GLOBALS['query'] .= " `IsRegisteredVoter`=$is_registered ";
                            $GLOBALS['query'] .= "WHERE ";
                            $GLOBALS['query'] .= " `" . self::$primary_key . "`=$citizen_id";
                            mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                            if (has_no_db_error('for updating PhCitizen data')) {
                                parent::update_helper(new PhCitizen($citizen_id, 'for getting newly updated PhCitizen'), $citizen->get_item());
                            }
                        }
                    }
                }
            }


            /****************************************************************************************************
             * PhCitizen :: delete
             *
             * Delete PhCitizen item for delete.js
             * @access public
             *
             */
            public function delete() {
                require INDEX . 'php/models/UserAccount.php';
                if(UserAccount::exists($this->get_data('id'))) {
                    $GLOBALS['response']['success']['message']     = '<span class="text-danger">CANNOT DELETE ' . self::$tab_singular . '</span>';
                    $GLOBALS['response']['success']['sub_message'] =  '<b>' . $this->get_data('full_name_3') . '</b> is registered as a user of this system.';
                }
                else
                    parent::delete_helper($this);
            }


            /****************************************************************************************************
             * PhCitizen :: search
             *
             * Search through citizen records
             * @access public static
             *
             * @param string $q - the search query
             *
             */
            public static function search($q) {
                $q = mysqli_real_escape_string($GLOBALS['con'], $q);

                require INDEX . 'php/models/PhBarangay.php';
                $GLOBALS['query']  = "SELECT ";
                $GLOBALS['query'] .= " `" . self::$table . "`.`" . self::$primary_key . "` ";
                $GLOBALS['query'] .= "FROM ";
                $GLOBALS['query'] .= " `" . self::$table . "`, ";
                $GLOBALS['query'] .= " `" . PhBarangay::$table . "` ";
                $GLOBALS['query'] .= "WHERE ";
                $GLOBALS['query'] .= " `" . self::$table . "`.`" . PhBarangay::$foreign_key . "`=`" . PhBarangay::$table . "`.`" . PhBarangay::$primary_key . "` ";
                $GLOBALS['query'] .= " AND ";
                $GLOBALS['query'] .= " ( ";
                $GLOBALS['query'] .= "      `" . self::$table . "`.`" . self::$primary_key . "`=" . intval($q) . " ";
                $GLOBALS['query'] .= "   OR `" . self::$table . "`.`FirstName` LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR `" . self::$table . "`.`MiddleName` LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR `" . self::$table . "`.`LastName` LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . self::$table . "`.`FirstName`, ' ', `" . self::$table . "`.`LastName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . self::$table . "`.`FirstName`, ' ', `" . self::$table . "`.`MiddleName`, ' ', `" . self::$table . "`.`LastName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . self::$table . "`.`FirstName`, ' ', MID(`" . self::$table . "`.`MiddleName`, 1, 1), '. ', `" . self::$table . "`.`LastName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . self::$table . "`.`LastName`, ' ', `" . self::$table . "`.`FirstName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . self::$table . "`.`LastName`, ', ', `" . self::$table . "`.`FirstName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . self::$table . "`.`LastName`, ', ', `" . self::$table . "`.`FirstName`, ' ', `" . self::$table . "`.`MiddleName`) LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR CONCAT(`" . self::$table . "`.`LastName`, ', ', `" . self::$table . "`.`FirstName`, ' ', MID(`" . self::$table . "`.`MiddleName`, 1, 1), '.') LIKE '%$q%' ";
                $GLOBALS['query'] .= "   OR `" . PhBarangay::$table . "`.`Description` LIKE '%$q%' ";
                $GLOBALS['query'] .= " ) ";
                $GLOBALS['query'] .= "ORDER BY ";
                $GLOBALS['query'] .= " `" . self::$table . "`.`LastName`, `" . self::$table . "`.`FirstName`, `" . self::$table . "`.`MiddleName` ";
                parent::search_helper(get_class());
            }
        }

        if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
            $object = new PhCitizen(0, 'for class usage');
            require '__fin.php';
        }
    }

?>