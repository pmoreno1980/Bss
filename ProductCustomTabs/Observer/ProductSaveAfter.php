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
 * @category  BSS
 * @package   Bss_ProductCustomTabs
 * @author    Extension Team
 * @copyright Copyright (c) 2019-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Observer;

use Bss\ProductCustomTabs\Model\Rule;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory
     */
    protected $collectionTabFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Array Product Ids
     *
     * @var array
     */
    protected $dataProductIds;

    /**
     * Resource Rule
     *
     * @var \Bss\ProductCustomTabs\Model\ResourceModel\Rule $resourceRule
     */
    protected $resourceRule;

    /**
     * Rule Factory
     *
     * @var \Bss\ProductCustomTabs\Model\RuleFactory $ruleFactory
     */
    protected $ruleFactory;

    /**
     * Array Remove Product Ids
     *
     * @var array
     */
    protected $removeProductIds;

    /**
     * ProductSaveAfter constructor.
     * @param \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory $collectionTabFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\ProductCustomTabs\Model\RuleFactory $ruleFactory
     * @param \Bss\ProductCustomTabs\Model\ResourceModel\Rule $resourceRule
     */
    public function __construct(
        \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory $collectionTabFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\ProductCustomTabs\Model\RuleFactory $ruleFactory,
        \Bss\ProductCustomTabs\Model\ResourceModel\Rule $resourceRule
    ) {
        $this->collectionTabFactory = $collectionTabFactory;
        $this->storeManager = $storeManager;
        $this->ruleFactory = $ruleFactory;
        $this->resourceRule = $resourceRule;
    }

    /**
     * Get Rule
     * @param int $ruleId
     * @return Rule
     */
    protected function getRule($ruleId)
    {
        return $this->ruleFactory->create()->load($ruleId);
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var  $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        $storeId = $product->getStoreId();
        if ($storeId) {
            $arrConditionStore = [$storeId, 0];
        } else {
            $arrConditionStore = [];
            foreach ($this->storeManager->getStores() as $store) {
                $arrConditionStore[] = $store->getId();
            }
        }
        $collectionTab = $this->collectionTabFactory->create();
        $allTabs = $collectionTab->addFieldToFilter(
            'store_id',
            ['in' => $arrConditionStore]
        )->getData();
        foreach ($allTabs as $tab) {
            $rule = $this->getRule($tab['rule_id']);

            $checkProductId = $this->resourceRule->hasProductId($productId, $tab['rule_id']);
            if ($rule->validate($product) && !$checkProductId) {
                $this->dataProductIds[] = [
                    'rule_id' => $tab['rule_id'],
                    'product_id' => $productId,
                    'tab_id' => $tab['id']
                ];
            }
            if (!$rule->validate($product) && $checkProductId) {
                $whereRule = ['product_id = ?' => $productId, 'tab_id  = ?' => $tab['id']];
                $this->resourceRule->deleteMultipleData('bss_pct_tabs_products', $whereRule);
            }
        }
        $this->resourceRule->updateMultipleData('bss_pct_tabs_products', $this->dataProductIds);
    }
}
