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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_GroupedProductOption
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GroupedProductOption\Block\Product\View\Options\Type;

use Bss\GroupedProductOption\Helper\Data;

class Select extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $pricingHelper;

    /**
     * Select constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Data $groupedProductHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        Data $groupedProductHelper,
        array $data = []
    ) {
        $this->pricingHelper = $pricingHelper;
        parent::__construct(
            $context,
            $pricingHelper,
            $catalogData,
            $data
        );
    }

    /**
     * @return string
     */
    public function getValuesHtml()
    {
        $option = $this->getOption();
        $product = $this->getProduct();
        $productId = $product->getId();
        $optionType = $option->getType();
        $html = '';
        $optionId = $option->getId();
        $configValue = $product->getPreconfiguredValues()->getData('options/' . $optionId);
        $store = $product->getStore();

        $this->setSkipJsReloadPrice(1);

        if ($optionType == Data::OPTION_TYPE_DROP_DOWN || $optionType == Data::OPTION_TYPE_MULTIPLE) {
            $html = $this->_getDropdownMultipleHtml($option, $configValue, $productId, $optionId, $optionType, $store);
        }

        if ($optionType == Data::OPTION_TYPE_RADIO || $optionType == Data::OPTION_TYPE_CHECKBOX) {
            $html = $this->_getRadioCheckboxHtml($option, $configValue, $productId, $optionId, $optionType, $store);
        }
        return $html;
    }

    /**
     * @param $option
     * @param $configValue
     * @param $productId
     * @param $optionId
     * @param $optionType
     * @param $store
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getDropdownMultipleHtml($option, $configValue, $productId, $optionId, $optionType, $store)
    {
        $require = $option->getIsRequire() ? ' required' : '';
        $extraParams = '';
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => 'select_' . $productId . '-' . $optionId,
                'class' => $require . ' product-custom-option admin__control-select'
            ]
        );
        if ($optionType == Data::OPTION_TYPE_DROP_DOWN) {
            $select->setName(
                'options_' . $productId . '[' . $optionId . ']'
            )->addOption(
                '',
                __('-- Please Select --')
            );
        } else {
            $select->setName(
                'options_' . $productId . '[' . $optionId . '][]'
            );
            $select->setClass(
                'multiselect admin__control-multiselect' . $require . ' product-custom-option'
            );
        }
        $optionValues = $option->getValues();
        foreach ($optionValues as $value) {
            $priceType = $value->getPriceType();
            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $priceType == 'percent',
                    'pricing_value' => $value->getPrice($priceType == 'percent'),
                ],
                false
            );
            $select->addOption(
                $value->getOptionTypeId(),
                $value->getTitle() . ' ' . strip_tags($priceStr) . '',
                [
                    'price' => $this->pricingHelper->currencyByStore($value->getPrice(true), $store, false)
                ]
            );
        }
        if ($optionType == Data::OPTION_TYPE_MULTIPLE) {
            $extraParams = ' multiple="multiple"';
        }
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);

        if ($configValue) {
            $select->setValue($configValue);
        }

        return $select->getHtml();
    }

    /**
     * @param $option
     * @param $configValue
     * @param $productId
     * @param $optionId
     * @param $optionType
     * @param $store
     * @return string
     */
    private function _getRadioCheckboxHtml($option, $configValue, $productId, $optionId, $optionType, $store)
    {
        $type = '';
        $class = '';
        $selectHtml = '<div class="options-list nested" id="options-'
            . $productId . '-' . $optionId . '-list">';
        $require = $option->getIsRequire() ? ' required' : '';
        $arraySign = '';
        switch ($optionType) {
            case Data::OPTION_TYPE_RADIO:
                $type = 'radio';
                $class = 'radio admin__control-radio';
                if (!$option->getIsRequire()) {
                    $selectHtml .= '<div class="field choice admin__field admin__field-option">';
                    $selectHtml .= '<input type="radio"';
                    $selectHtml .= ' id="options_' . $productId . '_' . $optionId . '"';
                    $selectHtml .= ' class="' .  $class . ' product-custom-option"';
                    $selectHtml .= ' name="options_' . $productId . '[' .  $optionId . ']"';
                    $selectHtml .= ' data-selector="options[' . $productId . '][' . $optionId . ']"';
                    if (!$this->getSkipJsReloadPrice()) {
                        $selectHtml .= ' onclick="opConfig.reloadPrice()"';
                    }
                    $selectHtml .= ' value="" checked="checked" />';
                    $selectHtml .= '<label class="label admin__field-label"';
                    $selectHtml .= ' for="options_' . $productId . '_'. $optionId . '">';
                    $selectHtml .= '<span>' . __('None') . '</span>';
                    $selectHtml .= '</label>';
                    $selectHtml .= '</div>';
                }
                break;
            case Data::OPTION_TYPE_CHECKBOX:
                $type = 'checkbox';
                $class = 'checkbox admin__control-checkbox';
                $arraySign = '[]';
                break;
        }
        $count = 1;
        $optionValues = $option->getValues();
        foreach ($optionValues as $value) {
            $count++;
            $priceType = $value->getPriceType();
            $priceAmount = $this->pricingHelper->currencyByStore($value->getPrice(true), $store, false);
            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $priceType == 'percent',
                    'pricing_value' => $value->getPrice($priceType == 'percent'),
                ]
            );

            $htmlValue = $value->getOptionTypeId();
            if ($arraySign) {
                $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
            } else {
                $checked = $configValue == $htmlValue ? 'checked' : '';
            }

            $dataSelector = 'options' . $productId . '[' . $optionId . ']';
            if ($arraySign) {
                $dataSelector .= '[' . $htmlValue . ']';
            }

            $selectHtml .= '<div class="field choice admin__field admin__field-option' . $require . '">';
            $selectHtml .= '<input type="' . $type . '" class="' . $class ;
            $selectHtml .= ' ' . $require . ' product-custom-option"';
            if (!$this->getSkipJsReloadPrice()) {
                $selectHtml .= ' onclick="opConfig.reloadPrice()"';
            }
            $selectHtml .= ' name="options_' . $productId . '[' . $optionId . ']' . $arraySign . '"';
            $selectHtml .= ' id="options_' . $productId . '_'. $optionId . '_' . $count . '"';
            $selectHtml .= ' value="' . $htmlValue . '"';
            $selectHtml .= ' ' . $checked . '';
            $selectHtml .= ' data-selector="' . $dataSelector . '"';
            $selectHtml .= ' price="' . $priceAmount . '" />';
            $selectHtml .= '<label class="label admin__field-label"';
            $selectHtml .= ' for="options_' . $productId . '_' . $optionId . '_' . $count . '">';
            $selectHtml .= '<span>' . $value->getTitle() . '</span> ';
            $selectHtml .= $priceStr;
            $selectHtml .= '</label>';
            $selectHtml .= '</div>';
        }
        $selectHtml .= '</div>';

        return $selectHtml;
    }
}
