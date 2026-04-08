<?php
/* -----------------------------------------------------------
Copyright (c) 2020 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/

if (!isset($current_page)) {
    global $current_page;
}

if (!isset($messageStack)) {
    global $messageStack;
}

global $admin_access;

require_once (DIR_WS_INCLUDES.'head.php');
?>
    <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_MOD_RELEVANZ.'admin/assets/relevanz.css'; ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_MOD_RELEVANZ.'admin/assets/'.$view.'.css'; ?>">
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<table class="tableBody module-relevanz"><tbody>
    <tr>
    <?php //left_navigation
        if (USE_ADMIN_TOP_MENU == 'false') {
            echo '
        <td class="columnLeft2">
            <!-- left_navigation //-->';
            require_once(DIR_WS_INCLUDES . 'column_left.php');
            echo '
            <!-- left_navigation eof //-->
        </td>';
        }
    ?>
    <!-- body_text //-->
    <td class="boxCenter">
        <div class="ui-tabs">
            <ul><?php
                $classStats = [];
                $classConf  = [];
                if (!$data['credentials']->isComplete()) {
                    $classStats[] = 'inactive';
                } else if ($view === 'statistics') {
                    $classStats[] = 'selected';
                }
                if ($view === 'configuration') {
                    $classConf[] = 'selected';
                }

                echo '
                <li class="'.implode(' ', $classStats).'">
                    <a href="'.xtc_href_link('relevanz.php', 'tab=stats', 'NONSSL').'">'.RELEVANZ_TAB_STATS.'</a>
                </li>
                <li class="'.implode(' ', $classConf).'">
                    <a href="'.xtc_href_link('relevanz.php', 'tab=conf', 'NONSSL').'">'.RELEVANZ_TAB_CONF.'</a>
                </li>
                ';
            ?></ul>
        </div>
        <div id="rz-content">
<?php
if (isset($data['messages']) && is_array($data['messages'])) {
    foreach ($data['messages'] as $message) {
        echo '
            <div class="alert alert-'.$message['type'].'">
                <span>'.constant('RELEVANZ_MSG_'.$message['code']).'</span>
            </div>';
    }
}
