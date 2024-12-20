<?php

declare(strict_types=1);

namespace App\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;

final readonly class RatingHandler implements CalculateAverageRating, CountRatingsPerValue
{
    public function calculateAverage(VideoGame $videoGame): void
    {
        if (count($videoGame->getReviews()) === 0) {
            $videoGame->setAverageRating(null);
            return;
        }

        // Calcul de la somme des notes associées au jeu vidéo.
        $ratingsSum = array_sum(
            // Utilisation de array_map() pour transformer la collection d'avis en un tableau de notes.
            array_map(
                // Callback : on extrait la note de chaque objet Review.
                static fn(Review $review): int => $review->getRating(),
                // Conversion de la collection d'objets Review en un tableau simple d'objets Review.
                $videoGame->getReviews()->toArray()
            )
        );

        $videoGame->setAverageRating((int) ceil($ratingsSum / count($videoGame->getReviews())));
    }

    public function countRatingsPerValue(VideoGame $videoGame): void
    {
        $videoGame->getNumberOfRatingsPerValue()->clear();

        if (count($videoGame->getReviews()) === 0) {
            return;
        }

        foreach ($videoGame->getReviews() as $review) {
            match ($review->getRating()) {
                1 => $videoGame->getNumberOfRatingsPerValue()->increaseOne(),
                2 => $videoGame->getNumberOfRatingsPerValue()->increaseTwo(),
                3 => $videoGame->getNumberOfRatingsPerValue()->increaseThree(),
                4 => $videoGame->getNumberOfRatingsPerValue()->increaseFour(),
                default => $videoGame->getNumberOfRatingsPerValue()->increaseFive(),
            };
        }
    }
}
