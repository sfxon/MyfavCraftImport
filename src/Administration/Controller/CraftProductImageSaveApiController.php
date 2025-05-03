<?php declare(strict_types=1);

namespace Myfav\CraftImport\Administration\Controller;

use Myfav\CraftImport\Service\ProductImageProcessorService;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route(defaults: ['_routeScope' => ['api']])]
class CraftProductImageSaveApiController extends AbstractController
{
    public function __construct(
        private readonly ProductImageProcessorService $productImageProcessorService,)
    {
    }

    #[Route(path: '/api/myfav/craft/product/image/save/', name: 'api.action.myfav.craft.product.image.save', methods: ['POST'])]
    public function save(Context $context, Request $request): JsonResponse
    {
        $requestValues = $request->request->all();
        $productId = $requestValues['productId'];
        //$productNumber = $requestValues['productNumber'];
        $imageUrls = $requestValues['imageUrls'];

        $result = $this->productImageProcessorService->process($context, $productId, $imageUrls);
        die('done');
    }
}