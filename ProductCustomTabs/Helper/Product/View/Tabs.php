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
 * @package    BSS_ProductCustomTabs
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Helper\Product\View;

use \Bss\ProductCustomTabs\Model;
use Magento\Cms\Model\Template\FilterProvider;

class Tabs extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Tab Factory
     *
     * @var Model\TabFactory $tabFactory
     */
    protected $tabFactory;

    /**
     * Filter Provider
     *
     * @var FilterProvider $filterProvider
     */
    protected $filterProvider;

    /**
     * ConfigAdmin
     *
     * @var \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin
     */
    protected $helperConfigAdmin;

    /**
     * Tab Product Collection Factory
     *
     * @var Model\ResourceModel\Product\CollectionFactory $productTabCollection
     */
    protected $productTabCollection;

    /**
     * @var \Bss\ProductCustomTabs\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Tabs constructor.
     * @param Model\ResourceModel\Product\CollectionFactory $productTabCollection
     * @param Model\TabFactory $tabFactory
     * @param \Magento\Framework\App\Helper\Context $context
     * @param FilterProvider $filterProvider
     * @param \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin
     * @param \Bss\ProductCustomTabs\Helper\Data $helperData
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Model\ResourceModel\Product\CollectionFactory $productTabCollection,
        Model\TabFactory $tabFactory,
        \Magento\Framework\App\Helper\Context $context,
        FilterProvider $filterProvider,
        \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin,
        \Bss\ProductCustomTabs\Helper\Data $helperData,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->productTabCollection = $productTabCollection;
        $this->tabFactory = $tabFactory;
        $this->filterProvider = $filterProvider;
        $this->helperConfigAdmin = $helperConfigAdmin;
        $this->helperData = $helperData;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * Remove Tabs Default
     *
     * @param array $detailedInfoGroup
     * @return void
     */
    public function removeTabsDefault(&$detailedInfoGroup = [])
    {
        if (is_array($detailedInfoGroup)) {
            for ($i = 0; $i <= 2; $i++) {
                unset($detailedInfoGroup[$i]);
            }
        }
    }

    /**
     * Get Config Admin
     *
     * @return \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin
     */
    public function getConfigAdmin()
    {
        return $this->helperConfigAdmin;
    }

    /**
     * @return mixed
     */
    public function getConfigHideDefaultTabs()
    {
        return $this->getConfigAdmin()->getConfigHideDefaultTabs();
    }

    /**
     * Get Product Customer Purchased
     *
     * @return array
     */
    protected function getProductCustomerPurchased()
    {
        return $this->helperData->getProductCustomerPurchased();
    }

    /**
     * Load Tab By Id
     *
     * @param int $tabId
     *
     * @return Model\Tab
     */
    protected function loadTabById($tabId)
    {
        return $this->tabFactory->create()->load($tabId);
    }

    /**
     * Get Tabs By Store View
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getTabsByStoreView()
    {
        $storeId = $this->helperData->getStoreManager()->getStore()->getId();
        $customerGroupId = $this->helperData->getCustomerSession()->getCustomerGroupId();
        $currentProductId = $this->helperData->getRegistry()->registry('current_product')->getId();
        $arrTab = [];
        try {
            $collection = $this->productTabCollection->create(
            )->addFieldToFilter(
                'store_id',
                [
                    'eq' => $storeId
                ]
            )->addFieldToFilter(
                'product_id',
                ['eq' => $currentProductId]
            )->addFieldToFilter(
                'status',
                [
                    'eq' => 1
                ]
            );
            $collection->setOrder(
                'position',
                'ASC'
            );
            $arrData = $collection->getItems();

            $arrTabs = [];
            $stt = 0;
            $arrParentTabIds = [];
            foreach ($arrData as $tab) {
                $parentId = $tab->getData('parent_id');
                $this->getAllParentIdsByTab($parentId, $arrParentTabIds);
                $resourceTab = $this->tabFactory->create()->getResource();
                $customerGroupIds = $resourceTab->getCustomerGroupIdsByTabId($tab->getId());
                $arrTabs[] = $tab->getData();
                $arrTabs[$stt]['group_customer_ids'] = $customerGroupIds;
                $stt++;
            }

            $this->filterArrayTabs($arrTabs, $arrParentTabIds);
            $arrTab = [];
            $arrCurrentProductId = ['product_id' => $currentProductId];

            foreach ($arrTabs as $tab) {
                $customerGroupIds = $resourceTab->getCustomerGroupTable($tab['id']);
                $customerPurchasedIds = $this->getProductCustomerPurchased();

                if (in_array($customerGroupId, $customerGroupIds)) {
                    if ($tab['visible_customer_purchased'] &&
                        in_array($arrCurrentProductId, $customerPurchasedIds)
                    ) {
                        $arrTab[] = [
                            'tab_id' => $tab['id'],
                            'title' => $tab['title'],
                            'content' => $tab['content'],
                            'attribute_ids' => $tab['attribute_ids'],
                            'show_related_products' => $tab['show_related_products'],
                            'show_up_sell_products' => $tab['show_up_sell_products'],
                            'show_cross_sell_products' => $tab['show_cross_sell_products']
                        ];
                    } elseif (!$tab['visible_customer_purchased']) {
                        $arrTab[] = [
                            'tab_id' => $tab['id'],
                            'title' => $tab['title'],
                            'content' => $tab['content'],
                            'attribute_ids' => $tab['attribute_ids'],
                            'show_related_products' => $tab['show_related_products'],
                            'show_up_sell_products' => $tab['show_up_sell_products'],
                            'show_cross_sell_products' => $tab['show_cross_sell_products']
                        ];
                    }

                }
            }
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return $arrTab;
    }

    /**
     * Filter Array Tabs
     * @param array $arrTabs
     * @param array $arrParentTabIds
     * @return void
     */
    protected function filterArrayTabs(&$arrTabs, $arrParentTabIds)
    {
        foreach ($arrTabs as $key => $tab) {
            if (in_array($tab['id'], $arrParentTabIds)) {
                unset($arrTabs[$key]);
            }
        }
    }

    /**
     * Get All Parent Ids By Tab
     * @param int $parentId
     * @param array $arrParentTabIds
     */
    protected function getAllParentIdsByTab($parentId, &$arrParentTabIds)
    {
        while ($parentId != 0) {
            $tabId = $parentId;
            $resourceTab = $this->tabFactory->create()->getResource();
            $parentId = $resourceTab->getParentIdByTab($tabId);
            $arrParentTabIds[] = $tabId;
        }
    }

    /**
     * Get Attributes Content
     * @param string $attributeIdsOfTab
     * @return string
     */
    public function getAttributesContent($attributeIdsOfTab)
    {
        return $this->helperData->getAttributesContent($attributeIdsOfTab);
    }

    /**
     * Get Related Product Content
     *
     * @param int $relatedContent
     * @return string
     */
    public function getRelatedProductContent($relatedContent)
    {
        if ($relatedContent) {
            if ($this->productMetadata->getEdition() != 'Community') {
                $content = "{{block class=\"Magento\TargetRule\Block\Catalog\Product\ProductList\Related\" 
                name=\"catalog.product.related\" type=\"related-rule\" 
                template=\"Magento_Catalog::product/list/items.phtml\" }}";
            } else {
                $content = "{{block class=\"Magento\Catalog\Block\Product\ProductList\Related\" 
                name=\"catalog.product.related\" type=\"related\" 
                template=\"Magento_Catalog::product/list/items.phtml\" }}";
            }

        } else {
            $content = "";
        }
        return $content;
    }

    /**
     * Get Upsell Product Content
     *
     * @param int $upsellContent
     * @return string
     */
    public function getUpsellProductContent($upsellContent)
    {
        if ($upsellContent) {
            if ($this->productMetadata->getEdition() != 'Community') {
                $content = "{{block class=\"Magento\TargetRule\Block\Catalog\Product\ProductList\Upsell\" 
                name=\"product.info.upsell\" type=\"upsell-rule\" 
                template=\"Magento_Catalog::product/list/items.phtml\" }}";
            } else {
                $content = "{{block class=\"Magento\Catalog\Block\Product\ProductList\Upsell\" 
                name=\"product.info.upsell\" type=\"upsell\" 
                template=\"Magento_Catalog::product/list/items.phtml\" }}";
            }
        } else {
            $content = "";
        }
        return $content;
    }

    /**
     * Get Crosssell Product Content
     *
     * @param int $crosssellContent
     * @return string
     */
    public function getCrosssellProductContent($crosssellContent)
    {

        if ($crosssellContent) {
            $content = "{{block class=\"Magento\Catalog\Block\Product\ProductList\Crosssell\"
            name=\"product.info.upsell\" type=\"crosssell\" 
            template=\"Magento_Catalog::product/list/items.phtml\" }}";
        } else {
            $content = "";
        }
        return $content;
    }

    /**
     * Edit Content Tab
     * @param string $content
     * @return string
     * @throws \Exception
     */
    public function editTabContent($content)
    {
        $content = $this->filterProvider->getPageFilter()->filter($content);
        return $content;
    }
}
