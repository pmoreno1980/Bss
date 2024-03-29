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
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Observer;

use Magento\Framework\Event\ObserverInterface;

class RedirectConfigurableProduct implements ObserverInterface
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\UrlIdentifier
     */
    private $urlIdentifier;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $moduleConfig;

    /**
     * RedirectConfigurableProduct constructor.
     * @param \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\UrlIdentifier $urlIdentifier,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->urlIdentifier = $urlIdentifier;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getData('request');
        if ($this->moduleConfig->isModuleEnable() && $this->moduleConfig->customUrl()) {
            $redirectUrl = $this->urlIdentifier->readUrl($request->getOriginalPathInfo());
            if ($redirectUrl['product'] != '0') {
                $controllerRequest = $observer->getData('controller_action')->getRequest();
                $controllerRequest->initForward();
                $params = ['id' => $redirectUrl['product'], 'category' => $redirectUrl['category']];
                $controllerRequest->setParams($params);
                $controllerRequest->setModuleName('catalog');
                $controllerRequest->setControllerName('product');
                $controllerRequest->setActionName('view');
                $controllerRequest->setDispatched(false);
            }
        }
    }
}
