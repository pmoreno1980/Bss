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
namespace Bss\ProductCustomTabs\Model;

use \Magento\Framework\Model\AbstractModel;
use \Bss\ProductCustomTabs;
use Bss\ProductCustomTabs\Model;

class Tab extends AbstractModel implements ProductCustomTabs\Api\Data\TabInterface
{

    /**
     * Resource Tab
     *
     * @var ResourceModel\Tab $resourceTab
     */
    protected $resourceTab;

    /**
     * Tab constructor.
     *
     * @param ResourceModel\Tab $resourceTab
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        Model\ResourceModel\Tab $resourceTab,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->resourceTab = $resourceTab;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_init(\Bss\ProductCustomTabs\Model\ResourceModel\Tab::class);
    }

    /**
     * After Save
     * @return $this|void
     */
    public function afterSave()
    {
        $data = $this->_registry->registry('bss_pct_customer_group_update_data');
        if (!empty($data)) {
            if (!empty($data['group_customer_ids'])) {
                $this->resourceTab->deleteMultiData($this->getId());
                $this->resourceTab->updateCustomerGroup($data['group_customer_ids'], $this->getId());
            }
        }
    }

    /**
     * Set Id
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->setData(self::BSS_ID_TAB, $id);
    }

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::BSS_ID_TAB);
    }

    /**
     * Set Status
     *
     * @param string $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->setData(self::BSS_STATUS, $status);
    }

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::BSS_STATUS);
    }

    /**
     * Set Title
     *
     * @param string $title
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->setData(self::BSS_TITLE_TAB, $title);
    }

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::BSS_TITLE_TAB);
    }

    /**
     * Set Content
     *
     * @param string $content
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->setData(self::BSS_CONTENT_TAB, $content);
    }

    /**
     * Get Content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getData(self::BSS_CONTENT_TAB);
    }

    /**
     * Set Position
     *
     * @param int $position
     *
     * @return void
     */
    public function setPosition($position)
    {
        $this->setData(self::BSS_POSITION, $position);
    }

    /**
     * Get Position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->getData(self::BSS_POSITION);
    }

    /**
     * Set Visible Customer Purchased
     *
     * @param int $visibleCustomerPurchased
     *
     * @return void
     */
    public function setVisibleCustomerPurchased($visibleCustomerPurchased)
    {
        $this->setData(self::BSS_VISIBLE_CUSTOMER_PURCHASED, $visibleCustomerPurchased);
    }

    /**
     * Get Visible Customer Purchased
     * @return int
     */
    public function getVisibleCustomerPurchased()
    {
        return $this->getData(self::BSS_VISIBLE_CUSTOMER_PURCHASED);
    }

    /**
     * Set Store Id
     *
     * @param int $storeId
     *
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->setData(self::BSS_STORE_ID, $storeId);
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::BSS_STORE_ID);
    }

    /**
     * Set Array Attribute Ids
     *
     * @param string $attributeIds
     *
     * @return void
     */
    public function setAttributeIds($attributeIds)
    {
        $this->setData(self::BSS_ATTRIBUTE_IDS, $attributeIds);
    }

    /**
     * Get Array Attriute Ids
     *
     * @return array
     */
    public function getAttributeIds()
    {
        return $this->getData(self::BSS_ATTRIBUTE_IDS);
    }

    /**
     * Get Array Attribute Ids
     *
     * @return array
     */
    public function getArrAttributeIds()
    {
        return explode('&', $this->getData(self::BSS_ATTRIBUTE_IDS));
    }

    /**
     * Set Show Related Product
     *
     * @param string $showRelatedProducts
     *
     * @return void
     */
    public function setShowRelatedProduct($showRelatedProducts)
    {
        $this->setData(self::BSS_SHOW_RELATED_PRODUCTS, $showRelatedProducts);
    }

    /**
     * Get Array Related Product
     *
     * @return array
     */
    public function getShowRelatedProduct()
    {
        return $this->getData(self::BSS_SHOW_RELATED_PRODUCTS);
    }

    /**
     * Set Show UpSell Product
     *
     * @param int $showUpSellProducts
     *
     * @return void
     */
    public function setShowUpSellProduct($showUpSellProducts)
    {
        $this->setData(self::BSS_SHOW_UP_SELL_PRODUCTS, $showUpSellProducts);
    }

    /**
     * Get Array UpSell Product
     *
     * @return array
     */
    public function getShowUpSellProduct()
    {
        return $this->getData(self::BSS_SHOW_UP_SELL_PRODUCTS);
    }

    /**
     * Set Show Cross Sell Product
     *
     * @param int $showCrossSellProducts
     */
    public function setShowCrossSellProduct($showCrossSellProducts)
    {
        $this->setData(self::BSS_SHOW_CROSS_SELL_PRODUCTS, $showCrossSellProducts);
    }

    /**
     * Get Array CrossSell Product
     *
     * @return array
     */
    public function getShowCrossSellProduct()
    {
        return $this->getData(self::BSS_SHOW_CROSS_SELL_PRODUCTS);
    }
}
