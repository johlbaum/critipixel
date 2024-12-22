<?php

use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use App\Model\Entity\Review;
use PHPUnit\Framework\TestCase;

final class CalculateAverageRatingTest extends TestCase
{
    /**
     * @dataProvider provideRatingsAndExpectedAverage
     */
    public function testCalculateAverage(array $ratings, ?int $expectedAverage)
    {
        // On crée un jeu et on lui ajoute des notes.
        $videoGame = $this->createVideoGameWithRatings($ratings);

        // On crée une instance de RatingHandler qui est responsable de la gestion des notes.
        $ratingHandler = new RatingHandler();

        // On appelle la méthode qui calcule la moyenne des notes pour un jeu.
        $ratingHandler->calculateAverage($videoGame);

        // On vérifie que la moyenne calculée correspond à la valeur attendue.
        $this->assertSame($expectedAverage, $videoGame->getAverageRating());
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

    public function provideRatingsAndExpectedAverage(): array
    {
        return [
            // Aucun avis : moyenne null.
            'no reviews' => [[], null],

            // Une seule note : moyenne égale à la note.
            'one review' => [[5], 5],

            // Plusieurs notes : moyenne correcte.
            'multiple reviews' => [[3, 5, 2, 5, 5, 4, 3, 2, 4, 1, 1], 4],

            // Arrondi vers le haut : (3 + 4) / 2 = 4.
            'rounding up' => [[3, 4], 4],

            // On teste les bornes (les valeurs minimales et maximales).
            'minimum rating' => [[1], 1],
            'maximum rating' => [[5, 5, 5], 5],
        ];
    }
}
