<?php declare(strict_types=1);

namespace Myfav\CraftImport\Storefront\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class CraftActivateInheritanceController extends StorefrontController
{

    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $productMediaRepository)
    {
    }

    #[Route(path: '/myfav/craft/activate/inheritance/{productNumber}', name: 'frontend.myfav.craft.activate.inheritance', methods: ['GET', 'POST'] )]
    public function activateInheritance(string $productNumber, Context $context, Request $request): Response
    {
        // Prüfe den Schlüssel aus der URL (?k=...)
        $key = $request->query->get('k');
        $expectedKey = 'sdfhasjkdfldsjnksjdnf';

        if ($key !== $expectedKey) {
            return new Response('Zugriff verweigert: Ungültiger Schlüssel', 403);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $criteria->addAssociation('children');
        $criteria->addAssociation('children.media');

        $mainProduct = $this->productRepository->search($criteria, $context)->first();

        if (!$mainProduct) {
            return new Response('Produkt nicht gefunden', 404);
        }

        $updates = [];
        $mediaDeleteIds = [];

        foreach ($mainProduct->getChildren() ?? [] as $variant) {
            foreach ($variant->getMedia() ?? [] as $mediaAssociation) {
                $mediaDeleteIds[] = ['id' => $mediaAssociation->getId()];
            }

            $updates[] = [
                'id' => $variant->getId(),
                'name' => null, // Vererbung aktivieren
                'media' => null
            ];
        }

        if (!empty($mediaDeleteIds)) {
            $this->productMediaRepository->delete($mediaDeleteIds, $context);
        }

        if (!empty($updates)) {
            $this->productRepository->update($updates, $context);
        }

        return new Response('Namensvererbung für Varianten aktiviert: ' . count($updates));
    }
}