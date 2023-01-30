<?php

declare(strict_types=1);

/**
 * Date: 30.01.2023
 *
 * @author M. Usman usman.786cs@gmail.com
 * @package Ego_ImportUrlRewrite
 */

namespace Ego\ImportUrlRewrite\Block\Adminhtml;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Backend\Block\Template;
use Magento\Store\Api\StoreRepositoryInterface;

class UrlRewrites extends Template
{
    private StoreRepositoryInterface $storeRepository;

    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param Template\Context $context
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        Template\Context $context, array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->storeRepository = $storeRepository;
    }

    /**
     * @return array
     */
    public function getAvailableStores(): array {
        /** @var \Magento\Store\Api\Data\StoreInterface[] $stores */
        $stores = $this->storeRepository->getList();

        $availableStores = [];
        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if (0 == $storeId) {
                continue;
            }

            $availableStores[$store->getId()] = $store->getName();
        }
        return $availableStores;
    }
}
