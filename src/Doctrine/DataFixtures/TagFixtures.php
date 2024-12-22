<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

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
        /** @var Tag[] $tags */
        $tags = array_map(
            /**
             * Callback pour array_map.
             *
             * @param int $index
             * @return Tag
             */
            fn(int $index): Tag => self::create($index),
            range(0, 9) // On génère un tableau d'index de 0 à 9 pour créer 10 tags.
        );

        array_walk(
            $tags,
            /**
             * Callback pour array_walk.
             *
             * @param Tag $tag
             */
            function (Tag $tag) use ($manager): void {
                $manager->persist($tag);
            }
        );

        $manager->flush();
    }
}
