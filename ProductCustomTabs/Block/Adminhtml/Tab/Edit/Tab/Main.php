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

use \Magento\Backend\Block\Widget\Form\Generic;
use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Customer\Model\ResourceModel\Group\Collection;

class Main extends Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * Store
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Yes no
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $status;

    /**
     * Product Factory
     *
     * @var \Magento\Catalog\Model\ProductFactory;
     */
    protected $productFactory;

    /**
     * Wysiwyg
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     */
    protected $wysiwygConfig;

    /**
     * Collection Group Customer
     *
     * @var Collection $customerGroup
     */
    protected $customerGroup;

    /**
     * Resource Tab
     *
     * @var \Bss\ProductCustomTabs\Model\ResourceModel\Tab $resourceTab
     */
    protected $resourceTab;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param Collection $customerGroup
     * @param \Bss\ProductCustomTabs\Model\ResourceModel\Tab $resourceTab
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        Collection $customerGroup,
        \Bss\ProductCustomTabs\Model\ResourceModel\Tab $resourceTab,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->status = $yesno;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->customerGroup = $customerGroup;
        $this->resourceTab = $resourceTab;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('tab');
        $isElementDisabled = false;
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Edit Tab'),
                'collapsable'  => true
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Active'),
                'title' => __('Active'),
                'name' => 'status',
                'required' => true,
                'options' => $this->status->toArray(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'content',
            'editor',
            [
                'name'  => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'style' => 'height:24em;',
                'wysiwyg'   => true,
                'required' => true,
                'force_load' => true,
                'config' => $this->wysiwygConfig->getConfig($this->getWysiwygConfig())
            ]
        );
        $fieldset->addField(
            'group_customer_ids',
            'multiselect',
            [
                'name' => 'group_customer_ids',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required' => true,
                'values' => $this->customerGroup->toOptionArray()
            ]
        );
        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'position_tab',
                'label' => __('Position'),
                'title' => __('Position'),
                'required' => true,
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'visible_customer_purchased',
            'select',
            [
                'label' => __('Visible for Purchasing Customers Only'),
                'title' => __('Visible for Purchasing Customers Only'),
                'name' => 'visible_customer_purchased',
                'required' => true,
                'options' => $this->status->toArray()
            ]
        );
        $fieldset->addField('store_id', 'hidden', ['name' => 'store_id']);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $formData = $this->getFormData($model);
        $this->addFieldSetRelatedProduct($form);
        $this->addFieldSetAttributes($form);
        $form->setValues($formData);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @param mixed $model
     * @return array
     */
    protected function getFormData($model)
    {
        $formData = $model->getData();
        $formData['store_id'] = $this->getStoreIdInUrl();
        if ($model->getId()) {
            $formData['group_customer_ids'] = $this->getCustomerGroupIds($model->getId());
        }
        return $formData;
    }

    /**
     * @return array
     */
    protected function getWysiwygConfig()
    {
        $configWysiwyg['document_base_url'] = $this->getData('store_media_url');
        $configWysiwyg['store_id'] = $this->getData('store_id');
        $configWysiwyg['add_variables'] = true;
        $configWysiwyg['add_widgets'] = true;
        $configWysiwyg['add_directives'] = true;
        $configWysiwyg['use_container'] = true;
        $configWysiwyg['container_class'] = 'hor-scroll';
        return $configWysiwyg;
    }

    /**
     * @param mixed $form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addFieldSetAttributes(&$form)
    {
        /*Add fieldset assign attributes*/
        $fieldsetAttributes = $form->addFieldset(
            'attributes_fieldset',
            [
                'legend' => __('Assign Attributes'),
                'label' => __('Assign Attributes'),
                'collapsable'  => true
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            \Bss\ProductCustomTabs\Block\Adminhtml\Tab\Edit\Tab\Attributes\AssignAttributes::class
        );
        $fieldsetAttributes->addField(
            'attribute_field',
            'text',
            [
                'name' => 'attribute_field'
            ]
        )->setRenderer($renderer);
    }

    /**
     * Add FieldSet Related Product
     * @param mixed $form
     */
    protected function addFieldSetRelatedProduct(&$form)
    {
        $fieldsetRelatedProduct = $form->addFieldset(
            'related_product_tab',
            [
                'legend' => __('Related, Up-sell, Cross-Sell'),
                'label' => __('Related, Up-sell, Cross-Sell'),
                'class'     => 'fieldset-wide',
                'collapsable'  => true
            ]
        );
        $fieldsetRelatedProduct->addField(
            'show_related_products',
            'select',
            [
                'label' => __('Show Related Products'),
                'title' => __('Show Related Products'),
                'name' => 'show_related_products',
                'required' => true,
                'options' => $this->status->toArray()
            ]
        );

        $fieldsetRelatedProduct->addField(
            'show_up_sell_products',
            'select',
            [
                'label' => __('Show Up-sell Products'),
                'title' => __('Show Up-sell Products'),
                'name' => 'show_up_sell_products',
                'required' => true,
                'options' => $this->status->toArray()
            ]
        );

        $fieldsetRelatedProduct->addField(
            'show_cross_sell_products',
            'select',
            [
                'label' => __('Show Cross-sell Products'),
                'title' => __('Show Cross-sell Products'),
                'name' => 'show_cross_sell_products',
                'required' => true,
                'options' => $this->status->toArray()
            ]
        );
    }

    /**
     * Get Customer Group Ids
     *
     * @param int $tabId
     * @return string
     */
    public function getCustomerGroupIds($tabId)
    {
        $dataCustomerGroupIds = $this->resourceTab->getCustomerGroupTable($tabId);
        return implode(",", $dataCustomerGroupIds);
    }

    /**
     * Check param  tab_id on URL if isset return true else return false
     *
     * @return bool
     */
    protected function checkAddOrEdit()
    {
        $idTab = $this->getRequest()->getParam("id");
        if (isset($idTab)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Store Id In Url
     *
     * @return string
     */
    protected function getStoreIdInUrl()
    {
        return $this->getRequest()->getParam('store') ? $this->getRequest()->getParam('store') : 0;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Edit Tab');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Edit Tab');
    }

    /**
     * Can Show Tab
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is Hidden
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
