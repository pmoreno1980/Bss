<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_SizeChart
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductCustomTabs\Observer\Product\RelatedBlock;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Remove implements ObserverInterface
{

    /**
     * Config Admin
     *
     * @var \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin
     */
    protected $helperConfigAdmin;

    /**
     * Remove constructor.
     *
     * @param \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin
     *
     * @return void
     */
    public function __construct(
        \Bss\ProductCustomTabs\Helper\Config\ConfigAdmin $helperConfigAdmin
    ) {

        $this->helperConfigAdmin = $helperConfigAdmin;
    }

    /**
     * Remove Action
     *
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {

        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();

        if ($this->helperConfigAdmin->getConfigShowRelatedProduct() == 0 &&
            $this->helperConfigAdmin->getConfigEnableModule() == 1
        ) {
            $blockRelated = $layout->getBlock('catalog.product.related');
            if (isset($blockRelated)) {
                $layout->unsetElement('catalog.product.related');
            }

            $blockUpsell = $layout->getBlock('product.info.upsell');
            if (isset($blockUpsell)) {
                $layout->unsetElement('product.info.upsell');
            }

        }
    }
}
