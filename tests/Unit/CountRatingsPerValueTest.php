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

        // On appelle la méthode qui calcule nombre de fois où chaque note est attribuée.
        $ratingHandler->countRatingsPerValue($videoGame);

        // On vérifie que le nombre de notes pour chaque valeur (1, 2, 3, 4, 5) correspond à la valeur attendue.
        $this->assertSame(
            $expectedNumberOfRatingPerValue->getNumberOfOne(),
            $videoGame->getNumberOfRatingsPerValue()->getNumberOfOne()
        );
        $this->assertSame(
            $expectedNumberOfRatingPerValue->getNumberOfTwo(),
            $videoGame->getNumberOfRatingsPerValue()->getNumberOfTwo()
        );
        $this->assertSame(
            $expectedNumberOfRatingPerValue->getNumberOfThree(),
            $videoGame->getNumberOfRatingsPerValue()->getNumberOfThree()
        );
        $this->assertSame(
            $expectedNumberOfRatingPerValue->getNumberOfFour(),
            $videoGame->getNumberOfRatingsPerValue()->getNumberOfFour()
        );
        $this->assertSame(
            $expectedNumberOfRatingPerValue->getNumberOfFive(),
            $videoGame->getNumberOfRatingsPerValue()->getNumberOfFive()
        );
    }

    public function provideRatingsAndExpectedCountRating(): array
    {
        return [
            // Test avec des avis de différentes notes.
            [
                [2, 3, 5],
                (function () {
                    $numberOfRatings = new NumberOfRatingPerValue();
                    $numberOfRatings->increaseTwo();
                    $numberOfRatings->increaseThree();
                    $numberOfRatings->increaseFive();
                    return $numberOfRatings;
                })()
            ],
            // Test avec aucun avis.
            [
                [],
                (function () {
                    return new NumberOfRatingPerValue();
                })()
            ],
            // Test avec tous les avis ayant la même note.
            [
                [1, 1, 1],
                (function () {
                    $numberOfRatings = new NumberOfRatingPerValue();
                    $numberOfRatings->increaseOne();
                    $numberOfRatings->increaseOne();
                    $numberOfRatings->increaseOne();
                    return $numberOfRatings;
                })()
            ],
            // Test avec un seul avis avec la note 5.
            [
                [5],
                (function () {
                    $numberOfRatings = new NumberOfRatingPerValue();
                    $numberOfRatings->increaseFive();
                    return $numberOfRatings;
                })()
            ]
        ];
    }
}
