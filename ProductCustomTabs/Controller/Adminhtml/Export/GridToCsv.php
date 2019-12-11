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
namespace Bss\ProductCustomTabs\Controller\Adminhtml\Export;

use Bss\ProductCustomTabs\Model\ResourceModel;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList as FileDirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class GridToCsv
 *
 * @package Bss\ProductCustomTabs\Controller\Adminhtml\Export
 */
class GridToCsv extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var
     */
    protected $varDirectory;

    /**
     * @var File
     */
    protected $io;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var ResourceModel\Tab\CollectionFactory
     */
    protected $tabCollectionFactory;

    /**
     * GridToCsv constructor.
     * @param Action\Context $context
     * @param Filter $filter
     * @param Filesystem $filesystem
     * @param RawFactory $resultRawFactory
     * @param Filesystem\Driver\File $file
     * @param File $io
     * @param FileFactory $fileFactory
     * @param ResourceModel\Tab\CollectionFactory $tabCollectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        Filesystem $filesystem,
        RawFactory $resultRawFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        File $io,
        FileFactory $fileFactory,
        ResourceModel\Tab\CollectionFactory $tabCollectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->resultRawFactory = $resultRawFactory;
        $this->io = $io;
        $this->fileFactory = $fileFactory;
        $this->tabCollectionFactory = $tabCollectionFactory;
    }

    /**
     * Export to csv
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $component = $this->filter->getComponent();
            $params = $this->getRequest()->getParams();
            $selceted  = isset($params['selected']) ? $params['selected'] : 1;

            if (!(int) $selceted) {
                $dataProvider = $component->getContext()->getDataProvider()->getData();
                $data = isset($dataProvider['items']) ? $dataProvider['items'] : [];
            } else {
                $collection = $this->filter->getCollection($this->tabCollectionFactory->create());
                foreach ($collection as $item) {
                    $data[] = $item->getData();
                }
            }

            $this->varDirectory = $this->filesystem
                ->getDirectoryWrite(FileDirectoryList::VAR_DIR);
            $dir = $this->varDirectory->getAbsolutePath('bss/export');
            $this->io->mkdir($dir, 0775);

            $outputFile = $dir . "/Bss_Tab_List" . ".csv";
            $fileName = "Bss_Tab_List" . ".csv";
            // @codingStandardsIgnoreStart
            $fileHandler = fopen($outputFile, 'w');
            $dataConvert = $this->convertData($data);
            foreach ($dataConvert as $value) {
                $this->file->filePutCsv($fileHandler, $value);
            }

            fclose($fileHandler);
            // @codingStandardsIgnoreEnd
            $this->fileFactory->create(
                $fileName,
                [
                    'type'  => "filename",
                    'value' => "bss/export/" . $fileName,
                    'rm'    => true,
                ],
                FileDirectoryList::VAR_DIR,
                'text/csv',
                null
            );
            $resultRaw = $this->resultRawFactory->create();
            return $resultRaw;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/index',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }

    /**
     * Convert data
     *
     * @param array $items
     * @return array
     */
    protected function convertData($items)
    {
        $data[0] = [
            'ID',
            'Status',
            'Tab Title',
            'Position',
            'Visible for Purchasing Customers Only'
        ];
        $row = [];
        if (!empty($items)) {
            foreach ($items as $item) {
                $row['ID'] = $item['id'];
                $row['Status'] = $item['status'];
                $row['Tab Title'] = $item['title'];
                $row['Position'] = $item['position'];
                $row['Visible for Purchasing Customers Only'] = $item['visible_customer_purchased'];
                $data[] = $row;
            }
        }

        return $data;
    }
}
