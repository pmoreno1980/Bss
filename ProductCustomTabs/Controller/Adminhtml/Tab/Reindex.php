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
namespace Bss\ProductCustomTabs\Controller\Adminhtml\Tab;

use Bss\ProductCustomTabs\Model\Rule;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use \Psr\Log\LoggerInterface;

class Reindex extends \Magento\Backend\App\Action
{

    /**
     * Array Product Ids
     *
     * @var array
     */
    protected $dataProductIds;

    /**
     * Collection Tab Factory
     *
     * @var \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory $collectionTabFactory
     */
    protected $collectionTabFactory;

    /**
     * Resource Tab
     *
     * @var \Bss\ProductCustomTabs\Model\ResourceModel\Tab $resourceTab
     */
    protected $resourceTab;

    /**
     * Tab Factory
     *
     * @var \Bss\ProductCustomTabs\Model\TabFactory $tabFactory
     */
    protected $tabFactory;

    /**
     * LoggerInterface
     *
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * Rule Factory
     *
     * @var \Bss\ProductCustomTabs\Model\RuleFactory $ruleFactory
     */
    protected $ruleFactory;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry $coreRegistry
     */
    protected $coreRegistry;

    /**
     * Resource Rule
     *
     * @var \Bss\ProductCustomTabs\Model\ResourceModel\Rule $resourceRule
     */
    protected $resourceRule;

    /**
     * Reindex constructor.
     *
     * @param Action\Context $context
     * @param \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory $collectionTabFactory
     * @param \Bss\ProductCustomTabs\Model\ResourceModel\Tab $resourceTab
     * @param \Bss\ProductCustomTabs\Model\TabFactory $tabFactory
     * @param \Bss\ProductCustomTabs\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Bss\ProductCustomTabs\Model\ResourceModel\Rule $resourceRule
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function __construct(
        Action\Context $context,
        \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory $collectionTabFactory,
        \Bss\ProductCustomTabs\Model\ResourceModel\Tab $resourceTab,
        \Bss\ProductCustomTabs\Model\TabFactory $tabFactory,
        \Bss\ProductCustomTabs\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Bss\ProductCustomTabs\Model\ResourceModel\Rule $resourceRule,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->collectionTabFactory = $collectionTabFactory;
        $this->resourceTab = $resourceTab;
        $this->tabFactory = $tabFactory;
        $this->logger = $logger;
        $this->ruleFactory = $ruleFactory;
        $this->resourceRule = $resourceRule;
        $this->coreRegistry = $coreRegistry;
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
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {

        $storeId = $this->getRequest()->getParam('store');
        if ($storeId) {
            $arrConditionStore = [$storeId, 0];
        } else {
            $arrConditionStore = [0];
        }
        $collectionTab = $this->collectionTabFactory->create();
        $allTabs = $collectionTab->addFieldToFilter(
            'store_id',
            ['in' => $arrConditionStore]
        )->getData();
        $collectionSize = count($allTabs);
        foreach ($allTabs as $tab) {
            $this->coreRegistry->unregister('bss_pct_customer_group_update_data');
            $this->coreRegistry->register('bss_pct_customer_group_update_data', $tab);
            $rule = $this->getRule($tab['rule_id']);
            $matchProductIds = $rule->getMatchingProductIds();
            $whereRule = ['rule_id = ?' => $tab['rule_id']];
            foreach ($matchProductIds as $productId) {
                $this->dataProductIds[] = [
                    'rule_id' => $tab['rule_id'],
                    'product_id' => $productId,
                    'tab_id' => $tab['id']
                ];
            }
            $this->resourceRule->deleteMultipleData('bss_pct_tabs_products', $whereRule);
        }

        $this->resourceRule->updateMultipleData('bss_pct_tabs_products', $this->dataProductIds);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $this->messageManager->addSuccessMessage(
            __(
                'A total record(s) have been reindexed.',
                $collectionSize
            )
        );
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/', [
                'store' => $storeId
            ]);
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ProductCustomTabs::save");
    }
}
