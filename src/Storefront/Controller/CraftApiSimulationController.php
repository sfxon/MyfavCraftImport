<?php declare(strict_types=1);

namespace Myfav\CraftImport\Storefront\Controller;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/*
Die Simulation kann verwendet werden, um bestimmte API Anfragen mit gewünschten Ergebnissen zu simulieren.
Um die Simulation in Live-Systemen zu verwenden, genügt es, die URL als CRAFT-API Url zu konfigurieren.
In Dockware-Umgebungen ist ggf. eine alternative Konfiguration notwendig,
so ist es möglich, dass der Sales Channel korrekt benannt werden muss.
Da die Anfrage intern im Docker Container stattfindet, muss neben der externen URL des SalesChannel (bspw. meinServer.local:88)
auch eine interne konfiguriert werden, ohne den Port am Ende: (meinServer.local).
Die URL ohne den Port ist dann zu verwenden, und zwar auch, wenn der Admin-Watcher läuft,
denn diese Simulation ist als Storefront Service umgesetzt.
Die Absicherung vor externem Aufruf findet über das hinterlegte Token automatisiert statt.
*/
#[Route(defaults: ['_routeScope' => ['storefront']])]
class CraftApiSimulationController extends StorefrontController
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,)
    {
    }


    // Call it for example like https://myshop.de/myfav/craft/api/simulation/graphql
    #[Route(path: '/myfav/craft/api/simulation/graphql', name: 'frontend.myfav.craft.api.simulation.graphql', methods: ['GET', 'POST'] )]
    public function apiSimulation( Request $request, SalesChannelContext $context): Response
    {
        $headers = getallheaders();

        if(!isset($headers['Authorization'])) {
            die('Missing authorization header.');
        }

        $authorization = $headers['Authorization'];
        $authorization = trim(str_replace('Bearer ', '', $authorization));

        // Check API Token.
        $pluginConfig = $this->systemConfigService->get('MyfavCraftImport.config');

        if(
            !isset($pluginConfig['craftApiToken']) || 
            $pluginConfig['craftApiToken'] === null || 
            strlen($pluginConfig['craftApiToken']) === 0 ||
            $pluginConfig['craftApiToken'] != $authorization)
        {
            throw new \Exception('Authorization ist invalid.');
        }

        // Da wir an die CRAFT API eine GRAPHQL Anfrage senden, müssen wir diese hier parsen,
        // um den Wert des Parameters q zu erhalten (q = Suchbegriff).
        $requestValues = $request->request->all();
        preg_match('/q:\s*"([^"]+)"/', $requestValues['query'], $matches);
        $searchterm = '';

        if (isset($matches[1])) {
            $searchterm = $matches[1];
        }

        switch($searchterm) {
            case 'craft': // Loads the data for a default article.
                return $this->getDefault();
            case 'removed-variant':
                // Die letzte Variante wurde entfernt. Die vorletzte Variante wurde in availabilityGlobal auf 0 gesetzt. 
                // Der Preis der ersten Variante wurde angepasst.
                return $this->getDefaultWithRemovedVariant();
        }

        throw new \Exception('The given $searchterm (' . $searchterm . ') is not supported. Supported searchterms: craft, removed-variant');
    }

    /**
     * getDefault
     *
     * @return Response
     */
    private function getDefault(): JsonResponse
    {
        return new JsonResponse(
            json_decode('{
                "data": {
                    "productSearch": {
                    "pageInfo": {
                        "pageSize": 30,
                        "pageCount": 5,
                        "hasNextPage": true,
                        "page": 1
                    },
                    "result": [
                        {
                        "productActivityType": [
                            {
                            "value": null
                            }
                        ],
                        "productApplicationAreas": null,
                        "productApplicationTypes": null,
                        "productCareInstructions": [
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            }
                        ],
                        "productCatalogText": "Klassischer Hoodie aus angenehm weichem und bequemen Material für alltägliche Aktivitäten. Mit Kängurutasche vorn, auffälligem Craft-Print auf der Brust sowie Rippenbündchen an Ärmelabschlüssen und Bund.\n\n• Regular fit\n• Kängurutasche vorne\n• Craft Logo auf der Brust\n• Rippenbündchen an Bund und Armabschlüssen",
                        "productCatalogPrice": "",
                        "productCategory": [
                            {
                            "value": "Hoodie"
                            }
                        ],
                        "productCertifications": [],
                        "productClosure": null,
                        "productCommerceText": "",
                        "productFabrics": "65% Polyester 35% Baumwolle",
                        "productFeature": [
                            {
                            "value": "Soft Touch"
                            },
                            {
                            "value": "Regular fit"
                            }
                        ],
                        "productFit": [
                            {
                            "value": "Regular"
                            }
                        ],
                        "productGender": [
                            {
                            "value": "Herren"
                            }
                        ],
                        "productHoodDetails": null,
                        "productMaterialTechnique": null,
                        "productName": "CORE Craft hood M",
                        "productNeckline": null,
                        "productPockets": null,
                        "productSleeve": null,
                        "productText": "Klassischer Hoodie aus angenehm weichem und bequemen Material für alltägliche Aktivitäten. Mit Kängurutasche vorn, auffälligem Craft-Print auf der Brust sowie Rippenbündchen an Ärmelabschlüssen und Bund.\n\n• Regular fit\n• Kängurutasche vorne\n• Craft Logo auf der Brust\n• Rippenbündchen an Bund und Armabschlüssen",
                        "productColorComment": null,
                        "productPackagingStandard": null,
                        "productNumber": "1910677",
                        "productBrand": "Craft",
                        "productCatalogSize": "XS-XXL",
                        "productMeasure": null,
                        "productWeight": null,
                        "productHeightStandard": null,
                        "productWidthStandard": null,
                        "productLengthStandard": null,
                        "productVolumeStandard": null,
                        "productWeightStandard": null,
                        "documents": [],
                        "productRange": null,
                        "productPromoSeason": null,
                        "productRetailSeason": [
                            "SS21"
                        ],
                        "productDesignerStandard": null,
                        "productPrintCode": null,
                        "sizes": "S-XXL",
                        "retailPrice": {
                            "price": 49.95
                        },
                        "variations": [
                            {
                            "itemColorName": "Flow",
                            "productNumber": "1910677",
                            "itemNumber": "1910677-362000",
                            "itemColorCode": "362000",
                            "pictures": [
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/264553/1910677-362000_CORE%20Craft%20hood%20M_Front.jpg",
                                "resourceFileId": "264553",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Front.jpg",
                                "resourcePictureAngle": "front",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/264553/1910677-362000_CORE%20Craft%20hood%20M_Front.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281961/1910677-362000_CORE%20Craft%20hood%20M_Closeup1.jpg",
                                "resourceFileId": "281961",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup1.jpg",
                                "resourcePictureAngle": "closeup1",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281961/1910677-362000_CORE%20Craft%20hood%20M_Closeup1.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281962/1910677-362000_CORE%20Craft%20hood%20M_Closeup2.jpg",
                                "resourceFileId": "281962",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup2.jpg",
                                "resourcePictureAngle": "closeup2",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281962/1910677-362000_CORE%20Craft%20hood%20M_Closeup2.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281963/1910677-362000_CORE%20Craft%20hood%20M_Closeup3.jpg",
                                "resourceFileId": "281963",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup3.jpg",
                                "resourcePictureAngle": "closeup3",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281963/1910677-362000_CORE%20Craft%20hood%20M_Closeup3.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281964/1910677-362000_CORE%20Craft%20hood%20M_Closeup4.jpg",
                                "resourceFileId": "281964",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup4.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281964/1910677-362000_CORE%20Craft%20hood%20M_Closeup4.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281965/1910677-362000_CORE%20Craft%20hood%20M_Closeup5.jpg",
                                "resourceFileId": "281965",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup5.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281965/1910677-362000_CORE%20Craft%20hood%20M_Closeup5.jpg"
                                }
                            ],
                            "skus": []
                            },
                            {
                            "itemColorName": "Grey Melange",
                            "productNumber": "1910677",
                            "itemNumber": "1910677-950000",
                            "itemColorCode": "950000",
                            "pictures": [
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/238797/1910677-950000_CORE%20Craft%20hood%20M_Front.jpg",
                                "resourceFileId": "238797",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Front.jpg",
                                "resourcePictureAngle": "front",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/238797/1910677-950000_CORE%20Craft%20hood%20M_Front.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239288/1910677-950000_CORE%20Craft%20hood%20M_Closeup1.jpg",
                                "resourceFileId": "239288",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Closeup1.jpg",
                                "resourcePictureAngle": "closeup1",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239288/1910677-950000_CORE%20Craft%20hood%20M_Closeup1.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239289/1910677-950000_CORE%20Craft%20hood%20M_Closeup2.jpg",
                                "resourceFileId": "239289",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Closeup2.jpg",
                                "resourcePictureAngle": "closeup2",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239289/1910677-950000_CORE%20Craft%20hood%20M_Closeup2.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239290/1910677-950000_CORE%20Craft%20hood%20M_Closeup3.jpg",
                                "resourceFileId": "239290",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Closeup3.jpg",
                                "resourcePictureAngle": "closeup3",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239290/1910677-950000_CORE%20Craft%20hood%20M_Closeup3.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239291/1910677-950000_CORE%20Craft%20hood%20M_Closeup4.jpg",
                                "resourceFileId": "239291",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Closeup4.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239291/1910677-950000_CORE%20Craft%20hood%20M_Closeup4.jpg"
                                }
                            ],
                            "skus": [
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "4",
                                    "webtext": "S"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-4",
                                "skucolor": "950000",
                                "availability": 0,
                                "availabilityRegional": null,
                                "availabilityGlobal": 31,
                                "description": "CORE CRAFT HOOD M grey melange S"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "5",
                                    "webtext": "M"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-5",
                                "skucolor": "950000",
                                "availability": 14,
                                "availabilityRegional": null,
                                "availabilityGlobal": 29,
                                "description": "CORE CRAFT HOOD M grey melange M"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "6",
                                    "webtext": "L"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-6",
                                "skucolor": "950000",
                                "availability": 24,
                                "availabilityRegional": null,
                                "availabilityGlobal": 57,
                                "description": "CORE CRAFT HOOD M grey melange L"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "7",
                                    "webtext": "XL"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-7",
                                "skucolor": "950000",
                                "availability": 0,
                                "availabilityRegional": null,
                                "availabilityGlobal": 39,
                                "description": "CORE CRAFT HOOD M grey melange XL"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "8",
                                    "webtext": "XXL"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-8",
                                "skucolor": "950000",
                                "availability": 28,
                                "availabilityRegional": null,
                                "availabilityGlobal": 62,
                                "description": "CORE CRAFT HOOD M grey melange XXL"
                                }
                            ]
                            },
                            {
                            "itemColorName": "Black",
                            "productNumber": "1910677",
                            "itemNumber": "1910677-999000",
                            "itemColorCode": "999000",
                            "pictures": [
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/238799/1910677-999000_CORE%20Craft%20hood%20M_Front.jpg",
                                "resourceFileId": "238799",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Front.jpg",
                                "resourcePictureAngle": "front",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/238799/1910677-999000_CORE%20Craft%20hood%20M_Front.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239296/1910677-999000_CORE%20Craft%20hood%20M_Closeup1.jpg",
                                "resourceFileId": "239296",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup1.jpg",
                                "resourcePictureAngle": "closeup1",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239296/1910677-999000_CORE%20Craft%20hood%20M_Closeup1.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239297/1910677-999000_CORE%20Craft%20hood%20M_Closeup2.jpg",
                                "resourceFileId": "239297",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup2.jpg",
                                "resourcePictureAngle": "closeup2",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239297/1910677-999000_CORE%20Craft%20hood%20M_Closeup2.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239298/1910677-999000_CORE%20Craft%20hood%20M_Closeup3.jpg",
                                "resourceFileId": "239298",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup3.jpg",
                                "resourcePictureAngle": "closeup3",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239298/1910677-999000_CORE%20Craft%20hood%20M_Closeup3.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239299/1910677-999000_CORE%20Craft%20hood%20M_Closeup4.jpg",
                                "resourceFileId": "239299",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup4.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239299/1910677-999000_CORE%20Craft%20hood%20M_Closeup4.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239300/1910677-999000_CORE%20Craft%20hood%20M_Closeup5.jpg",
                                "resourceFileId": "239300",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup5.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239300/1910677-999000_CORE%20Craft%20hood%20M_Closeup5.jpg"
                                }
                            ],
                            "skus": [
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "4",
                                    "webtext": "S"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-999000-4",
                                "skucolor": "999000",
                                "availability": 0,
                                "availabilityRegional": 0,
                                "availabilityGlobal": 1,
                                "description": "CORE CRAFT HOOD M black S"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "5",
                                    "webtext": "M"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-999000-5",
                                "skucolor": "999000",
                                "availability": 0,
                                "availabilityRegional": 0,
                                "availabilityGlobal": 1,
                                "description": "CORE CRAFT HOOD M black M"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "6",
                                    "webtext": "L"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-999000-6",
                                "skucolor": "999000",
                                "availability": 0,
                                "availabilityRegional": 0,
                                "availabilityGlobal": 13,
                                "description": "CORE CRAFT HOOD M black L"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "7",
                                    "webtext": "XL"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-999000-7",
                                "skucolor": "999000",
                                "availability": 0,
                                "availabilityRegional": 0,
                                "availabilityGlobal": 11,
                                "description": "CORE CRAFT HOOD M black XL"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "8",
                                    "webtext": "XXL"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-999000-8",
                                "skucolor": "999000",
                                "availability": 0,
                                "availabilityRegional": null,
                                "availabilityGlobal": 1,
                                "description": "CORE CRAFT HOOD M black XXL"
                                }
                            ]
                            }
                        ],
                        "pictures": [
                            {
                            "imageUrl": "https://images.nwgmedia.com/preview/238797/1910677-950000_CORE%20Craft%20hood%20M_Front.jpg",
                            "resourceFileId": "238797",
                            "resourceFileName": "1910677-950000_CORE Craft hood M_Front.jpg",
                            "resourcePictureAngle": "front",
                            "resourcePictureType": "Productpicture",
                            "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/238797/1910677-950000_CORE%20Craft%20hood%20M_Front.jpg"
                            }
                        ]
                        }
                    ]
                    }
                }
            }')
        );
    }

    /**
     * getDefaultWithRemovedVariant
     *
     * @return JsonResponse
     */
    private function getDefaultWithRemovedVariant(): JsonResponse
    {
        // Die letzte Variante wurde hier entfernt:
        // Name: CORE CRAFT HOOD M black XXL
        // SKU: 1910677-999000-8

        // Gleichzeitig wurde bei der vorletzten Variante
        // der Lagerbestand auf 0 gesetzt (availabilityGlobal)
        // Name: CORE CRAFT HOOD M black XL
        // SKU: 1910677-999000-7
        return new JsonResponse(
            json_decode('{
                "data": {
                    "productSearch": {
                    "pageInfo": {
                        "pageSize": 30,
                        "pageCount": 5,
                        "hasNextPage": true,
                        "page": 1
                    },
                    "result": [
                        {
                        "productActivityType": [
                            {
                            "value": null
                            }
                        ],
                        "productApplicationAreas": null,
                        "productApplicationTypes": null,
                        "productCareInstructions": [
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            },
                            {
                            "value": null
                            }
                        ],
                        "productCatalogText": "Klassischer Hoodie aus angenehm weichem und bequemen Material für alltägliche Aktivitäten. Mit Kängurutasche vorn, auffälligem Craft-Print auf der Brust sowie Rippenbündchen an Ärmelabschlüssen und Bund.\n\n• Regular fit\n• Kängurutasche vorne\n• Craft Logo auf der Brust\n• Rippenbündchen an Bund und Armabschlüssen",
                        "productCatalogPrice": "",
                        "productCategory": [
                            {
                            "value": "Hoodie"
                            }
                        ],
                        "productCertifications": [],
                        "productClosure": null,
                        "productCommerceText": "",
                        "productFabrics": "65% Polyester 35% Baumwolle",
                        "productFeature": [
                            {
                            "value": "Soft Touch"
                            },
                            {
                            "value": "Regular fit"
                            }
                        ],
                        "productFit": [
                            {
                            "value": "Regular"
                            }
                        ],
                        "productGender": [
                            {
                            "value": "Herren"
                            }
                        ],
                        "productHoodDetails": null,
                        "productMaterialTechnique": null,
                        "productName": "CORE Craft hood M",
                        "productNeckline": null,
                        "productPockets": null,
                        "productSleeve": null,
                        "productText": "Klassischer Hoodie aus angenehm weichem und bequemen Material für alltägliche Aktivitäten. Mit Kängurutasche vorn, auffälligem Craft-Print auf der Brust sowie Rippenbündchen an Ärmelabschlüssen und Bund.\n\n• Regular fit\n• Kängurutasche vorne\n• Craft Logo auf der Brust\n• Rippenbündchen an Bund und Armabschlüssen",
                        "productColorComment": null,
                        "productPackagingStandard": null,
                        "productNumber": "1910677",
                        "productBrand": "Craft",
                        "productCatalogSize": "XS-XXL",
                        "productMeasure": null,
                        "productWeight": null,
                        "productHeightStandard": null,
                        "productWidthStandard": null,
                        "productLengthStandard": null,
                        "productVolumeStandard": null,
                        "productWeightStandard": null,
                        "documents": [],
                        "productRange": null,
                        "productPromoSeason": null,
                        "productRetailSeason": [
                            "SS21"
                        ],
                        "productDesignerStandard": null,
                        "productPrintCode": null,
                        "sizes": "S-XXL",
                        "retailPrice": {
                            "price": 49.95
                        },
                        "variations": [
                            {
                            "itemColorName": "Flow",
                            "productNumber": "1910677",
                            "itemNumber": "1910677-362000",
                            "itemColorCode": "362000",
                            "pictures": [
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/264553/1910677-362000_CORE%20Craft%20hood%20M_Front.jpg",
                                "resourceFileId": "264553",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Front.jpg",
                                "resourcePictureAngle": "front",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/264553/1910677-362000_CORE%20Craft%20hood%20M_Front.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281961/1910677-362000_CORE%20Craft%20hood%20M_Closeup1.jpg",
                                "resourceFileId": "281961",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup1.jpg",
                                "resourcePictureAngle": "closeup1",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281961/1910677-362000_CORE%20Craft%20hood%20M_Closeup1.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281962/1910677-362000_CORE%20Craft%20hood%20M_Closeup2.jpg",
                                "resourceFileId": "281962",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup2.jpg",
                                "resourcePictureAngle": "closeup2",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281962/1910677-362000_CORE%20Craft%20hood%20M_Closeup2.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281963/1910677-362000_CORE%20Craft%20hood%20M_Closeup3.jpg",
                                "resourceFileId": "281963",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup3.jpg",
                                "resourcePictureAngle": "closeup3",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281963/1910677-362000_CORE%20Craft%20hood%20M_Closeup3.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281964/1910677-362000_CORE%20Craft%20hood%20M_Closeup4.jpg",
                                "resourceFileId": "281964",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup4.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281964/1910677-362000_CORE%20Craft%20hood%20M_Closeup4.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/281965/1910677-362000_CORE%20Craft%20hood%20M_Closeup5.jpg",
                                "resourceFileId": "281965",
                                "resourceFileName": "1910677-362000_CORE Craft hood M_Closeup5.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/281965/1910677-362000_CORE%20Craft%20hood%20M_Closeup5.jpg"
                                }
                            ],
                            "skus": []
                            },
                            {
                            "itemColorName": "Grey Melange",
                            "productNumber": "1910677",
                            "itemNumber": "1910677-950000",
                            "itemColorCode": "950000",
                            "pictures": [
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/238797/1910677-950000_CORE%20Craft%20hood%20M_Front.jpg",
                                "resourceFileId": "238797",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Front.jpg",
                                "resourcePictureAngle": "front",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/238797/1910677-950000_CORE%20Craft%20hood%20M_Front.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239288/1910677-950000_CORE%20Craft%20hood%20M_Closeup1.jpg",
                                "resourceFileId": "239288",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Closeup1.jpg",
                                "resourcePictureAngle": "closeup1",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239288/1910677-950000_CORE%20Craft%20hood%20M_Closeup1.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239289/1910677-950000_CORE%20Craft%20hood%20M_Closeup2.jpg",
                                "resourceFileId": "239289",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Closeup2.jpg",
                                "resourcePictureAngle": "closeup2",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239289/1910677-950000_CORE%20Craft%20hood%20M_Closeup2.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239290/1910677-950000_CORE%20Craft%20hood%20M_Closeup3.jpg",
                                "resourceFileId": "239290",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Closeup3.jpg",
                                "resourcePictureAngle": "closeup3",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239290/1910677-950000_CORE%20Craft%20hood%20M_Closeup3.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239291/1910677-950000_CORE%20Craft%20hood%20M_Closeup4.jpg",
                                "resourceFileId": "239291",
                                "resourceFileName": "1910677-950000_CORE Craft hood M_Closeup4.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239291/1910677-950000_CORE%20Craft%20hood%20M_Closeup4.jpg"
                                }
                            ],
                            "skus": [
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "4",
                                    "webtext": "S"
                                },
                                "retailPrice": {
                                    "price": 79.95
                                },
                                "sku": "1910677-950000-4",
                                "skucolor": "950000",
                                "availability": 0,
                                "availabilityRegional": null,
                                "availabilityGlobal": 31,
                                "description": "CORE CRAFT HOOD M grey melange S"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "5",
                                    "webtext": "M"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-5",
                                "skucolor": "950000",
                                "availability": 14,
                                "availabilityRegional": null,
                                "availabilityGlobal": 29,
                                "description": "CORE CRAFT HOOD M grey melange M"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "6",
                                    "webtext": "L"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-6",
                                "skucolor": "950000",
                                "availability": 24,
                                "availabilityRegional": null,
                                "availabilityGlobal": 57,
                                "description": "CORE CRAFT HOOD M grey melange L"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "7",
                                    "webtext": "XL"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-7",
                                "skucolor": "950000",
                                "availability": 0,
                                "availabilityRegional": null,
                                "availabilityGlobal": 39,
                                "description": "CORE CRAFT HOOD M grey melange XL"
                                },
                                {
                                "company": "92",
                                "active": true,
                                "skuSize": {
                                    "size": "8",
                                    "webtext": "XXL"
                                },
                                "retailPrice": {
                                    "price": 49.95
                                },
                                "sku": "1910677-950000-8",
                                "skucolor": "950000",
                                "availability": 28,
                                "availabilityRegional": null,
                                "availabilityGlobal": 62,
                                "description": "CORE CRAFT HOOD M grey melange XXL"
                                }
                            ]
                            },
                            {
                            "itemColorName": "Black",
                            "productNumber": "1910677",
                            "itemNumber": "1910677-999000",
                            "itemColorCode": "999000",
                            "pictures": [
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/238799/1910677-999000_CORE%20Craft%20hood%20M_Front.jpg",
                                "resourceFileId": "238799",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Front.jpg",
                                "resourcePictureAngle": "front",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/238799/1910677-999000_CORE%20Craft%20hood%20M_Front.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239296/1910677-999000_CORE%20Craft%20hood%20M_Closeup1.jpg",
                                "resourceFileId": "239296",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup1.jpg",
                                "resourcePictureAngle": "closeup1",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239296/1910677-999000_CORE%20Craft%20hood%20M_Closeup1.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239297/1910677-999000_CORE%20Craft%20hood%20M_Closeup2.jpg",
                                "resourceFileId": "239297",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup2.jpg",
                                "resourcePictureAngle": "closeup2",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239297/1910677-999000_CORE%20Craft%20hood%20M_Closeup2.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239298/1910677-999000_CORE%20Craft%20hood%20M_Closeup3.jpg",
                                "resourceFileId": "239298",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup3.jpg",
                                "resourcePictureAngle": "closeup3",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239298/1910677-999000_CORE%20Craft%20hood%20M_Closeup3.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239299/1910677-999000_CORE%20Craft%20hood%20M_Closeup4.jpg",
                                "resourceFileId": "239299",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup4.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239299/1910677-999000_CORE%20Craft%20hood%20M_Closeup4.jpg"
                                },
                                {
                                "imageUrl": "https://images.nwgmedia.com/preview/239300/1910677-999000_CORE%20Craft%20hood%20M_Closeup5.jpg",
                                "resourceFileId": "239300",
                                "resourceFileName": "1910677-999000_CORE Craft hood M_Closeup5.jpg",
                                "resourcePictureAngle": "none",
                                "resourcePictureType": "Productpicture",
                                "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/239300/1910677-999000_CORE%20Craft%20hood%20M_Closeup5.jpg"
                                }
                            ],
                            "skus": [
                                {
                                    "company": "92",
                                    "active": true,
                                    "skuSize": {
                                        "size": "4",
                                        "webtext": "S"
                                    },
                                    "retailPrice": {
                                        "price": 49.95
                                    },
                                    "sku": "1910677-999000-4",
                                    "skucolor": "999000",
                                    "availability": 0,
                                    "availabilityRegional": 0,
                                    "availabilityGlobal": 1,
                                    "description": "CORE CRAFT HOOD M black S"
                                },
                                {
                                    "company": "92",
                                    "active": true,
                                    "skuSize": {
                                        "size": "5",
                                        "webtext": "M"
                                    },
                                    "retailPrice": {
                                        "price": 49.95
                                    },
                                    "sku": "1910677-999000-5",
                                    "skucolor": "999000",
                                    "availability": 0,
                                    "availabilityRegional": 0,
                                    "availabilityGlobal": 1,
                                    "description": "CORE CRAFT HOOD M black M"
                                },
                                {
                                    "company": "92",
                                    "active": true,
                                    "skuSize": {
                                        "size": "6",
                                        "webtext": "L"
                                    },
                                    "retailPrice": {
                                        "price": 49.95
                                    },
                                    "sku": "1910677-999000-6",
                                    "skucolor": "999000",
                                    "availability": 0,
                                    "availabilityRegional": 0,
                                    "availabilityGlobal": 13,
                                    "description": "CORE CRAFT HOOD M black L"
                                },
                                {
                                    "company": "92",
                                    "active": true,
                                    "skuSize": {
                                        "size": "7",
                                        "webtext": "XL"
                                    },
                                    "retailPrice": {
                                        "price": 49.95
                                    },
                                    "sku": "1910677-999000-7",
                                    "skucolor": "999000",
                                    "availability": 0,
                                    "availabilityRegional": 0,
                                    "availabilityGlobal": 0,
                                    "description": "CORE CRAFT HOOD M black XL"
                                }
                            ]
                            }
                        ],
                        "pictures": [
                            {
                            "imageUrl": "https://images.nwgmedia.com/preview/238797/1910677-950000_CORE%20Craft%20hood%20M_Front.jpg",
                            "resourceFileId": "238797",
                            "resourceFileName": "1910677-950000_CORE Craft hood M_Front.jpg",
                            "resourcePictureAngle": "front",
                            "resourcePictureType": "Productpicture",
                            "thumbnailUrl": "https://images.nwgmedia.com/thumbnail/238797/1910677-950000_CORE%20Craft%20hood%20M_Front.jpg"
                            }
                        ]
                        }
                    ]
                    }
                }
            }')
        );
    }
}
