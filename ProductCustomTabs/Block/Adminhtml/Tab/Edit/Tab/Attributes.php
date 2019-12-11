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
namespace Bss\ProductCustomTabs\Block\Adminhtml\Tab\Edit\Tab;

use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Attributes extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * Eav Attribute
     *
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * CollectionFactory
     *
     * @var CollectionFactory Collection Factory $productCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Attribute Collection Factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * Request
     *
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $request;

    /**
     * Model Tab Factory
     *
     * @var \Bss\ProductCustomTabs\Model\TabFactory $modelTabFactorys
     */
    protected $modelTabFactory;

    /**
     * Attributes constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Attribute $eavAttribute
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\ProductCustomTabs\Model\TabFactory $modelTabFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        CollectionFactory $productCollectionFactory,
        Attribute $eavAttribute,
        \Magento\Framework\App\Request\Http $request,
        \Bss\ProductCustomTabs\Model\TabFactory $modelTabFactory,
        array $data = []
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavAttribute = $eavAttribute;
        $this->request = $request;
        $this->modelTabFactory = $modelTabFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attributesGrid');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * Prepare Collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToSelect('attribute_id');
        $collection->addFieldToSelect('attribute_code');
        $collection->addFieldToSelect('frontend_label');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Filter column default
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {

        // Set custom filter for in category flag
        if ($column->getId() == 'in_attributes') {
            $attributeIds = $this->_getSelectedAttribute();
            if (empty($attributeIds)) {
                $attributeIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('additional_table.attribute_id', ['in' => $attributeIds]);
            } elseif (!empty($attributeIds)) {
                $this->getCollection()->addFieldToFilter('additional_table.attribute_id', ['nin' => $attributeIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare Columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_attributes',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_attributes',
                'align' => 'center',
                'index' => 'additional_table.attribute_id',
                'values' => $this->_getSelectedAttribute(),
            ]
        );

        $this->addColumn(
            'attribute_code',
            [
                'header' => __('Attribute Code'),
                'index' => 'attribute_code',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'frontend_label',
            [
                'header' => __('Label'),
                'index' => 'frontend_label',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'hidden',
                'index' => 'position',
                'default' => '1',
                'editable' => true,
                'column_css_class'=>'no-display',
                'header_css_class'=>'no-display'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get Selected Attribute
     *
     * @return array
     */
    public function getSelectedAttribute()
    {

        $arrayAttributesSelected = [];
        $id = $this->request->getParam('id');

        if ($id) {
            $tabEdit = $this->modelTabFactory->create()->load($id);

            $arrayAttributesSelected = $tabEdit->getArrAttributeIds();
        }

        return $arrayAttributesSelected;
    }

    /**
     * Get Selected Products
     *
     * @return array
     */
    protected function _getSelectedAttribute()
    {
        $arrayAttributesSelected = [];
        $id = $this->request->getParam('id');

        if ($id) {
            $tabEdit = $this->modelTabFactory->create()->load($id);
            $arrayAttributesSelected = $tabEdit->getArrAttributeIds();
        }

        return $arrayAttributesSelected;
    }

    /**
     * Get Grid Url
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridAttributes', ['_current' => true]);
    }

    /**
     * Return url row when click
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Can Show Tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is Hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return true;
    }
}
