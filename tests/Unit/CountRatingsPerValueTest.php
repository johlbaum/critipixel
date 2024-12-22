<?php

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use App\Model\Entity\Review;
use PHPUnit\Framework\TestCase;

final class CountRatingsPerValuesTest extends TestCase
{
    /**
     * @dataProvider provideRatingsAndExpectedCountRating
     */
    public function testCountRatingPerValues(array $ratings, NumberOfRatingPerValue $expectedNumberOfRatingPerValue)
    {
        // On crée un jeu et on lui ajoute des notes.
        $videoGame = $this->createVideoGameWithRatings($ratings);

        // On crée une instance de RatingHandler qui est responsable de la gestion des notes.
        $ratingHandler = new RatingHandler();

        // On appelle la méthode qui calcule nombre de fois où chaque note est attribuée.
        $ratingHandler->countRatingsPerValue($videoGame);

        // On vérifie que les deux objets ont les mêmes propriétés et les mêmes valeurs pour ces propriétés.
        $this->assertEquals($expectedNumberOfRatingPerValue, $videoGame->getNumberOfRatingsPerValue());
    }

    private function createVideoGameWithRatings(array $ratings): VideoGame
    {
        $videoGame = new VideoGame();

        foreach ($ratings as $rating) {
            $review = (new Review())->setRating($rating);
            $videoGame->getReviews()->add($review);
        }

        return $videoGame;
    }

    public function provideRatingsAndExpectedCountRating(): array
    {
        return [
            // Test avec des avis de différentes notes.
            'reviews with different ratings' => [[2, 3, 5], $this->createExpectedNumberOfRatingPerValue([2 => 1, 3 => 1, 5 => 1])],

            // Test avec aucun avis.
            'no reviews' => [[], $this->createExpectedNumberOfRatingPerValue([])],

            // Test avec tous les avis ayant la même note.
            'all reviews with the same rating' => [[1, 1, 1], $this->createExpectedNumberOfRatingPerValue([1 => 3])],

            // Test avec un seul avis avec la note 5.
            'single review with rating 5' => [[5], $this->createExpectedNumberOfRatingPerValue([5 => 1])],

            // Test avec de nombreux avis et une répartition variée des notes.
            'many reviews with varied ratings' => [
                [1, 2, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5],
                $this->createExpectedNumberOfRatingPerValue([
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5
                ])
            ]
        ];
    }

    private function createExpectedNumberOfRatingPerValue(array $ratingsCount): NumberOfRatingPerValue
    {
        $numberOfRatings = new NumberOfRatingPerValue();

        // Pour chaque note et sa fréquence dans le tableau, on incrémente le nombre de cette note.
        foreach ($ratingsCount as $rating => $count) {
            for ($i = 0; $i < $count; ++$i) {
                match ($rating) {
                    1 => $numberOfRatings->increaseOne(),
                    2 => $numberOfRatings->increaseTwo(),
                    3 => $numberOfRatings->increaseThree(),
                    4 => $numberOfRatings->increaseFour(),
                    5 => $numberOfRatings->increaseFive(),
                    default => $numberOfRatings->increaseFive(),
                };
            }
        }

        return $numberOfRatings;
    }
}
