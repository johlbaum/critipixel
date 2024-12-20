<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ReviewTest extends FunctionalTestCase
{
    private $urlGenerator;

    public function testShouldPostReview()
    {
        // On connecte l'utilisateur.
        $this->login();

        // On initialise le générateur d'URL.
        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        // On récupère un jeu dans la base de données (le premier jeu).
        $videoGame = $this->getEntityManager()->getRepository(VideoGame::class)->findOneBy([]);

        // On génére l'URL de la page de détail du jeu vidéo via le nom de la route et le slug.
        $url = $this->urlGenerator->generate('video_games_show', ['slug' => $videoGame->getSlug()]);

        // On envoie une requête GET vers l'URL générée.
        $crawler = $this->get($url);

        // On vérifie que la réponse de la requête HTTP a un statut HTTP compris entre 200 et 299.
        self::assertResponseIsSuccessful();

        // On capture le bouton de soumission du formulaire et on remplit les champs.
        $form = $crawler->selectButton('Poster')->form();
        $form['review[rating]'] = 3;
        $form['review[comment]'] = "C'est un très bon jeu !";

        // On soumet le formulaire.
        $this->client->submit($form);

        // On vérifie que la soumission du formulaire renvoie une redirection (code 302).
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // On suit la redirection après la soumission du formulaire.
        $this->client->followRedirect();

        // On vérifie que le formulaire n'est plus affiché.
        $crawler = $this->get($url);
        self::assertSelectorNotExists('form[name="review"]');

        // On vérifie que les données saisies sont présentes sur la page après la redirection
        self::assertSelectorTextContains('div.list-group-item:last-child h3', 'user+0');
        self::assertSelectorTextContains('div.list-group-item:last-child p', 'C\'est un très bon jeu !');
        self::assertSelectorTextContains('div.list-group-item:last-child span.value', '3');

        // On récupère la review créé par le test en base de données et on effectue les vérifications.
        $user = $this->getUser();
        $review = $this->getEntityManager()->getRepository(Review::class)->findOneBy([
            'videoGame' => $videoGame,
            'user' => $user
        ]);
        self::assertNotNull($review); // On vérifie que l'avis existe.
        self::assertEquals(3, $review->getRating()); // On vérifie que la note soit égale à 3.
        self::assertEquals("C'est un très bon jeu !", $review->getComment()); // On vérifie que le commentaire corresponde.
    }

    public function testShouldNotAllowInvalidReview()
    {
        // On connecte l'utilisateur.
        $this->login();

        // On initialise le générateur d'URL.
        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        // On récupère un jeu dans la base de données (le premier jeu).
        $videoGame = $this->getEntityManager()->getRepository(VideoGame::class)->findOneBy([]);

        // On génère l'URL de la page de détail du jeu vidéo.
        $url = $this->urlGenerator->generate('video_games_show', ['slug' => $videoGame->getSlug()]);

        // On envoie une requête GET vers l'URL générée.
        $crawler = $this->get($url);

        // On vérifie que la réponse de la requête HTTP a un statut HTTP compris entre 200 et 299.
        self::assertResponseIsSuccessful();

        // On capture le bouton de soumission du formulaire.
        $form = $crawler->selectButton('Poster')->form();

        // Scénario où l'utilisateur essaie d'ajouter une note avec un commentaire trop long. 
        $form['review[rating]'] = 3;
        $form['review[comment]'] = str_repeat('a', 501); // Une contrainte de validation à 500 caractères est insérée sur la propriété $comment de l'entité Review.
        $this->client->submit($form);

        // On vérifie que la soumission renvoie une erreur (HTTP 422).
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testShouldNotDisplayFormForUnauthenticatedUser()
    {
        // On initialise le générateur d'URL.
        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        // On récupère un jeu dans la base de données (le premier jeu).
        $videoGame = $this->getEntityManager()->getRepository(VideoGame::class)->findOneBy([]);

        // On génère l'URL de la page de détail du jeu vidéo.
        $url = $this->urlGenerator->generate('video_games_show', ['slug' => $videoGame->getSlug()]);

        // On envoie une requête GET sans être connecté.
        $this->get($url);

        // On vérifie que la réponse de la requête HTTP a un statut HTTP compris entre 200 et 299.
        self::assertResponseIsSuccessful();

        // On vérifie que le formulaire d'ajout de note n'est pas affiché.
        self::assertSelectorNotExists('form[name="review"]');
    }
}
