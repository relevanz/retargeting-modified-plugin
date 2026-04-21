<?php
/* -----------------------------------------------------------
Copyright (c) 2020 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
use Releva\Retargeting\Base\RelevanzApi;
use Releva\Retargeting\Modified\Configuration as ShopConfiguration;

class RelevanzHeaderBodyHook extends RelevanzHookAbstract {

    public function run() {
        global $category;
        global $current_category_id;
        global $product;
        global $orders;

        if (!$this->init()) {
            return;
        }

        $mode = '';
        $modeid = 0;
        $customerId = 0;
        if((int)$_SESSION['customer_id'] > 0) {
            $customerId = (int)$_SESSION['customer_id'];
        }

        // Detect the mode
        $baseScriptFilename = basename($_SERVER['SCRIPT_FILENAME']);
        if ($baseScriptFilename == 'index.php') {
            $mode = 'index';
            if (is_array($category) && ((int)$current_category_id > 0)) {
                $mode = 'category';
                $modeid = (int)$current_category_id;
            }

        } else if (is_object($product) && isset($product->pID) && ((int)$product->pID > 0)) {
            $mode = 'product';
            $modeid = (int)$product->pID;

        } else if (is_array($orders) && isset($orders['orders_id']) && ((int)$orders['orders_id'] > 0)) {
            $mode = 'ordercomplete';
            $modeid = (int)$orders['orders_id'];

        } else if (($baseScriptFilename == FILENAME_CHECKOUT_SUCCESS) && ((int)$_SESSION['customer_id'] > 0)) {
            // Fallback in case the global $oders variable is nulled by some other script.
            $orders_query = xtc_db_query('
                SELECT orders_id
                  FROM '.TABLE_ORDERS.'
                 wHERE customers_id = "'.(int)$_SESSION['customer_id'].'"
                       AND unix_timestamp(date_purchased) > (unix_timestamp(now()) - "'.(int)SESSION_LIFE_CUSTOMERS.'")
              ORDER BY orders_id DESC
                 LIMIT 1
            ');
            if ((xtc_db_num_rows($orders_query) == 1)
                && ($orders = xtc_db_fetch_array($orders_query))
                && ((int)$orders['orders_id'] > 0)
            ) {
                $mode = 'ordercomplete';
                $modeid = (int)$orders['orders_id'];
            }
        }

        $trackerUrl = '';
        $trackerUrlBase = RelevanzApi::RELEVANZ_TRACKER_URL.'?cid='.$this->credentials->getUserId().'&t=d&';
        switch ($mode) {
            case 'category': {
                $trackerUrl = $trackerUrlBase.'action=c&id='.$modeid.'&custid='.$customerId;
                break;
            }
            case 'product': {
                $trackerUrl = $trackerUrlBase.'action=p&id='.$modeid.'&custid='.$customerId;
                break;
            }
            case 'ordercomplete': {
                $orderTotal = null;
                // Get the total amount of the order based on the correct orders total class.
                $q = xtc_db_query('SELECT `value` FROM `'.TABLE_ORDERS_TOTAL.'` WHERE `orders_id` = '.$modeid.' AND `class`=\'ot_total\' LIMIT 1');
                $row = xtc_db_fetch_array($q);
                if (isset($row['value'])) {
                    $orderTotal = (float)$row['value'];
                    // reduce by tax (sum all tax rows to handle mixed tax rates, e.g. 7% + 19%)
                    $q = xtc_db_query('SELECT SUM(`value`) as `value` FROM `'.TABLE_ORDERS_TOTAL.'` WHERE `orders_id` = '.$modeid.' AND `class`=\'ot_tax\'');
                    $row = xtc_db_fetch_array($q);
                    if (isset($row['value'])) {
                        $orderTotal -= (float)$row['value'];
                    }
                    // reduce by shipping
                    $q = xtc_db_query('SELECT `value` FROM `'.TABLE_ORDERS_TOTAL.'` WHERE `orders_id` = '.$modeid.' AND `class`=\'ot_shipping\' LIMIT 1');
                    $row = xtc_db_fetch_array($q);
                    if (isset($row['value'])) {
                        $orderTotal -= (float)$row['value'];
                    }                    
                }
                // If ot_total does not exist, assume it is the highest value in the orders_total table for this order.
                if ($orderTotal === null) {
                    $q = xtc_db_query('SELECT max(`value`) as `value` FROM `'.TABLE_ORDERS_TOTAL.'` WHERE `orders_id` = '.$modeid.'');
                    $row = xtc_db_fetch_array($q);
                    if (isset($row['value'])) {
                        $orderTotal = (float)$row['value'];
                    }
                }

                $productIds = [];
                $q = xtc_db_query('SELECT `products_id` FROM `'.TABLE_ORDERS_PRODUCTS.'` WHERE `orders_id` = '.$modeid.'');
                while ($row = xtc_db_fetch_array($q)) {
                    if (isset($row['products_id'])) {
                        $productIds[] = (int)$row['products_id'];
                    }
                }

                if (($orderTotal !== null) && !empty($productIds)) {
                    $trackerUrl = RelevanzApi::RELEVANZ_CONV_URL.'?cid='.$this->credentials->getUserId()
                        .'&orderId='.$modeid.'&amount='.$orderTotal
                        .'&products='.implode(',', $productIds).'&custid='.$customerId;
                }
                break;
            }
            default: {
                $trackerUrl = $trackerUrlBase.'action=s'.'&custid='.$customerId;;
                break;
            }
        }
        if (empty($trackerUrl)) {
            return;
        }

        $additionalHtml = ShopConfiguration::getAdditionalHtml();
        $forcePixelScript = ($additionalHtml !== null)
            ? $additionalHtml . "\n"
            : '<script>relevanzRetargetingForcePixel = true;</script>' . "\n";

        $trackerCode = $forcePixelScript
            . '            <!-- Start of releva.nz tracking code -->' . "\n"
            . '            <script type="text/javascript" src="' . htmlspecialchars($trackerUrl) . '" async="true"></script>' . "\n"
            . '            <!-- End of releva.nz tracking code -->' . "\n";

        echo $trackerCode;
    }

}
