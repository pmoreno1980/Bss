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

namespace Bss\ProductCustomTabs\Model;

class Rule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $productIds;

    /**
     * Store matched product Ids with rule id
     *
     * @var array
     */
    private $dataProductIds;

    /**
     * Rule matched Tab id
     * @var array
     */
    private $dataRuleTab;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $resourceIterator;

    /**
     * @var \Bss\ProductCustomTabs\Helper\Rule
     */
    protected $helperRule;

    /**
     * Rule constructor.
     * @param \Bss\ProductCustomTabs\Helper\Rule $helperRule
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @return void
     */
    public function __construct(
        \Bss\ProductCustomTabs\Helper\Rule $helperRule,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperRule = $helperRule;
        $this->resourceIterator = $resourceIterator;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Bss\ProductCustomTabs\Model\ResourceModel\Rule::class);
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get Conditions Instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->helperRule->getCondCombine();
    }

    /**
     * Get Actions Instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getActionsInstance()
    {
        return $this->helperRule->getCondCombine();
    }

    /**
     * Get Conditions Field Set Id
     *
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get Actions Field Set Id
     * @param string $formName
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    /**
     * After Save
     *
     * @return \Magento\Rule\Model\AbstractModel
     *
     * @throws \Exception
     */
    public function afterSave()
    {
        try {
            $data = $this->_registry->registry('bss_pct_rule_update_data');
            $ruleId = $this->getRuleId();
            $warningMessage = true;
            $this->dataRuleTab[] = ['rule_id' => $ruleId, 'tab_id' => $data['tab_id']];
            $this->getMatchingProductIds();
            $whereRule = ['rule_id = ?' => $ruleId];
            $productsTable = $this->getResource()->getTable('bss_pct_tabs_products');
            $ruleTable = $this->getResource()->getTable('bss_pct_tab_rule');
            $this->getResource()->deleteMultipleData($productsTable, $whereRule);
            $this->getResource()->deleteMultipleData($ruleTable, $whereRule);
            $this->getResource()->updateMultipleData($ruleTable, $this->dataRuleTab);
            if (!empty($this->dataProductIds) && is_array($this->dataProductIds)) {
                $this->getResource()->updateMultipleData($productsTable, $this->dataProductIds);
                $warningMessage = false;
            }
            $this->_registry->unregister('bss_warning_message');
            $this->_registry->register('bss_warning_message', $warningMessage);
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }

        return parent::afterSave();
    }

    /**
     * Get Matching Product Ids
     *
     * @return array|null
     *
     * @throws \Exception
     */
    public function getMatchingProductIds()
    {
        try {
            if ($this->productIds === null) {
                $this->productIds = [];
                $this->setCollectedAttributes([]);

                /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $productCollection = $this->helperRule->getCollectionProductFactory();
                $productCollection->addAttributeToFilter(
                    'status',
                    [
                        'in' => $this->helperRule->getProductStatus()->getVisibleStatusIds()
                    ]
                );

                $productCollection->setVisibility($this->helperRule->getProductVisibility()->getVisibleInSiteIds());
                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->resourceIterator->walk(
                    $productCollection->getSelect(),
                    [
                        [
                            $this,
                            'callbackValidateProduct'
                        ]
                    ],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->helperRule->getProductFactory()
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }

        return $this->productIds;
    }

    /**
     * Callback Validate Product
     *
     * @param array $args
     *
     * @return void
     *
     * @throws \Exception
     */
    public function callbackValidateProduct($args)
    {
        try {
            $data = $this->_registry->registry('bss_pct_rule_update_data');
            $product = clone $args['product'];
            $product->setData($args['row']);

            $ruleId = $this->getRuleId();

            if ($ruleId && $this->getConditions()->validate($product)) {
                $this->productIds[] = $product->getId();
                $this->dataProductIds[] = [
                    'rule_id' => $ruleId,
                    'product_id' => $product->getId(),
                    'tab_id' => $data['tab_id']
                ];

            }
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
    }
}
