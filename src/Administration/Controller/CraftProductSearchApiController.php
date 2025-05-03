<?php declare(strict_types=1);

namespace Myfav\CraftImport\Administration\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route(defaults: ['_routeScope' => ['api']])]
class CraftProductSearchApiController extends AbstractController
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,)
    {
    }

    #[Route(path: '/api/myfav/craft/product/search/{searchTerm}', name: 'api.action.myfav.craft.product.search', methods: ['GET'])]
    public function getState(string $searchTerm, Context $context, Request $request): JsonResponse
    {
        $pluginConfig = $this->systemConfigService->get('MyfavCraftImport.config');

        if(!isset($pluginConfig['craftApiUrl']) || !isset($pluginConfig['craftApiToken'])) {
            throw new \Exception('Plugin configuration not found.');
        }

        $client = new Client(
            [
                'base_uri' => $pluginConfig['craftApiUrl']
            ]
        );

        try {
            $response = $client->request('POST', 'graphql', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $pluginConfig['craftApiToken'],
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'query' => $this->getGraphqlSearchDefinition($searchTerm, 1)
                ]
            ]);
            $statusCode = $response->getStatusCode();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody()->getContents();
                throw(new \Exception($body));
            } else {
                throw $e;
            }
        } catch (\Exception $e) {
            throw($e);
        }

        switch ($statusCode) {
            case 200:
                return new JsonResponse(['data' => $response->getBody()->getContents()]);
        }

        throw new \Exception('Something went wrong');
    }

    /**
     * getGraphqlSearchDefinition
     *
     * @param  mixed $keyword
     * @param  mixed $page
     * @return string
     */
    public function getGraphqlSearchDefinition($productNumber, $page): string
    {
       return <<<GQL
query {
productSearch(
q: "$productNumber"
language: "de"
assortmentId: "221774"
page: $page
pageSize: 30
) {
    pageInfo {
pageSize
pageCount
hasNextPage
page
}
result {
productActivityType {
    value
}
productApplicationAreas{
    value
}
productApplicationTypes{
    value
}
productCareInstructions{
    value
}
productCatalogText
productCatalogPrice
productCategory{
    value
}
productCertifications{
    value
}
productClosure{
    value
}
productCommerceText
productFabrics
productFeature{
    value
}
productFit{
    value
}
productGender{
    value
}
productHoodDetails{
    value
}
productMaterialTechnique{
    value
}
productName
productNeckline{
    value
}
productPockets{
    value
}
productSleeve{
    value
}
productText
productColorComment
productPackagingStandard{
    value
}
productNumber
productBrand
productCatalogSize
productMeasure
productWeight
productHeightStandard
productWidthStandard
productLengthStandard
productVolumeStandard
productWeightStandard
documents{
imageUrl
resourceFileId
resourceFileName
resourcePictureAngle
resourcePictureType
thumbnailUrl
}
productRange
productPromoSeason
productRetailSeason
productDesignerStandard
productPrintCode
sizes
retailPrice {
    price
}
variations {
    itemColorName
    productNumber
    itemNumber
    itemColorCode
    pictures {
imageUrl
resourceFileId
resourceFileName
resourcePictureAngle
resourcePictureType
thumbnailUrl
}
    skus {
    company
    active
    skuSize {
        size
        webtext
    }
    retailPrice {
    price
}
sku
skucolor
availability
availabilityRegional
availabilityGlobal
description
}
}
    pictures {
imageUrl
resourceFileId
resourceFileName
resourcePictureAngle
resourcePictureType
thumbnailUrl
}
}
}
}
GQL;
    }
}