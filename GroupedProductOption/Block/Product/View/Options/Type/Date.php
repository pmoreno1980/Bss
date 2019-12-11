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

class Date extends \Magento\Catalog\Block\Product\View\Options\Type\Date
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCalendarDateHtml()
    {
        $product = $this->getProduct();
        $option = $this->getOption();
        $productId = $product->getId();
        $value = $product->getPreconfiguredValues()->getData('options/' . $option->getId() . '/date');

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();

        $calendarId = 'options_' . $productId . '_' . $this->getOption()->getId() . '_date';
        $calendarName = 'options_' . $productId . '[' . $this->getOption()->getId() . '][date]';
        $calendarClass = 'product-custom-option datetime-picker input-text';
        $calendarImage = $this->getViewFileUrl('Magento_Theme::calendar.png');
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $calendar = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Html\Date::class);
        $calendar->setId($calendarId)
            ->setName($calendarName)
            ->setClass($calendarClass)
            ->setImage($calendarImage)
            ->setDateFormat($dateFormat)
            ->setValue($value)
            ->setYearsRange($yearStart . ':' . $yearEnd);

        return $calendar->getHtml();
    }

    /**
     * @param string $name
     * @param null $value
     * @return \Magento\Framework\View\Element\BlockInterface|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        $product = $this->getProduct();
        $option = $this->getOption();
        $productId = $product->getId();
        $optionId = $option->getId();
        $this->setSkipJsReloadPrice(1);

        $selectId = 'options_' . $productId . '_' . $optionId . '_' . $name;
        $selectClass = 'product-custom-option admin__control-select datetime-picker';
        $selectName = 'options_' . $productId . '[' . $optionId . '][' . $name . ']';

        $select = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Html\Select::class);
        $select->setId($selectId)
            ->setClass($selectClass)
            ->setExtraParams()
            ->setName($selectName);

        $extraParams = 'style="width:auto"';
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-role="calendar-dropdown" data-calendar-role="' . $name . '"';
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        if ($this->getOption()->getIsRequire()) {
            $extraParams .= ' data-validate=\'{"datetime-validation": true}\'';
        }

        $select->setExtraParams($extraParams);
        if ($value === null) {
            $value = $product->getPreconfiguredValues()->getData(
                'options/' . $optionId . '/' . $name
            );
        }
        if ($value !== null) {
            $select->setValue($value);
        }

        return $select;
    }
}
