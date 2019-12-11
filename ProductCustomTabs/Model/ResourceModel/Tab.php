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
namespace Bss\ProductCustomTabs\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Psr\Log\LoggerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Customer;
use \Magento\Framework\Registry;

class Tab extends AbstractDb
{

    /**
     *
     * ResourceConnection
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resources;

    /**
     * AdapterInterface
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connections;

    /**
     * Log
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Message Manager
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Store Manager Interface
     *
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * Session Factory
     *
     * @var Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * Registry
     *
     * @var Registry $registry
     */
    protected $registry;

    /**
     * Tab Factory
     *
     * @var \Bss\ProductCustomTabs\Model\TabFactory $tabFactory
     */
    protected $tabFactory;

    /**
     * Resource Rule
     *
     * @var Rule
     */
    protected $resourceRule;

    /**
     * Tab constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param Customer\Model\SessionFactory $customerSession
     * @param Registry $registry
     * @param \Bss\ProductCustomTabs\Model\TabFactory $tabFactory
     * @param Rule $resourceRule
     * @param null $connectionName
     * @return void
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        Customer\Model\SessionFactory $customerSession,
        Registry $registry,
        \Bss\ProductCustomTabs\Model\TabFactory $tabFactory,
        \Bss\ProductCustomTabs\Model\ResourceModel\Rule $resourceRule,
        $connectionName = null
    ) {
        $this->resourceRule = $resourceRule;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->resources = $context->getResources();
        $this->connections = $this->resources->getConnection();
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->tabFactory = $tabFactory;

        parent::__construct($context, $connectionName);
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('bss_product_custom_tabs', 'id');
    }

    /**
     * Insert Table
     *
     * @param string $tableName
     * @param array $bind
     *
     * @return void
     */
    public function insertTable($tableName, $bind)
    {

        try {
            $table = $this->getTable($tableName);
            if (is_array($bind)) {
                $this->connections->insert($table, $bind);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Delete Multi Data
     *
     * @param int $tabId
     *
     * @return void
     */
    public function deleteMultiData($tabId)
    {
        try {
            $table = $this->getTable('bss_pct_tab_customer_group');
            $whereTab = ['tab_id = ?' => $tabId];
            $this->deleteData($table, $whereTab);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Get Customer Group Table
     *
     * @param int $tabId
     *
     * @return array
     */
    public function getCustomerGroupTable($tabId)
    {
        $customerGroupIds = [];
        try {
            $table = $this->getTable('bss_pct_tab_customer_group');
            $sql = $this->connections->select()->from(
                $table
            )->where('tab_id = ?', $tabId);
            $query = $this->connections->query($sql);
            while ($row = $query->fetch()) {
                $customerGroupIds[] = $row['customer_group_id'];
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $customerGroupIds;
    }

    /**
     * UpdateCustomerGroup
     *
     * @param array $data
     * @param int $tabId
     *
     * @return void
     */
    public function updateCustomerGroup($data, $tabId)
    {
        $dataInsert = [];
        try {
            $table = $this->getTable('bss_pct_tab_customer_group');
            foreach ($data as $customerGroupId) {
                $dataInsert[] = [
                    'tab_id'    => $tabId,
                    'customer_group_id' => $customerGroupId
                ];
            }

            $this->updateMultipleData($table, $dataInsert);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Update database
     *
     * @param string $tableName
     * @param array $data
     *
     * @return void
     */
    public function updateMultipleData($tableName, $data = [])
    {

        try {
            $table = $this->getTable($tableName);
            if ($table && !empty($data)) {
                $this->getConnection()->insertMultiple($table, $data);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Delete Data
     *
     * @param string $table
     * @param array $where
     *
     * @return void
     */
    public function deleteData($table, $where)
    {

        $table = $this->getTable($table);
        try {
            if (is_array($where)) {
                $this->connections->delete($table, $where);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Get Child Tab
     * @param int $tabId
     * @return array
     */
    public function getChildTab($tabId)
    {

        $childIds = [];
        try {
            $adapter = $this->getConnection();
            $table = $this->getTable('bss_product_custom_tabs');

            $sql = $adapter->select()->from(
                $table,
                ['id']
            )->where(
                'parent_id = ?',
                $tabId
            );

            $query = $adapter->query($sql);
            while ($row = $query->fetch()) {
                $childIds[] = $row['id'];
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $childIds;
    }

    /**
     * Get Array Customer Group Ids By Tab Id
     *
     * @param int $tabId
     *
     * @return array
     */
    public function getCustomerGroupIdsByTabId($tabId)
    {
        $table = $this->getTable('bss_pct_tab_customer_group');
        $customerGroupIds = [];
        try {
            $sql = $this->connections->select()->from(
                [$table]
            )->where('tab_id = ?', $tabId);

            $query = $this->connections->query($sql);

            while ($row = $query->fetch()) {
                array_push($customerGroupIds, $row['customer_group_id']);
            }

        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $customerGroupIds;
    }

    /**
     * Get Child Tab By Parent Id
     *
     * @param int $parentId
     * @param int $storeId
     *
     * @return array
     */
    public function getChildTabByParentId($parentId, $storeId)
    {

        $table = $this->getTable('bss_product_custom_tabs');
        $sql = $this->connections->select()->from(
            [
                'bss_main_table' => $table,
                ['id']
            ]
        )->where(
            'parent_id = ?',
            $parentId
        )->where(
            'store_id = ?',
            $storeId
        );
        $result = $this->connections->fetchRow($sql);
        return $result['id'];
    }

    /**
     * Get Parent Id By Tab
     *
     * @param int $tabId
     *
     * @return array
     */
    public function getParentIdByTab($tabId)
    {
        $table = $this->getTable('bss_product_custom_tabs');
        $sql = $this->connections->select()->from(
            $table,
            ['parent_id']
        )->where('id = ?', $tabId);
        $result = $this->connections->fetchRow($sql);
        return $result['parent_id'];
    }

    /**
     * Get Tab Child Not Is Default
     *
     * @param int $parentId
     *
     * @return array
     */
    public function getTabChildNotIsDefault($parentId)
    {
        $tabIds = [];
        try {
            $tableTab = $this->getTable('bss_product_custom_tabs');
            $tableRule = $this->getTable('bss_pct_tab_rule');
            $sql = $this->connections->select()->from(
                [
                    'bss_main_table' => $tableTab,
                    ['*']
                ]
            )->join(
                ['bptr' => $tableRule],
                'bss_main_table.id = bptr.tab_id',
                ['*']
            )->where(
                'parent_id = ?',
                $parentId
            )->where(
                'is_default = ?',
                1
            )->where(
                'rule_id != ?',
                0
            );
            $query = $this->connections->query($sql);

            while ($row = $query->fetch()) {
                array_push(
                    $tabIds,
                    $row['id']
                );
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $tabIds;
    }

    /**
     * Get Tab By Store
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getTabByStore($storeId)
    {
        $tabIds = [];
        try {
            $table = $this->getTable('bss_product_custom_tabs');

            $sql = $this->connections->select()->from([$table])->where(
                'store_id = ?',
                $storeId
            );

            $query = $this->connections->query($sql);

            while ($row = $query->fetch()) {
                array_push(
                    $tabIds,
                    $row['id']
                );
            }

        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $tabIds;
    }

    /**
     * Remove Tab To Product
     *
     * @param int $productId
     * @param int $storeId
     *
     * @return void
     */
    public function removeTabToProduct($productId, $storeId)
    {
        try {
            $tabIds = $this->getTabByStore($storeId);

            if (is_array($tabIds)) {
                $table = $this->getTable('bss_pct_tabs_products');
                foreach ($tabIds as $tabId) {
                    $where = [
                        'product_id = ?' => $productId,
                        'tab_id = ?' => $tabId
                    ];
                    $this->deleteData($table, $where);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Get Rule Id By Tab Id
     *
     * @param int $tabId
     *
     * @return int|string
     */
    public function getRuleIdByTabId($tabId)
    {
        $ruleId = 0;
        try {
            $table = $this->getTable('bss_pct_tab_rule');
            $sql = $this->connections->select()->from(
                $table
            )->where(
                'tab_id = ?',
                $tabId
            );
            $ruleId = $this->connections->fetchOne($sql);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $ruleId;
    }
}
