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

use \Psr\Log\LoggerInterface;

class Rule extends \Magento\Rule\Model\ResourceModel\AbstractResource
{

    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     *
     * @var \Bss\ProductCustomTabs\Model\RuleFactory
     */
    private $autoRelatedRuleFac;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * LoggerInterface
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * Rule constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Bss\ProductCustomTabs\Model\RuleFactory $autoRelatedRuleFac
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Bss\ProductCustomTabs\Model\RuleFactory $autoRelatedRuleFac,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->date = $date;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->autoRelatedRuleFac = $autoRelatedRuleFac;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->logger = $logger;
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_pct_rule', 'rule_id');
    }

    /**
     * Update store view
     *
     * @param array $data
     * @param string $ruleId
     *
     * @return void
     */
    public function updateStore($data, $ruleId)
    {
        $dataInsert = [];
        foreach ($data as $storeId) {
            $dataInsert[] = [
                'rule_id'    => $ruleId,
                'store_id' => $storeId
            ];
        }
        $this->updateMultipleData('bss_pct_rule_store', $dataInsert);
    }

    /**
     * Insert Data
     * @param string $tableName
     * @param array $data
     */
    public function insertData($tableName, $data = [])
    {
        $table = $this->getTable($tableName);
        if ($table && !empty($data)) {
            $this->getConnection()->insert($table, $data);
        }
    }

    /**
     * Update database
     *
     * @param string $tableName
     * @param array $data
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
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Get Rule By Id
     * @param null $ruleId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRuleById($ruleId = null)
    {
        if ($ruleId) {
            $adapter = $this->getConnection();
            $select = $adapter->select()
                ->from($this->getMainTable())
                ->where('rule_id = ?', $ruleId);
            return $adapter->fetchRow($select);
        }
        return [];
    }

    /**
     * Delete database
     *
     * @param string $tableName
     * @param array $where
     *
     * @return void
     */
    public function deleteMultipleData($tableName, $where = [])
    {
        $table = $this->getTable($tableName);

        if ($table && !empty($where)) {
            $this->getConnection()->delete($table, $where);
        }
    }

    /**
     * Get Rule By Tab Id
     * @param int $tabId
     * @return array
     */
    public function getRuleByTabId($tabId)
    {
        $ruleId = [];
        try {
            $table = $this->getTable('bss_pct_tab_rule');
            $sql = $this->getConnection()->select()->from(
                $table,
                ['rule_id']
            )->where('tab_id = ?', $tabId);

            $ruleId = $this->getConnection()->fetchRow($sql);

        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $ruleId['rule_id'];
    }

    /**
     * @param int $productId
     * @return mixed
     */
    public function hasProductId($productId, $ruleId)
    {
        $productIds = [];
        try {
            $table = $this->getTable('bss_pct_tabs_products');
            $sql = $this->getConnection()->select()->from(
                $table,
                ['product_id']
            )->where('product_id = ?', $productId)
                ->where('rule_id = ?', $ruleId);

            $productIds = $this->getConnection()->fetchRow($sql);

        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return isset($productIds['product_id']);
    }
}
