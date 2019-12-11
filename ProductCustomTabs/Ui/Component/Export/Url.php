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
namespace Bss\ProductCustomTabs\Ui\Component\Export;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\ExportButton;
use \Magento\Framework\App\Request\Http;

class Url extends ExportButton
{

    /**
     * URL_EXPORT_CSV
     */
    const URL_EXPORT_CSV = 'product_custom_tabs/export/gridToCsv';

    /**
     * URL_EXPORT_XML
     */
    const URL_EXPORT_XML = 'product_custom_tabs/export/gridToXml';

    /**
     * Http Request
     *
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
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param Http $request
     * @param array $components
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        Http $request,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($context, $urlBuilder, $components, $data);
    }

    /**
     * Get Configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        $storeId = $this->request->getParam('store');

        $config = parent::getConfiguration();

        $config['options'][0]['url'] = $this->urlBuilder->getUrl(self::URL_EXPORT_CSV, [
            'store' => $storeId
        ]);
        $config['options'][1]['url'] = $this->urlBuilder->getUrl(self::URL_EXPORT_XML, [
            'store' => $storeId
        ]);
        return $config;
    }
}
