<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        // On exclut l'utilisateur ayant l'email 'user+0@email.com'. Pour les tests, cet utilisateur ne doit pas avoir de review associée.
        $filteredUsers = array_filter($users, fn (User $user) => 'user+0@email.com' !== $user->getEmail());

        $videoGames = \array_fill_callback(
            0,
            50,
            fn (int $index): array => [
                (new VideoGame())
                    ->setTitle(sprintf('Jeu vidéo %d', $index))
                    ->setDescription($this->faker->paragraphs(10, true))
                    ->setReleaseDate(new \DateTimeImmutable())
                    ->setTest($this->faker->paragraphs(6, true))
                    ->setRating(($index % 5) + 1)
                    ->setImageName(sprintf('video_game_%d.png', $index))
                    ->setImageSize(2_098_872),
            ]
        );

        array_walk($videoGames, static function (array $videoGameArray, int $index) use ($tags) {
            foreach ($videoGameArray as $videoGame) {
                for ($tagIndex = 0; $tagIndex < 5; ++$tagIndex) {
                    $tag = $tags[($index + $tagIndex) % count($tags)];
                    if (!$videoGame->getTags()->contains($tag)) {
                        $videoGame->getTags()->add($tag);
                    }
                }
            }
        });

        array_walk($videoGames, static function (array $videoGameArray) use ($manager) {
            foreach ($videoGameArray as $videoGame) {
                $manager->persist($videoGame);
            }
        });

        $manager->flush();

        // On ajoute des reviews.
        array_walk($videoGames, function (array $videoGameArray) use ($filteredUsers, $manager) {
            foreach ($videoGameArray as $videoGame) {
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
            }
        });

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
