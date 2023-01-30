<?php

declare(strict_types=1);

/**
 * Date: 30.01.2023
 *
 * @author M. Usman usman.786cs@gmail.com
 * @package Ego_ImportUrlRewrite
 */

namespace Ego\ImportUrlRewrite\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class UrlRewrite extends Action implements HttpGetActionInterface
{
    const MENU_RESOURCE_ID = 'Ego_ImportUrlRewrite::ego_url_rewrite';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        protected PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::MENU_RESOURCE_ID);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::MENU_RESOURCE_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('Import URL Rewrites'));
        return $resultPage;
    }
}
