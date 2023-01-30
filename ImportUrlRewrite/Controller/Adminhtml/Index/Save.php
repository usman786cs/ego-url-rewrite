<?php

declare(strict_types=1);

/**
 * Date: 30.01.2023
 *
 * @author M. Usman usman.786cs@gmail.com
 * @package Ego_ImportUrlRewrite
 */

namespace Ego\ImportUrlRewrite\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Ego\ImportUrlRewrite\Model\GenerateUrlRewrites;

/**
 * Class Save for Url rewrite Import
 */
class Save extends Action
{
    const REDIRECT_URL = 'egourlrewrite/index/urlrewrite';

    /**
     * @param Context $context
     * @param GenerateUrlRewrites $urlRewriteGenerator
     */
    public function __construct(
        Context $context,
        private GenerateUrlRewrites $urlRewriteGenerator
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $requestData = $this->getRequest();
        $file = $requestData->getFiles()->toArray();

        $storeId = (int)$requestData->getParam('selected_store');
        if (!isset($file['import_csv_file'])) {
            $this->messageManager->addErrorMessage('System Couldn\'t Find The Uploaded File.');
            return $resultRedirect->setPath(self::REDIRECT_URL);
        }

        if(!$this->analyseUploadedFile($file)) {
            return $resultRedirect->setPath(self::REDIRECT_URL);
        }

        $response = $this->urlRewriteGenerator->generatePermanentRewrites($file, $storeId);
        if (true === $response) {
            $this->messageManager->addSuccessMessage(__('URLs Imported Successfully.'));
            return $resultRedirect->setPath(self::REDIRECT_URL);
        } else if (is_array($response)) {
            $this->messageManager->addErrorMessage(
                __('Could Not Generate the Error Report, non Update URls are: ' . implode('', $response) . 'Because of Unique Constraints Violation')
            );
            return $resultRedirect->setPath(self::REDIRECT_URL);
        } else if ($response instanceof ResponseInterface) {
            return $response;
        }

        $this->messageManager->addErrorMessage(__('Something went wrong while generating URL rewrites.'));
        return $resultRedirect->setPath(self::REDIRECT_URL);
    }

    /**
     * @param $file
     * @return bool
     */
    private function analyseUploadedFile($file): bool
    {
        $type = $file['import_csv_file']['type'];
        $size = $file['import_csv_file']['size'];
        if ($type != "text/csv") {
            $this->messageManager->addErrorMessage(__('Only CSV File Types Are Allowed.'));
            return false;
        }
        if ($size > 2097152) {
            $this->messageManager->addErrorMessage(__('File Size Sould be Less Than 2MBs.'));
            return false;
        }
        return true;
    }
}
