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
        // On crée une instance de VideoGame.
        $videoGame = new VideoGame();

        // On crée et ajoute des objets Review au jeu vidéo avec les notes associées. 
        foreach ($ratings as $rating) {
            $review = new Review();
            $review->setRating($rating);
            $videoGame->getReviews()->add($review);
        }

        // On crée une instance de RatingHandler qui est responsable de la gestion des notes.
        $ratingHandler = new RatingHandler();

        // On appelle la méthode qui calcule la moyenne des notes pour un jeu.
        $ratingHandler->calculateAverage($videoGame);

        // On vérifie que la moyenne calculée correspond à la valeur attendue.
        $this->assertSame($expectedAverage, $videoGame->getAverageRating());
    }

    public function provideRatingsAndExpectedAverage(): array
    {
        return [
            'no reviews' => [[], null], // Aucun avis : moyenne null.
            'one review' => [[5], 5],  // Une seule note : moyenne égale à la note.
            'multiple reviews' => [[3, 5, 2, 5, 5, 4, 3, 2, 4, 1, 1], 4], // Plusieurs notes : moyenne correcte.
            'rounding up' => [[3, 4], 4], // Arrondi vers le haut : (3 + 4) / 2 = 4.
            // On teste les bornes (les valeurs minimales et maximales).
            'minimum rating' => [[1], 1], // Note minimale.
            'maximum rating' => [[5, 5, 5], 5], // Note maximale.
        ];
    }
}
