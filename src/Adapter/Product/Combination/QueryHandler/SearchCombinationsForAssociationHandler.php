<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Product\Combination\QueryHandler;

use PrestaShop\PrestaShop\Adapter\Product\Image\ProductImagePathFactory;
use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\Query\SearchCombinationsForAssociation;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\QueryHandler\SearchCombinationsForAssociationHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\QueryResult\CombinationForAssociation;
use PrestaShop\PrestaShop\Core\Domain\Product\Image\ValueObject\ImageId;

class SearchCombinationsForAssociationHandler implements SearchCombinationsForAssociationHandlerInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ProductImagePathFactory
     */
    private $productImagePathFactory;

    /**
     * @param ProductRepository $productRepository
     * @param ProductImagePathFactory $productImagePathFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        ProductImagePathFactory $productImagePathFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productImagePathFactory = $productImagePathFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(SearchCombinationsForAssociation $query): array
    {
        $foundCombinations = $this->productRepository->searchCombinations(
            $query->getPhrase(),
            $query->getLanguageId(),
            $query->getShopId(),
            $query->getLimit()
        );

        $productsForAssociation = [];
        foreach ($foundCombinations as $foundProduct) {
            $productsForAssociation[] = $this->createResult($foundProduct);
        }

        return $productsForAssociation;
    }

    /**
     * @param array $foundProduct
     *
     * @return CombinationForAssociation
     */
    private function createResult(array $foundProduct): CombinationForAssociation
    {
        if (empty($foundProduct['id_image'])) {
            $imagePath = $this->productImagePathFactory->getNoImagePath(ProductImagePathFactory::IMAGE_TYPE_HOME_DEFAULT);
        } else {
            $imagePath = $this->productImagePathFactory->getPathByType(
                new ImageId((int) $foundProduct['id_image']),
                ProductImagePathFactory::IMAGE_TYPE_HOME_DEFAULT
            );
        }

        return new CombinationForAssociation(
            (int) $foundProduct['id_product'],
            (int) $foundProduct['id_product_attribute'],
            $foundProduct['name'],
            $foundProduct['reference'] ?? '',
            $imagePath
        );
    }
}
