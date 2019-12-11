<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductCustomTabs
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Api\Data;

interface TabInterface
{

    /**
     * BSS_ID_TAB
     */
    const BSS_ID_TAB = 'id';

    /**
     * BSS_STATUS
     */
    const BSS_STATUS = 'status';

    /**
     * BSS_TITLE_TAB
     */
    const BSS_TITLE_TAB = 'title';

    /**
     * BSS_CONTENT_TAB
     */
    const BSS_CONTENT_TAB = 'content';

    /**
     * BSS_POSITION
     */
    const BSS_POSITION = 'position';

    /**
     * BSS_VISIBLE_CUSTOMER_PURCHASED
     */
    const BSS_VISIBLE_CUSTOMER_PURCHASED = 'visible_customer_purchased';

    /**
     * BSS_STORE_ID
     */
    const BSS_STORE_ID = 'store_id';

    /**
     * BSS_RULE
     */
    const BSS_RULE = 'rule_id';

    /**
     * BSS_ATTRIBUTE_IDS
     */
    const BSS_ATTRIBUTE_IDS = 'attribute_ids';

    /**
     * BSS_SHOW_RELATED_PRODUCTS
     */
    const BSS_SHOW_RELATED_PRODUCTS = 'show_related_products';

    /**
     * BSS_SHOW_UP_SELL_PRODUCTS
     */
    const BSS_SHOW_UP_SELL_PRODUCTS = 'show_up_sell_products';

    /**
     * BSS_SHOW_CROSS_SELL_PRODUCTS
     */
    const BSS_SHOW_CROSS_SELL_PRODUCTS = 'show_cross_sell_products';

    /**
     * Set Id
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * Get Id
     * @return Int
     */
    public function getId();

    /**
     * Set Status
     * @param string $status
     * @return void
     */
    public function setStatus($status);

    /**
     * Get Status
     * @return int
     */
    public function getStatus();

    /**
     * Set Title
     * @param string $title
     * @return void
     */
    public function setTitle($title);

    /**
     * Get Title
     * @return string
     */
    public function getTitle();

    /**
     * @param string $content
     * @return void
     */
    public function setContent($content);

    /**
     * Get Content
     * @return string
     */
    public function getContent();

    /**
     * Set Position
     * @param int $position
     * @return void
     */
    public function setPosition($position);

    /**
     * Get Position
     * @return int
     */
    public function getPosition();

    /**
     * @param int $visibleCustomerPurchased
     * @return void
     */
    public function setVisibleCustomerPurchased($visibleCustomerPurchased);

    /**
     * Get Visible Customer Purchased
     * @return int
     */
    public function getVisibleCustomerPurchased();

    /**
     * Set Store Id
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId);

    /**
     * Get Store Id
     * @return int
     */
    public function getStoreId();

    /**
     * Set Attribute Ids
     * @param mixed $attributeIds
     * @return void
     */
    public function setAttributeIds($attributeIds);

    /**
     * Get Array Attribute Ids
     * @return array
     */
    public function getAttributeIds();

    /**
     * Set Show Related Product Ids
     * @param bool $showRelatedProducts
     * @return void
     */
    public function setShowRelatedProduct($showRelatedProducts);

    /**
     * Get Array Product Related Ids
     * @return array
     */
    public function getShowRelatedProduct();

    /**
     * Set Show Up Sell Product Ids
     * @param bool $showUpSellProducts
     * @return void
     */
    public function setShowUpSellProduct($showUpSellProducts);

    /**
     * Get Array Up Sell Product Ids
     * @return array
     */
    public function getShowUpSellProduct();

    /**
     * Set Show Cross Sell Product
     * @param bool $showCrossSellProducts
     * @return void
     */
    public function setShowCrossSellProduct($showCrossSellProducts);

    /**
     * Get Show Cross Sell Product Ids
     * @return array
     */
    public function getShowCrossSellProduct();
}
