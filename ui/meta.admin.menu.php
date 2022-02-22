<?php

    $arr_menu_items = array(
        array(
            'name'        => 'HOME',
            'href'        => 'home',
            'status'      => '',
            'default-tab' => '1',
            'icon'        => 'fa fa-home fa-fw',
            'tabs'        => array(
                array(
                    'tab_icon'      => 'fa fa-th-large',
                    'tab_text'      => 'DASHBOARD',
                    'desc'          => 'View the system dashboard.',
                    'model'         => 'Dashboard',
                    'controls'      => array(),
                    'for_devs_only' => false
                )
            )
        ),
        array(
            'name'        => 'CITIZENS',
            'href'        => 'citizens',
            'status'      => '',
            'default-tab' => '1',
            'icon'        => 'fa fa-user-friends fa-fw',
            'tabs'        => array(
                array(
                    'tab_icon'      => 'fa fa-user-circle fa-fw',
                    'tab_text'      => 'CITIZENS',
                    'desc'          => 'View citizens.',
                    'model'         => 'PhCitizen',
                    'controls'      => array('list', 'create', 'select', 'update', 'delete', 'search'),
                    'for_devs_only' => false
                )
            )
        ),
        array(
            'name'        => 'LEADERS',
            'href'        => 'leaders',
            'status'      => '',
            'default-tab' => '1',
            'icon'        => 'fa fa-user-tag fa-fw',
            'tabs'        => array(
                array(
                    'tab_icon'      => 'fa fa-user-tag fa-fw',
                    'tab_text'      => 'LEADERS',
                    'desc'          => 'View leaders.',
                    'model'         => 'Leader',
                    'controls'      => array('list', 'create', 'select', 'update', 'delete', 'search', 'print'),
                    'for_devs_only' => false
                ),
            )
        ),
        array(
            'name'        => 'REPORTS',
            'href'        => 'reports',
            'status'      => '',
            'default-tab' => '1',
            'icon'        => 'fa fa-clone fa-fw',
            'tabs'        => array(
                array(
                    'tab_icon'      => 'fa fa-chalkboard-teacher fa-fw',
                    'tab_text'      => 'BRGY. LEADERS',
                    'desc'          => 'View leaders per barangay',
                    'model'         => 'BarangayLeaders',
                    'controls'      => array('list', 'select', 'search', 'print'),
                    'for_devs_only' => false
                ),
                array(
                    'tab_icon'      => 'fa fa-users fa-fw',
                    'tab_text'      => 'BRGY. CITIZENS',
                    'desc'          => 'View citizens per barangay',
                    'model'         => 'BarangayCitizens',
                    'controls'      => array('list', 'select', 'search', 'print'),
                    'for_devs_only' => false
                ),
                array(
                    'tab_icon'      => 'fa fa-stream fa-fw',
                    'tab_text'      => 'OTHER REPORTS',
                    'desc'          => 'View other reports.',
                    'model'         => 'Report',
                    'controls'      => array('list', 'select', 'search'),
                    'for_devs_only' => false
                ),
            )
        ),
        array(
            'name'        => 'ACCOUNTS',
            'href'        => 'accounts',
            'status'      => '',
            'default-tab' => '1',
            'icon'        => 'fa fa-key fa-fw',
            'tabs'        => array(
                array(
                    'tab_icon'      => 'fa fa-tags fa-fw',
                    'tab_text'      => 'USER TYPES',
                    'desc'          => 'Manage settings for user types.',
                    'model'         => 'UserType',
                    'controls'      => array('list', 'create', 'select', 'update', 'delete', 'search'),
                    'for_devs_only' => false
                ),
                array(
                    'tab_icon'      => 'fa fa-user fa-fw',
                    'tab_text'      => 'USER ACCOUNTS',
                    'desc'          => 'Manage settings for user accounts.',
                    'model'         => 'UserAccount',
                    'controls'      => array('list', 'create', 'select', 'update', 'delete', 'search'),
                    'for_devs_only' => false
                ),
            )
        ),
        array(
            'name'        => 'SYSTEM LOGS',
            'href'        => 'systemlogs',
            'status'      => '',
            'default-tab' => '1',
            'icon'        => 'fa fa-desktop fa-fw',
            'tabs'        => array(
                array (
                    'tab_icon'      => 'fa fa-desktop fa-fw',
                    'tab_text'      => 'ACTIVITY LOGS',
                    'desc'          => 'Access activity logs.',
                    'model'         => 'ActivityLog',
                    'controls'      => array('list', 'select', 'search'),
                    'for_devs_only' => false
                )
            )
        )
    );

?>