<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /** @var User[] $users */
        $users = array_map(
            /**
             * Callback pour array_map.
             */
            fn (int $index): User => (new User())
                ->setEmail(sprintf('user+%d@email.com', $index))
                ->setPlainPassword('password')
                ->setUsername(sprintf('user+%d', $index)),
            range(0, 24) // On génère un tableau d'index de 0 à 24 pour la création des utilisateurs.
        );

        // array_walk() : exécute une callback sur chaque élément d'un tableau.
        array_walk(
            $users,
            /**
             * Callback pour array_walk.
             *
             * @return void
             */
            function (User $user) use ($manager): void {
                $manager->persist($user);
            }
        );

        // On sauvegarde les utilisateurs dans la base de données.
        $manager->flush();
    }
}
