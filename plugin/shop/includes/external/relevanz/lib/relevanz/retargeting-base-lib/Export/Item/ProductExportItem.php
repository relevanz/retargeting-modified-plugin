<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base\Export\Item;

use Releva\Retargeting\Base\Utf8Util;

/**
 * Product export item
 *
 * Simple data wrapper object that represents a product.
 */
class ProductExportItem implements ExportItemInterface
{
    protected $id = 0;
    protected $categoryIds = [];
    protected $name = '';
    protected $descriptionShort = '';
    protected $descriptionLong = '';
    protected $price = 0.0;
    protected $priceOffer = 0.0;
    protected $link = '';
    protected $image = '';

    protected $netPrice = 0.0;
    protected $taxRate = 0;

    public function __construct($id, array $cIds, $name, $descShort, $descLong, $price, $priceOffer, $netPrice, $taxRate, $link, $image) {
        $this->id = $id;
        $this->categoryIds = $cIds;
        $this->name = Utf8Util::toUtf8($name);
        $this->descriptionShort = str_replace(["\r\n", "\r"], "\n", Utf8Util::toUtf8($descShort));
        $this->descriptionLong = str_replace(["\r\n", "\r"], "\n", Utf8Util::toUtf8($descLong));
        $this->price = (float)$price;
        $this->priceOffer = (float)$priceOffer;
        $this->price = max($this->price, $this->priceOffer);
        $this->priceOffer = min($this->price, $this->priceOffer);
        if ($this->priceOffer <= 0.0) {
            $this->priceOffer = $this->price;
        }
        $this->link = $link;
        $this->image = $image;
        $this->netPrice = (float)$netPrice;
        $this->taxRate = (int)$taxRate;
    }

    public function getId() {
        return $this->id;
    }

    public function getCategoryIds() {
        return $this->categoryIds;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescriptionShort() {
        return $this->descriptionShort;
    }

    public function getDescriptionLong() {
        return $this->descriptionLong;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getPriceOffer() {
        return $this->priceOffer;
    }

    public function getLink() {
        return $this->link;
    }

    public function getImage() {
        return $this->image;
    }

    public function getData() {
        return [
            'id' => $this->id,
            'categoryIds' => $this->categoryIds,
            'name' => $this->name,
            'descriptionShort' => $this->descriptionShort,
            'descriptionLong' => $this->descriptionLong,
            'price' => $this->price,
            'priceOffer' => $this->priceOffer,
            'link' => $this->link,
            'image' => $this->image,
            'netPrice' => $this->netPrice,
            'taxRate' => $this->taxRate,
        ];
    }

    public function getKeys() {
        return array_keys($this->getData());
    }

}
