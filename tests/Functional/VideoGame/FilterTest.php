<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    /**
     * @dataProvider tagProvider
     *
     * @param array<int|string> $tags
     * @param string|null       $expectedFirstGameTitle
     * @param string|null       $expectedLastGameTitle
     */
    public function testShouldFilterByTagsVideoGames(array $tags, int $expectedCount, $expectedFirstGameTitle, $expectedLastGameTitle): void
    {
        // On effectue la requête.
        $crawler = $this->get('/');

        // On vérifie que la réponse de la requête HTTP a un statut HTTP compris entre 200 et 299.
        self::assertResponseIsSuccessful();

        // On vérifie qu'il y ait exactement 10 éléments <article> avec la classe "game-card".
        self::assertSelectorCount(10, 'article.game-card');

        // On capture le bouton de soumission du formulaire et on sélectionne les tags par leurs IDs.
        $form = $crawler->selectButton('Filtrer')->form();

        foreach ($tags as $tag) {
            // On construit la clé pour chaque champ du formulaire
            $fieldKey = 'filter[tags]['.((int) $tag - 1).']';

            // On vérifie si le tag existe.
            if (isset($form[$fieldKey])) {
                // On assigne la valeur du tag au champ du formulaire.
                $form[$fieldKey] = (string) $tag;
            }
        }

        // On soumet le formulaire.
        $this->client->submit($form);

        // On vérifie que la réponse de la requête HTTP a un statut HTTP compris entre 200 et 299.
        self::assertResponseIsSuccessful();

        // On vérifie qu'il y ait exactement $expectedCount éléments <article> avec la classe "game-card" après le filtrage.
        self::assertSelectorCount($expectedCount, 'article.game-card');

        if ($expectedCount > 0) {
            // On vérifie que le titre du premier jeu vidéo filtré est bien le bon.
            self::assertSelectorTextSame(
                'article.game-card:nth-child(1) h5.game-card-title a',
                $expectedFirstGameTitle
            );

            // On vérifie que le titre du dernier jeu vidéo filtré est bien le bon.
            self::assertSelectorTextSame(
                'article.game-card:nth-child('.$expectedCount.') h5.game-card-title a',
                $expectedLastGameTitle
            );
        }

        if (0 === $expectedCount) {
            self::assertSelectorNotExists('article.game-card');
        }
    }

    /**
     * @return array<string, array{0: array<int|string>, 1: int, 2: string|null, 3: string|null}>
     */
    public function tagProvider(): array
    {
        return [
            // Test sans aucun tag.
            'no tags' => [[], 10, 'Jeu vidéo 0', 'Jeu vidéo 9'],

            // Test avec un seul tag.
            'single tag' => [['4'], 10, 'Jeu vidéo 0', 'Jeu vidéo 19'],

            // Test avec plusieurs tags.
            'multiple tags' => [['1', '2', '3', '4', '5'], 5, 'Jeu vidéo 0', 'Jeu vidéo 40'],

            // Test avec plusieurs tags qui ne correspondent à aucun jeu.
            'tags with no matching games' => [['1', '2', '3', '4', '5', '6'], 0, null, null],

            // Test avec un tag inexistant.
            'non-existent tag' => [['100'], 10, 'Jeu vidéo 0', 'Jeu vidéo 9'],
        ];
    }
}
