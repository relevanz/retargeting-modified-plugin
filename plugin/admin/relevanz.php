<?php
/* -----------------------------------------------------------
Copyright (c) 2020 Releva GmbH - https://www.releva.nz
Released under the GNU General Public License (Version 2)
[http://www.gnu.org/licenses/gpl-2.0.html]
--------------------------------------------------------------
*/
require_once('includes/application_top.php');
require_once(DIR_FS_EXTERNAL.'/relevanz/admin/main.php');
if (isset($relevanz_view)) {
    $view = $relevanz_view;
    $data = $relevanz_data;
    require(DIR_FS_EXTERNAL.'/relevanz/admin/views/admin-top.php');
    require(DIR_FS_EXTERNAL.'/relevanz/admin/views/'.$view.'.php');
    require(DIR_FS_EXTERNAL.'/relevanz/admin/views/admin-bottom.php');
}
