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
use Bss\ProductCustomTabs\Model\TabFactory;
use Bss\ProductCustomTabs\Model\ResourceModel;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Bss\ProductCustomTabs\Model\RuleFactory;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Attribute
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * Resource Tab
     * @var ResourceModel\Tab $resourceTab
     */
    protected $resourceTab;

    /**
     * Tab Factory
     *
     * @var TabFactory $tabFactory
     */
    protected $tabFactory;

    /**
     * Resource Rule
     * @var ResourceModel\Rule $resourceRule
     */
    protected $resourceRule;

    /**
     * Rule Factory
     * @var RuleFactory $ruleFactory
     */
    protected $ruleFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param TabFactory $tabFactory
     * @param ResourceModel\Tab $resourceTab
     * @param Attribute $eavAttribute
     * @param ResourceModel\Rule $resourceRule
     * @param RuleFactory $ruleFactory
     * @return void
     */
    public function __construct(
        Action\Context $context,
        TabFactory $tabFactory,
        ResourceModel\Tab $resourceTab,
        Attribute $eavAttribute,
        ResourceModel\Rule $resourceRule,
        RuleFactory $ruleFactory
    ) {
        parent::__construct($context);
        $this->eavAttribute = $eavAttribute;
        $this->resourceTab = $resourceTab;
        $this->tabFactory = $tabFactory;
        $this->resourceRule = $resourceRule;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Delete Related Tab
     *
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
     * Delete Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /* @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                /**
                 * Load Tab By Id
                 */
                $modelTab = $this->tabFactory->create();
                $modelTab->load($id);
                /**
                 * Delete Rule
                 */
                $ruleId = $this->resourceRule->getRuleByTabId($id);
                $modelRule = $this->ruleFactory->create();
                $modelRule->load($ruleId);
                $modelRule->delete();

                $storeId = $modelTab->getStoreId();
                $this->deleteRelatedTab($id);

                if ($storeId == 0) {
                    $childIds = $this->resourceTab->getChildTab($id);
                    foreach ($childIds as $childId) {
                        $this->deleteRelatedTab($childId);
                    }
                }

                /**
                 * Delete Tab
                 */
                $modelTab->delete();

                // display success message
                $this->messageManager->addSuccessMessage(
                    __('The tab has been deleted.')
                );
                return $resultRedirect->setPath('*/*/',
                    ['store' => $storeId]);
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['id' => $id]
                );
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(
            __('We can\'t find a tab to delete.')
        );
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check Rule
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ProductCustomTabs::delete");
    }
}
