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

class MassDisable extends \Magento\Backend\App\Action
{

    /**
     * BSS_DISABLE
     */
    const BSS_DISABLE = '0';

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
     * MassDisable constructor.
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
     * Disable Tab
     *
     * @param int $tabId
     * @return void
     */
    protected function disableTab($tabId)
    {
        try {
            $modelTab = $this->tabFactory->create();
            $modelTab->load($tabId);
            $modelTab->setStatus(self::BSS_DISABLE);
            $modelTab->save();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * All Store View Disable Child Tab
     *
     * @param int $storeId
     * @param int $tabId
     * @return void
     */
    protected function allStoreViewDisableChildTab($storeId, $tabId)
    {
        if ($storeId == 0) {
            /**
             * Get Child Tabs By Tab Id
             */
            $childIds = $this->resourceTab->getChildTab($tabId);

            foreach ($childIds as $childId) {
                $this->disableTab($childId);
            }
        }
    }

    /**
     * Mass Enable Action
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
            $this->allStoreViewDisableChildTab($storeId, $tabId);
            $this->disableTab($tabId);
            $collectionSize++;
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $this->messageManager->addSuccessMessage(
            __(
                'A total of %1 record(s) have been disabled.',
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
