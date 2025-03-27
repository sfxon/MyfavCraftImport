<?php declare(strict_types=1);

namespace Myfav\CraftImport\Administration\Controller;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Shopware\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route(defaults: ['_routeScope' => ['api']])]
class CraftProductSearchApiController extends AbstractController
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,)
    {
    }

    #[Route(path: '/api/myfav/craft/product/search/{productNumber}', name: 'api.action.myfav.craft.product.search', methods: ['GET'])]
    public function getState(string $productNumber, Context $context, Request $request): JsonResponse
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
                    'query' => $this->getGraphqlSearchDefinition($productNumber, 1)
                ]
            ]);
            $statusCode = $response->getStatusCode();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
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