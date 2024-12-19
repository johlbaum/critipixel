<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Model\Entity\Tag;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {}

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();

        $users = $manager->getRepository(User::class)->findAll();

        // On exclut l'utilisateur ayant l'email 'user+0@email.com'. Pour les tests, cet utilisateur ne doit pas avoir de review associée.
        $filteredUsers = array_filter($users, fn(User $user) => $user->getEmail() !== 'user+0@email.com');

        $videoGames = array_fill_callback(
            0,
            50,
            // fn est une manière de déclarer des fonctions anonymes dans PHP.
            // Elle prend un argument $index et retourne un objet VideoGame.
            fn(int $index): VideoGame => (new VideoGame)
                ->setTitle(sprintf('Jeu vidéo %d', $index))
                ->setDescription($this->faker->paragraphs(10, true)) // Si le deuxième paramètre est 'true', Faker retourne les paragraphes générés sous forme de chaîne de caractères, où les paragraphes sont concaténés et séparés par un retour à la ligne (\n).
                ->setReleaseDate(new DateTimeImmutable())
                ->setTest($this->faker->paragraphs(6, true))
                ->setRating(($index % 5) + 1)
                ->setImageName(sprintf('video_game_%d.png', $index))
                ->setImageSize(2_098_872)
        );

        // On ajoute des tags aux jeux.
        array_walk($videoGames, static function (VideoGame $videoGame, int $index) use ($tags) {
            // On ajoute 5 tags à chaque jeu.
            for ($tagIndex = 0; $tagIndex < 5; $tagIndex++) {
                // On détermine quel tag ajouter en fonction de l'index du jeu et de celui du tag.
                $tag = $tags[($index + $tagIndex) % count($tags)];

                // On récupère la collection de Tags du jeu.
                // Au premier tour, la collection est vide, mais cela permet d'utiliser les méthodes disponibles pour les collections (add(), get(), contains() etc.).
                // On vérifie si le tag existe déjà dans la collection du jeu.
                if (!$videoGame->getTags()->contains($tag)) {
                    // Si ce n'est pas le cas, on l'ajoute à la collection.
                    $videoGame->getTags()->add($tag);
                }
            }
        });

        array_walk($videoGames, [$manager, 'persist']);

        $manager->flush();

        // On ajoute des reviews aux jeux.
        array_walk($videoGames, function (VideoGame $videoGame) use ($filteredUsers, $manager) {
            foreach ($filteredUsers as $user) {
                $comment = $this->faker->paragraphs(1, true);

                $review = (new Review())
                    ->setUser($user)
                    ->setVideoGame($videoGame)
                    ->setRating($this->faker->numberBetween(1, 5))
                    ->setComment($comment);

                $videoGame->getReviews()->add($review);

                $manager->persist($review);

                $this->calculateAverageRating->calculateAverage($videoGame);
                $this->countRatingsPerValue->countRatingsPerValue($videoGame);
            }
        });

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
