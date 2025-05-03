<?php declare(strict_types=1);

namespace Myfav\CraftImport\Administration\Controller;

use Myfav\CraftImport\Service\CraftProductSaveService;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route(defaults: ['_routeScope' => ['api']])]
class CraftProductSaveApiController extends AbstractController
{
    public function __construct(
        private readonly CraftProductSaveService $craftProductSaveService,)
    {
    }

    #[Route(path: '/api/myfav/craft/product/save/', name: 'api.action.myfav.craft.product.save', methods: ['POST'])]
    public function save(Context $context, Request $request): JsonResponse
    {
        $requestValues = $request->request->all();
        $craftData = $requestValues['craftData'];
        $customProductSettings = $requestValues['customProductSettings'];
        $syncProduct = $requestValues['syncProduct'];

        $result = $this->craftProductSaveService->saveProduct($context, $craftData, $customProductSettings, $syncProduct);

        return new JsonResponse($result);
    }
}