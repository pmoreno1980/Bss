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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use \Bss\ProductCustomTabs\Model\TabFactory;
use Bss\ProductCustomTabs\Model\ResourceModel;

/**
 * Tab edit form main tab
 */
class Conditions extends Generic implements TabInterface
{

    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * Conditions
     *
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * RuleFactory
     *
     * @var \Bss\ProductCustomTabs\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * TabFactory
     *
     * @var TabFactory $tabFactory
     */
    protected $tabFactory;

    /**
     * ResourceModel\Rule
     *
     * @var ResourceModel\Rule $resourceRule
     */
    protected $resourceRule;

    /**
     * Conditions constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param TabFactory $tabFactory
     * @param \Bss\ProductCustomTabs\Model\RuleFactory $ruleFactory
     * @param ResourceModel\Rule $resourceRule
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        TabFactory $tabFactory,
        \Bss\ProductCustomTabs\Model\RuleFactory $ruleFactory,
        ResourceModel\Rule $resourceRule,
        array $data = []
    ) {
        $this->resourceRule = $resourceRule;
        $this->ruleFactory = $ruleFactory;
        $this->tabFactory = $tabFactory;
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Get Tab Class
     *
     * @return null
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Get Tab Label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Get Tab Title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Conditions');
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
        return false;
    }

    /**
     * Prepare Form
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        //Get Tab
        $modelTab = $this->_coreRegistry->registry('tab');
        $model  = $this->ruleFactory->create();

        //Load Rule
        if ($modelTab->getId()) {
            //Get Rule Id By Tab Id
            $ruleId = $this->resourceRule->getRuleByTabId($modelTab->getId());

            if ($ruleId) {
                $model = $model->load($ruleId);
            }
        }

        $fieldsetId = 'conditions_fieldset';
        $formName = 'catalog_rule_form';

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'catalog_rule/promo_catalog/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        $renderer = $this->rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($model->getConditionsFieldSetId($formName));

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Apply the rule only if the following conditions are met (leave blank for all products).'
                )
            ]
        )->setRenderer(
            $renderer
        );
        $model->getConditions()->setJsFormObject($conditionsFieldSetId);
        $fieldset->addField(
            'conditions_serialized',
            'text',
            [
                'name' => 'conditions_serialized',
                'label' => __('Conditions'),
                'title' => __('Conditions')
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->conditions
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
