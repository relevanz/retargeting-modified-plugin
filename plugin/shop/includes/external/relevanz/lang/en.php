<?php
/* -----------------------------------------------------------
Copyright (c) 2020 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
if (defined('MODULE_RELEVANZ_TEXT_TITLE')) {
    return;
}
define('MODULE_RELEVANZ_TEXT_TITLE', 'releva.nz');
define('MODULE_RELEVANZ_TEXT_DESCRIPTION', 'releva.nz - Technology for personalized marketing');
define('MODULE_RELEVANZSORT_ORDER', 9999);

define('RELEVANZ_TAB_STATS', 'Statistics');
define('RELEVANZ_TAB_CONF', 'Configuration');

define('RELEVANZ_LABEL_APIKEY', 'Your releva.nz API Key');
define('RELEVANZ_LABEL_APIKEY_TOOLTIP', 'If you are already registered and have received our key, please enter it in this field.');
define('RELEVANZ_LABEL_APIKEY_TOOLTIP_LINK_HREF', 'https://releva.nz');
define('RELEVANZ_LABEL_APIKEY_TOOLTIP_LINK_TEXT', 'Not registered yet? Get it now!');

define('RELEVANZ_LABEL_CUSTOMERID', 'Your Customer ID');

define('RELEVANZ_LABEL_EXPORTURL', 'Export-URL');
define('RELEVANZ_LABEL_EXPORTURL_TOOLTIP', 'Please submit this URL to the releva.nz customer service.');

define('RELEVANZ_LABEL_ADDITIONAL_HTML', 'Additional HTML');
define('RELEVANZ_LABEL_ADDITIONAL_HTML_TOOLTIP', 'Optional: HTML for integrating a consent manager (e.g. Usercentrics, consentmanager). If empty, the tracking pixel always loads.');

define('RELEVANZ_MSG_1554076968', 'Configuration saved.');
define('RELEVANZ_MSG_1553935480', 'The connection to the API server cannot be established. Please try again at a later time.');
define('RELEVANZ_MSG_1553935569', 'The API key cannot be verified. Please make sure that the API key is correct.');
define('RELEVANZ_MSG_1553935786', 'The API key cannot be verified. Please make sure that the API key is correct.');
