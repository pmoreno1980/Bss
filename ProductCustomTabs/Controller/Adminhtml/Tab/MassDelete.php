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

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use \Psr\Log\LoggerInterface;

class MassDelete extends \Magento\Backend\App\Action
{

    /**
     * Collection Tab Factory
     *
     * @var \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory $collectionTabFactory
     */
    protected $collectionTabFactory;

    /**
     * Filter
     *
     * @var Filter $filter
     */
    protected $filter;

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
     * Logger
     *
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * Store Manager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * MassDelete constructor.
     *
     * @param Action\Context $context
     * @param \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory $collectionTabFactory
     * @param \Bss\ProductCustomTabs\Model\ResourceModel\Tab $resourceTab
     * @param \Bss\ProductCustomTabs\Model\TabFactory $tabFactory
     * @param LoggerInterface $logger
     * @param Filter $filter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     *
     * @return void
     */
    public function __construct(
        Action\Context $context,
        \Bss\ProductCustomTabs\Model\ResourceModel\Tab\CollectionFactory $collectionTabFactory,
        \Bss\ProductCustomTabs\Model\ResourceModel\Tab $resourceTab,
        \Bss\ProductCustomTabs\Model\TabFactory $tabFactory,
        LoggerInterface $logger,
        Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionTabFactory = $collectionTabFactory;
        $this->resourceTab = $resourceTab;
        $this->tabFactory = $tabFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Delete Tab
     *
     * @param int $tabId
     * @return void
     */
    protected function deleteTab($tabId)
    {
        try {
            $modelTab = $this->tabFactory->create();
            $modelTab->load($tabId);
            $modelTab->delete();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Delete Related Tab
     * @param int $tabId
     * @return void
     */
    protected function deleteRelatedTab($tabId)
    {
        $where = [
            'tab_id = ?' => $tabId
        ];
        $tableCustomer = 'bss_pct_tab_customer_group';
        $tableProduct = 'bss_pct_tabs_products';
        $tableRule = 'bss_pct_tab_rule';
        $this->resourceTab->deleteData($tableCustomer, $where);
        $this->resourceTab->deleteData($tableProduct, $where);
        $this->resourceTab->deleteData($tableRule, $where);
    }

    /**
     * All Store View Delete Child Tab
     * @param int $storeId
     * @param int $tabId
     * @return void
     */
    protected function allStoreViewDeleteChildTab($storeId, $tabId)
    {
        if ($storeId == 0) {
            /**
             * Get Child Tabs By Tab Id
             */
            $childIds = $this->resourceTab->getChildTab($tabId);

            foreach ($childIds as $childId) {
                $this->deleteRelatedTab($childId);
                $this->deleteTab($childId);
            }
        }
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        $storeId = $this->getRequest()->getParam('store') ? $this->getRequest()->getParam('store') : 0;
        $collectionSize = 0;
        $collection = $this->filter->getCollection($this->collectionTabFactory->create());
        $tabIds = $collection->getAllIds();
        foreach ($tabIds as $tabId) {
            /**
             * All Store View
             */
            $this->allStoreViewDeleteChildTab($storeId, $tabId);
            $this->deleteRelatedTab($tabId);
            $this->deleteTab($tabId);
            $collectionSize++;
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $this->messageManager->addSuccessMessage(
            __(
                'A total of %1 record(s) have been deleted.',
                $collectionSize
            )
        );
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/', [
            'store' => $storeId,
            '_current' => true
        ]);
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ProductCustomTabs::delete");
    }
}
