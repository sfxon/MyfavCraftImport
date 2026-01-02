<?php declare(strict_types=1);

namespace Myfav\CraftImport\Administration\Controller;

use Myfav\CraftImport\Service\CraftImportedArticleSaveService;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route(defaults: ['_routeScope' => ['api']])]
class CraftImportedArticleSaveApiController extends AbstractController
{
    public function __construct(
        private readonly CraftImportedArticleSaveService $craftImportedArticleSaveService,)
    {
    }

    #[Route(path: '/api/myfav/craft/imported/article/save/', name: 'api.action.myfav.craft.imported.article.save', methods: ['POST'])]
    public function save(Context $context, Request $request): JsonResponse
    {
        $requestValues = $request->request->all();
        $myfavVereinId = $requestValues['myfavVereinId'];
        $myfavCraftImportArticleId = $requestValues['myfavCraftImportArticleId'];
        $customProductSettings = $requestValues['customProductSettings'];
        $overriddenCustomProductSettings = $requestValues['overriddenCustomProductSettings'];
        $variations = $requestValues['variations'];

        $result = $this->craftImportedArticleSaveService->saveProduct(
            $context,
            $myfavVereinId,
            $myfavCraftImportArticleId,
            $customProductSettings,
            $overriddenCustomProductSettings,
            $variations
        );

        return new JsonResponse([
            'data' => $result->getData(),
            'hasError' => $result->hasErrors(),
            'errorMessages' => $result->getErrorMessages(),
        ]);
    }
}