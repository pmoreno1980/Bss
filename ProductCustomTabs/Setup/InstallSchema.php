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
namespace Bss\ProductCustomTabs\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\App\ProductMetadataInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * Product Metadata Interface
     *
     * @var ProductMetadataInterface $version
     */
    protected $version;

    /**
     * InstallSchema constructor.
     *
     * @param ProductMetadataInterface $version
     *
     * @return void
     */
    public function __construct(ProductMetadataInterface $version)
    {
        $this->version = $version;
    }

    /**
     * Get magento version
     *
     * @return string
     */
    protected function getMagentoVersion()
    {
        return $this->version->getVersion();
    }

    /**
     * Conpare Version
     *
     * @param string $version
     * @return bool
     */
    protected function compareVersion($version)
    {
        $versionCurrent = $this->getMagentoVersion();
        if (version_compare($versionCurrent, $version) === 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Install
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        /**
         * Create table 'bss_product_custom_tabs'
         */
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bss_product_custom_tabs')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Tab ID'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Status'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Title Tab'
        )->addColumn(
            'is_default',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Default Tab'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Default Id'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => false, 'default' => ''],
            'Content Tab'
        )->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Position Tab'
        )->addColumn(
            'visible_customer_purchased',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Visible Customer Purchased'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store View Id'
        )->addColumn(
            'attribute_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => false, 'default' => ''],
            'Attributes Of Tab'
        )->addColumn(
            'show_related_products',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Show Related Products'
        )->addColumn(
            'show_up_sell_products',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Show Up Sell Products'
        )->addColumn(
            'show_cross_sell_products',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Show Cross Sell Products'
        )->addIndex(
            $installer->getIdxName('bss_product_custom_tabs', ['id']),
            ['id']
        )->setComment("Product Custom Tabs Table");
        $installer->getConnection()->createTable($table);
        $this->createTableRule($installer);
        $this->createTableCustomerGroup($installer);
        $installer->endSetup();
    }

    /**
     * Create Table Related Customer Group
     * @param $installer
     */
    public function createTableCustomerGroup($installer)
    {
        /**
         * Create table 'bss_pct_tab_customer_group'
         */
        $customerGroupsTable = $installer->getTable('customer_group');
        $tabCustomerGroupsTable = $installer->getTable('bss_pct_tab_customer_group');
        if ($this->compareVersion('2.2.0')) {
            $typeCustomerGroupId = \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;
        } else {
            $typeCustomerGroupId = \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT;
        }
        if (!$installer->tableExists('bss_pct_tab_customer_group')) {
            $table = $installer->getConnection()->newTable(
                $tabCustomerGroupsTable
            )->addColumn(
                'tab_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Tab Id'
            )->addColumn(
                'customer_group_id',
                $typeCustomerGroupId,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )->addIndex(
                $installer->getIdxName('bss_pct_rule_customer_group', ['customer_group_id']),
                ['customer_group_id']
            )->addForeignKey(
                $installer->getFkName(
                    'bss_pct_tab_customer_group',
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'bss_pct_tab_customer_group',
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $customerGroupsTable,
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Bss Product Custom Tabs Rules Customer Groups Table'
            );
            $installer->getConnection()->createTable($table);
        }
    }

    /**
     * Create Table related rule
     * @param $installer
     */
    public function createTableRule($installer)
    {
        /**
         * Create table 'bss_pct_rule'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bss_pct_rule')
        )->addColumn(
            'rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'conditions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Conditions Serialized'
        )->addIndex(
            $installer->getIdxName('bss_pct_rule', ['rule_id']),
            ['rule_id']
        )->setComment(
            'Bss Product Custom Tabs Rules Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'bss_pct_tab_rule'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bss_pct_tab_rule')
        )->addColumn(
            'rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Rule Id'
        )->addColumn(
            'tab_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Tab Id'
        )->addIndex(
            $installer->getIdxName(
                'bss_pct_tab_rule',
                ['rule_id']
            ),
            ['rule_id']
        )->setComment(
            'Bss Product Custom Tabs Tabs Rules Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table bss_pct_tabs_products
         */
        if (!$installer->tableExists('bss_pct_tabs_products')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('bss_pct_tabs_products')
            )->addColumn(
                'tab_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false],
                'Tab Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Rule Id'
            )->addIndex(
                $installer->getIdxName(
                    'bss_pct_tabs_products',
                    ['tab_id', 'product_id', 'rule_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['tab_id', 'product_id', 'rule_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment(
                'Bss Product Custom Tabs Tabs Products Table'
            );
            $installer->getConnection()->createTable($table);
        }
    }
}
