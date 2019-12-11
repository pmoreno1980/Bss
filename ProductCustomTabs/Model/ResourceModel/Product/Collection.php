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
namespace Bss\ProductCustomTabs\Model\ResourceModel\Product;

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
            ['bptp' => $this->getResource()->getTable('bss_pct_tabs_products')],
            'bss_main_table.id = bptp.tab_id',
            ['bptp.rule_id']
        );

        return $this;
    }
}
