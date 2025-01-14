<?php
/* -----------------------------------------------------------
Copyright (c) 2020 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Modified;

use DateTime;

use Releva\Retargeting\Base\ConfigurationInterface;
use Releva\Retargeting\Base\Credentials;

class Configuration implements ConfigurationInterface
{
    const PLUGIN_VERSION = '1.0.2';
    const CONF_PREFIX = 'configuration/';
    const CONF_APIKEY = 'RELEVANZ_APIKEY';
    const CONF_USERID = 'RELEVANZ_USERID';

    protected static function read($key) {
        $query = xtc_db_query('
            SELECT * FROM '.TABLE_CONFIGURATION.'
             WHERE configuration_key = "'.xtc_db_input($key).'"
        ');
        if (xtc_db_num_rows($query) === 1) {
            $row = xtc_db_fetch_array($query);
            return (isset($row['configuration_value']) && !empty($row['configuration_value']))
                ? $row['configuration_value']
                : null;
        }
        return null;
    }

    protected static function write($key, $value) {
        $query = xtc_db_query('
            SELECT * FROM '.TABLE_CONFIGURATION.'
             WHERE configuration_key = "'.xtc_db_input($key).'"
        ');
        $now = new \DateTime();
        $nowdt = $now->format('Y-m-d H:i:s');

        if (xtc_db_num_rows($query) === 1) {
            // update
            xtc_db_query('
                 UPDATE '.TABLE_CONFIGURATION.'
                    SET configuration_value = \''.xtc_db_input($value).'\',
                        last_modified = \''.$nowdt.'\'
                  WHERE configuration_key = \''.xtc_db_input($key).'\'
                  LIMIT 1
            ');
        } else {
            // insert
            xtc_db_query('
                 INSERT INTO `'.TABLE_CONFIGURATION.'` (`configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`)
                 VALUES (\''.xtc_db_input($key).'\', \''.xtc_db_input($value).'\', 1000, 0, \''.$nowdt.'\', \''.$nowdt.'\')
            ');
        }
    }

    public static function getCredentials() {
        return new Credentials(
            (string)self::read(self::CONF_APIKEY),
            (int)self::read(self::CONF_USERID)
        );
    }

    public static function updateCredentials(Credentials $credentials) {
        self::write(self::CONF_APIKEY, $credentials->getApiKey());
        self::write(self::CONF_USERID, $credentials->getUserId());
    }

    public static function getPluginVersion() {
        return self::PLUGIN_VERSION;
    }

}
