<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use function array_fill_callback;

final class TagFixtures extends Fixture
{
    public static function create(int $index): Tag
    {
        $tag = new Tag();

        $tagName = sprintf('Tag %d', $index);

        // Avec l'annotation #[Slug(fields: ['name'])] sur la propriété $code, le slug sera automatiquement
        // généré par Doctrine à partir de la propriété name lors de la persistance de l'entité.
        $tag->setName($tagName);

        return $tag;
    }


    public function load(ObjectManager $manager): void
    {
        // Va retourner un tableau d'objets Tag.
        $tags = array_fill_callback(
            0,
            10,
            // La méthode array_fill_callback§() attend un callable. 
            // On doit passer une référence à la méthode create, pas son résultat immédiat.
            [self::class, 'create']
        );

        // array_walk() : exécute une callback sur chacun des éléments d'un tableau.
        array_walk($tags, [$manager, 'persist']);

        $manager->flush();
    }
}
