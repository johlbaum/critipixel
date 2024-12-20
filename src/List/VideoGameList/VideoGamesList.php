<?php

declare(strict_types=1);

namespace App\List\VideoGameList;

use App\Doctrine\Repository\VideoGameRepository;
use App\Form\FilterType;
use App\Model\Entity\VideoGame;
use App\Model\ValueObject\Page;
use Countable;
use Doctrine\ORM\Tools\Pagination\Paginator;
use IteratorAggregate;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traversable;

/**
 * @implements IteratorAggregate<VideoGame>
 */
final class VideoGamesList implements Countable, IteratorAggregate
{
    private FormView $form;

    private Filter $filter;

    /**
     * @var Paginator<VideoGame>
     */
    private Paginator $data;

    private string $route;

    private array $routeParameters;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FormFactoryInterface $formFactory,
        private VideoGameRepository $videoGameRepository,
        private Pagination  $pagination,
    ) {}

    public function getForm(): FormView
    {
        return $this->form;
    }

    public function handleRequest(Request $request): self
    {
        // On génère un objet filtre vide pour les tags et pour la recherche pas tags.
        $this->filter = new Filter();

        // On récupère les données du TRI envoyées dans l'URL (formulaire de TRI).
        $this->route = $request->attributes->get('_route');
        $this->routeParameters = $request->query->all();

        // On récupère les données du filtre (Tag + résulat de la recherche).
        $this->form = $this->formFactory
            ->create(
                FilterType::class,
                $this->filter,
                [
                    'method' => Request::METHOD_GET,
                    'csrf_protection' => false,
                ]
            )
            ->handleRequest($request)
            ->createView();

        $this->data = $this->videoGameRepository->getVideoGames($this->pagination, $this->filter);

        $this->pagination->init(count($this->data), count($this));

        if ($this->pagination->getPage() > 1) {
            $this->pagination->add(
                new Page(
                    1,
                    false,
                    'Première page',
                    $this->generateUrl(1)
                )
            );

            $this->pagination->add(
                new Page(
                    $this->pagination->getPage() - 1,
                    false,
                    'Précédent',
                    $this->generateUrl($this->pagination->getPage() - 1)
                )
            );
        }

        $pageRange = range(
            max(1, $this->pagination->getPage() - 3),
            min($this->pagination->getLastPage(), $this->pagination->getPage() + 3)
        );

        foreach ($pageRange as $page) {
            $this->pagination->add(
                new Page(
                    $page,
                    $page === $this->pagination->getPage(),
                    (string) $page,
                    $this->generateUrl($page)
                )
            );
        }

        if ($this->pagination->getPage() < $this->pagination->getLastPage()) {
            $this->pagination->add(
                new Page(
                    $this->pagination->getPage() + 1,
                    false,
                    'Suivant',
                    $this->generateUrl($this->pagination->getPage() + 1)
                )
            );

            $this->pagination->add(
                new Page(
                    $this->pagination->getLastPage(),
                    false,
                    'Dernière page',
                    $this->generateUrl($this->pagination->getLastPage())
                )
            );
        }

        $this->pagination->init(count($this->data), count($this));

        return $this;
    }

    public function getFilter(): Filter
    {
        return $this->filter;
    }

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public function getIterator(): Traversable
    {
        return $this->data;
    }

    public function count(): int
    {
        return count($this->data->getIterator());
    }

    public function generateUrl(int $page): string
    {
        return $this->urlGenerator->generate(
            $this->route,
            ['page' => $page] + $this->pagination->toArray() + $this->routeParameters
        );
    }
}
