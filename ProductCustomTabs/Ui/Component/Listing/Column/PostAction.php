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
namespace Bss\ProductCustomTabs\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Bss\ProductCustomTabs\Block\Adminhtml\Tab\Grid\Renderer\Action\UrlBuilder;
use \Magento\Framework\App\Request\Http;

class PostAction extends Column
{
    /**
     * const URL_PATH_EDIT
     */
    const URL_PATH_EDIT = 'product_custom_tabs/tab/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Http
     * @var Http $request
     */
    protected $request;

    /**
     * PostAction constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $urlBuilder
     * @param Http $request
     * @param array $components
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $urlBuilder,
        Http $request,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $storeId = $this->request->get('store');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, [
                            'id' => $item['id'],
                            'store' => $storeId
                        ]),
                        'label' => __('Edit')
                    ];
                }
            }
        }
        return $dataSource;
    }
}
