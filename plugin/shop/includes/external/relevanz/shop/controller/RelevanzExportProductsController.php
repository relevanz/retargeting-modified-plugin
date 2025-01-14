<?php
/* -----------------------------------------------------------
Copyright (c) 2020 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Base\Export\Item\ProductExportItem;
use Releva\Retargeting\Base\Export\ProductCsvExporter;
use Releva\Retargeting\Base\Export\ProductJsonExporter;
use Releva\Retargeting\Base\HttpResponse;
use Releva\Retargeting\Modified\ShopInfo;

/**
 * Class RelevanzExportController
 *
 * This controller exports the shops products for the releva.nz service.
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class RelevanzExportProductsController
{
    const ITEMS_PER_PAGE = 2500;

    protected function getMainLang() {
        $result = xtc_db_query('
            SELECT configuration_value FROM `'.TABLE_CONFIGURATION.'`
             WHERE configuration_key = \'DEFAULT_LANGUAGE\'
        ');
        if (xtc_db_num_rows($result, true) === 0) {
            return isset($_GET['lang']) ? $_GET['lang'] : 'de';
        }
        $row = xtc_db_fetch_array($result, true);
        return $row['configuration_value'];
    }

    protected function getProductQuery($lang) {
        return '
            SELECT SQL_CALC_FOUND_ROWS
                   pr.products_id as `id`, pd.products_name as `name`,
                   pd.products_short_description as `shortDescription`,
                   pd.products_description as `longDescription`,
                   pr.products_price as `price`, sp.specials_new_products_price as specials_price,
                   tr.tax_rate as taxRate,
                   pr.products_image as `image`
              FROM '.TABLE_PRODUCTS.' pr
         LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pr.products_id = pd.products_id
         LEFT JOIN '.TABLE_TAX_RATES.' tr ON pr.products_tax_class_id = tr.tax_class_id
         LEFT JOIN '.TABLE_LANGUAGES.' ln ON pd.language_id = ln.languages_id
         LEFT JOIN '.TABLE_SPECIALS.' sp ON pr.products_id = sp.products_id AND sp.status = "1"
             WHERE ln.code = "'.$lang.'" AND products_status = "1"
          GROUP BY pr.products_id
        ';
    }

    protected function getCategoryIdsByProductId($pid) {
        $ids = [];

        $result = xtc_db_query('
            SELECT categories_id FROM `'.TABLE_PRODUCTS_TO_CATEGORIES.'`
             WHERE products_id = '.$pid.'
        ');
        while ($row = xtc_db_fetch_array($result, true)) {
            $ids[] = $row['categories_id'];
        }
        return $ids;
    }

    protected function productImageUrl($image) {
        $imageUrl = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_INFO_IMAGES . $image;

        if (file_exists(DIR_WS_ORIGINAL_IMAGES . $image)) {
            $imageUrl = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_ORIGINAL_IMAGES . $image;
        }
        return $imageUrl;
    }

    public function actionDefault() {
        $lang = $this->getMainLang();
        if (empty($lang)) {
            throw new RelevanzException('Unable to get default language.', 1554160909);
        }

        $query = $this->getProductQuery($lang);

        if (isset($_GET['page']) && (($page = (int)$_GET['page']) > 0)) {
            $query .= 'LIMIT '.(($page - 1) * self::ITEMS_PER_PAGE).', '.self::ITEMS_PER_PAGE;
        }

        $productResult = xtc_db_query($query);
        if (xtc_db_num_rows($productResult, true) === 0) {
            return new HttpResponse('No products found.', [
                'HTTP/1.0 404 Not Found'
            ]);
        }

        $q = xtc_db_query('SELECT FOUND_ROWS() AS total');
        $r = xtc_db_fetch_array($q, true);
        $pCount = isset($r['total']) ? $r['total'] : -1;

        $format = isset($_GET['format']) ? $_GET['format'] : '';
        $exporter = null;
        switch ($format) {
            case 'json': {
                $exporter = new ProductJsonExporter();
                break;
            }
            default: {
                $exporter = new ProductCsvExporter();
                break;
            }
        }

        while ($product = xtc_db_fetch_array($productResult, true)) {
            $price = round($product['price'] + $product['price'] / 100 * $product['taxRate'], 2);
            $priceOffer = ($product['specials_price'] === null)
                ? $price
                : round($product['specials_price'] + $product['specials_price'] / 100 * $product['taxRate'], 2);

            $exporter->addItem(new ProductExportItem(
                (int)$product['id'],
                $this->getCategoryIdsByProductId($product['id']),
                $product['name'],
                $product['shortDescription'],
                preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $product['longDescription']),
                $price,
                $priceOffer,
                HTTP_SERVER . DIR_WS_CATALOG . 'product_info.php?info=p' . xtc_get_prid($product['id']),
                $this->productImageUrl($product['image'])
            ));

        }

        $headers = [];
        foreach ($exporter->getHttpHeaders() as $hkey => $hval) {
            $headers[] = $hkey.': '.$hval;
        }
        $headers[] = 'Cache-Control: must-revalidate';
        $headers[] = 'X-Relevanz-Product-Count: '.$pCount;
        #$headers[] = 'Content-Type: text/plain; charset="utf-8"'; $headers[] = 'Content-Disposition: inline';

        return new HttpResponse($exporter->getContents(), $headers);
    }

    public static function discover() {
        return [
            'url' => ShopInfo::getUrlProductExport(),
            'parameters' => [
                'format' => [
                    'values' => ['csv', 'json'],
                    'default' => 'csv',
                    'optional' => true,
                ],
                'page' => [
                    'type' => 'integer',
                    'optional' => true,
                    'info' => [
                         'items-per-page' => self::ITEMS_PER_PAGE,
                    ],
                ],
            ]
        ];
    }
}
