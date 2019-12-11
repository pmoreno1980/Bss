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
namespace Bss\ProductCustomTabs\Model\ResourceModel\Tab;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
   /**
    * Id Field Name
    *
    * @var string
    */
    protected $_idFieldName = 'id';

    /**
     * Constructor
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Bss\ProductCustomTabs\Model\Tab::class,
            \Bss\ProductCustomTabs\Model\ResourceModel\Tab::class
        );
        $this->_map['fields']['id'] = 'id';
    }

    /**
     * Init Select
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _initSelect()
    {
        $this->getSelect()->from(
            [
                'bss_main_table' => $this->getResource()->getMainTable()
                ,
                [
                    '*'
                ]
            ]
        )->join(
            ['bptr' => $this->getResource()->getTable('bss_pct_tab_rule')],
            'bss_main_table.id = bptr.tab_id',
            ['bptr.rule_id']
        );

        return $this;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function filterTabByStore($storeId)
    {
        $this->getSelect()->where('store_id = ?', $storeId);
        return $this;
    }

    /**
     * Retrieve all ids for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->getClearSelect();
        $idsSelect->columns('bss_main_table.' .'id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve clear select
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getClearSelect()
    {
        return $this->buildClearSelect();
    }

    /**
     * Build clear select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function buildClearSelect($select = null)
    {
        if (null === $select) {
            $select = clone $this->getSelect();
        }
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);

        return $select;
    }
}
