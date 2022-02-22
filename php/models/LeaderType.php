<?php
    require '__init.php';

    if(!class_exists('LeaderType')) {
        class LeaderType extends __init {

            // class variables
            public static $tab_singular         = 'TEMPLATE';
            public static $table                = 'leader_types';
            public static $primary_key          = 'ID';
            public static $primary_zero_allowed = false;
            public static $list_order           = 'ORDER BY `ID`';

            public static $params = array(
                'list'   => array(
                    'key'     => 'list_leadertype',
                    'enabled' => true
                ),
                'search' => array(
                    'key'     => 'search_leadertype',
                    'enabled' => true
                )
            );


            /****************************************************************************************************
             * LeaderType :: CONSTRUCTOR
             *
             * Initialize the LeaderType object.
             * @access public
             *
             * @param int $leader_type_id - the LeaderType id
             * @param string $purpose - the usage of this operation
             * @param bool $metadata
             */
            public function __construct($leader_type_id = 0, $purpose = '', $metadata = true) {
                $this->data = array(
                    'id' => 0,
                );

                $leader_type_id = intval($leader_type_id);
                if(($leader_type_id > 0 || self::$primary_zero_allowed) && $purpose != 'for class usage') {
                    $purpose = 'getting LeaderType info' . cascade_purpose($purpose);
                    $GLOBALS['query']  = "SELECT ";
                    $GLOBALS['query'] .= " `Title`, ";
                    $GLOBALS['query'] .= " `Description`, ";
                    $GLOBALS['query'] .= "  DATE_FORMAT(`CreatedAt`, '%M %e, %Y &middot; %h:%i %p') AS CreatedAt, ";
                    $GLOBALS['query'] .= "  DATE_FORMAT(`UpdatedAt`, '%M %e, %Y &middot; %h:%i %p') AS UpdatedAt ";
                    $GLOBALS['query'] .= "FROM ";
                    $GLOBALS['query'] .= " `" . self::$table . "` ";
                    $GLOBALS['query'] .= "WHERE ";
                    $GLOBALS['query'] .= " `" . self::$primary_key . "`=$leader_type_id";
                    $result = mysqli_query($GLOBALS['con'], $GLOBALS['query']);
                    if (has_no_db_error($purpose)) {
                        if (mysqli_num_rows($result) > 0) {
                            $row = mysqli_fetch_assoc($result);

                            $this->data = array(
                                'id'           => $leader_type_id,
                                'title'        => $row['Title'],
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
             * LeaderType :: get_item
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
                    $this->get_data('title'),
                    $this->get_data('description'),
                    ''
                );
            }


            /****************************************************************************************************
             * LeaderType :: search
             *
             * Search through LeaderType records
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
            $object = new LeaderType(0, 'for class usage');
            require '__fin.php';
        }
    }
?>