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
namespace Bss\ProductCustomTabs\Block\Adminhtml\Tab\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('tab_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('TAB INFORMATION'));
    }

    /**
     * Before to html
     *
     * @return \Magento\Backend\Block\Widget\Tabs
     */
    protected function _beforeToHtml()
    {
        try {
            $this->addTab(
                'main',
                [
                    'label' => __('Edit Tab'),
                    'title' => __('Edit Tab'),
                    'content' => $this->getChildHtml('main'),
                    'active' => true
                ]
            );

            $this->addTab(
                'conditions',
                [
                    'label' => __('Assign to Products'),
                    'title' => __('Assign to Products'),
                    'content' => $this->getLayout()->createBlock(
                        \Bss\ProductCustomTabs\Block\Adminhtml\Tab\Edit\Tab\Conditions::class
                    )->toHtml(),
                ]
            );
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }

        return parent::_beforeToHtml();
    }
}
