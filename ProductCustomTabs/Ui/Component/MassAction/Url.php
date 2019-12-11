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
namespace Bss\ProductCustomTabs\Ui\Component\MassAction;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\MassAction;
use \Magento\Framework\App\Request\Http;

class Url extends MassAction
{

    /**
     * const CONFIG_TYPE_DELETE
     */
    const CONFIG_TYPE_DELETE = 'delete';

    /**
     * const CONFIG_TYPE_ENABLE
     */
    const CONFIG_TYPE_ENABLE = 'enable';

    /**
     * const CONFIG_TYPE_DISABLE
     */
    const CONFIG_TYPE_DISABLE = 'disable';

    /**
     * const URL_PATH_EDIT
     */
    const URL_MASSDELETE = 'product_custom_tabs/tab/massDelete';

    /**
     * const URL_MASSENABLE
     */
    const URL_MASSENABLE = 'product_custom_tabs/tab/massEnable';

    /**
     * const URL_MASSDISABLE
     */
    const URL_MASSDISABLE = 'product_custom_tabs/tab/massDisable';

    /**
     * Http
     * @var Http $request
     */
    protected $request;

    /**
     * UrlInterface
     *
     * @var UrlInterface $urlBuilder
     */
    protected $urlBuilder;

    /**
     * Url constructor.
     *
     * @param Http $request
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        Http $request,
        UrlInterface $urlBuilder,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $components, $data);
    }

    /**
     * Get Configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        $storeId = $this->request->get('store');
        $config = parent::getConfiguration();
        $url = '';
        if ($config['type']) {
            switch ($config['type']) {
                case self::CONFIG_TYPE_DELETE:
                    $url = self::URL_MASSDELETE;
                    break;
                case self::CONFIG_TYPE_ENABLE:
                    $url = self::URL_MASSENABLE;
                    break;
                case self::CONFIG_TYPE_DISABLE:
                    $url = self::URL_MASSDISABLE;
                    break;
            }
        }

        if ($url) {
            $config['url'] = $this->urlBuilder->getUrl($url, [
                'store' => $storeId
            ]);
        }

        return $config;
    }
}
