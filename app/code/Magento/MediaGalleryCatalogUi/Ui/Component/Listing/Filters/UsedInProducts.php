<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryCatalogUi\Ui\Component\Listing\Filters;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Used in products filter
 */
class UsedInProducts extends Select
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param OptionSourceInterface $optionsProvider
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param ProductRepositoryInterface $productRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        OptionSourceInterface $optionsProvider = null,
        BookmarkManagementInterface $bookmarkManagement,
        ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->filterBuilder = $filterBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $filterBuilder,
            $filterModifier,
            $optionsProvider,
            $components,
            $data
        );
        $this->bookmarkManagement = $bookmarkManagement;
        $this->productRepository = $productRepository;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $options = [];
        $productIds = [];
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            $this->context->getNameSpace()
        );
        if ($bookmark === null) {
            parent::prepare();
            return;
        }

        $applied = $bookmark->getConfig()['current']['filters']['applied'];

        if (isset($applied[$this->getName()])) {
            $productIds = $applied[$this->getName()];
        }

        foreach ($productIds as $id) {
            try {
                $product = $this->productRepository->getById($id);
                $options[] = [
                    'value' => $id,
                    'label' => $product->getName(),
                    'is_active' => $product->getStatus(),
                    'path' => $product->getSku(),
                    'optgroup' => false
                ];
            } catch (\Exception $e) {
                continue;
            }
        }
        $this->optionsProvider = $options;
        parent::prepare();
    }
}
