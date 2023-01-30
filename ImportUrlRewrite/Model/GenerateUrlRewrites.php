<?php

declare(strict_types=1);

/**
 * Date: 30.01.2023
 *
 * @author M. Usman usman.786cs@gmail.com
 * @package Ego_ImportUrlRewrite
 */

namespace Ego\ImportUrlRewrite\Model;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\File\Csv;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Psr\Log\LoggerInterface;

/**
 * Generate permanent redirects with uploaded CSV file
 */
class GenerateUrlRewrites
{
    /**
     * @param Csv $csvProcessor
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlRewriteCollectionFactory $rewriteCollection
     * @param FileFactory $fileFactory
     * @param DirectoryList $directoryList
     * @param LoggerInterface $logger
     */
    public function __construct(
        private Csv $csvProcessor,
        private UrlRewriteFactory $urlRewriteFactory,
        private UrlRewriteCollectionFactory $rewriteCollection,
        private FileFactory $fileFactory,
        private DirectoryList $directoryList,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param $file
     * @param int $storeId
     * @return array|bool|ResponseInterface
     * @throws \Exception
     */
    public function generatePermanentRewrites(array $file, int $storeId) {
        $errors = [];
        $tempFile = $file['import_csv_file']['tmp_name'];
        $importProductRawData = $this->csvProcessor->getData($tempFile);

        foreach ($importProductRawData as $dataRow) {
            /** @var \Magento\UrlRewrite\Model\UrlRewrite $urlRewriteModel */
            $urlRewriteModel = $this->urlRewriteFactory->create();
            /** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection $rewriteCollection */
            $rewriteCollection = $this->rewriteCollection->create();

            $requestUrl = $dataRow[0];
            $targetUrl = $dataRow[1];
            if (strlen($requestUrl) === 0) {
                $errors[] = [$requestUrl, $targetUrl, 'Missing Request URl'];
            }
            $requestUrl = $this->sanitizeRequestUrl($requestUrl);
            $targetUrl = $this->sanitizeTargetUrl($targetUrl);

            $requestUrlExists = $rewriteCollection->addFieldToFilter("request_path", [$requestUrl]);
            if ($requestUrlExists->count()) {
                $errors[] = [$requestUrl, $targetUrl, 'Request URL Exists'];
                continue;
            }

            $urlRewriteModel->setRequestPath($requestUrl);
            $urlRewriteModel->setTargetPath($targetUrl);
            $urlRewriteModel->setRedirectType(301);
            $urlRewriteModel->setStoreId($storeId);
            $urlRewriteModel->getIsAutogenerated(0);
            $urlRewriteModel->setEntityType('custom');
            try {
                $urlRewriteModel->save();
            } catch (\Exception $exception) {
                $errors[] = [$requestUrl, $targetUrl, $exception->getMessage()];
                continue;
            }
        }

        if (count($errors)) {
            $response = $this->createResponseCsv($errors);
            if (null === $response) {
                return $errors;
            }
            return $response;
        }
        return true;
    }

    /**
     * @param array $missingRecords
     * @return ResponseInterface|null
     */
    private function createResponseCsv(array $missingRecords): ?ResponseInterface {
        try {
            $fileName = 'error_report_url_rewrites.csv';
            $filePath = $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;
            $this->csvProcessor->setEnclosure('"')
                ->setDelimiter(',')
                ->saveData($filePath, $missingRecords);

            return $this->fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => $fileName,
                    'rm' => true
                ],
                DirectoryList::MEDIA,
                'text/csv',
                null
            );
        } catch (\Exception $exception) {
            $this->logger->critical(
                __('Something went wrong while preparing the response CSV: ' . $exception->getMessage())
            );
            return null;
        }
    }

    /**
     * @param string $requestUrl
     * @return string
     */
    private function sanitizeRequestUrl(string $requestUrl): string {
        /**
         * Remove / from start of request URL if exists
         */
        $requestUrl = ltrim($requestUrl, '/');
        /**
         * Update request URL to remove substring after '?'
         */
        if (str_contains($requestUrl, '?')) {
            $requestUrl = substr($requestUrl, 0, strpos($requestUrl, '?'));
        }

        return $requestUrl;
    }

    /**
     * @param string $targetUrl
     * @return string
     */
    private function sanitizeTargetUrl(string $targetUrl): string {
        /**
         * If empty target URL, create redirect to HP.
         */
        if (strlen($targetUrl) === 0) {
            $targetUrl = "/";
        }
        if (strlen($targetUrl) > 1) {
            /**
             * Remove / from start of target URL if exists
             */
            $targetUrl = ltrim($targetUrl, '/');
        }

        return $targetUrl;
    }
}