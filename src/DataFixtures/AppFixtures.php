<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Faker\Factory;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private UserRepository $userRepository;

    public function __construct(UserPasswordHasherInterface $hasher, UserRepository $userRepository)
    {
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
    }

    public function load(ObjectManager $manager): void
    {
        // Ajout de l'administrateur.
        $user1 = new User();
        $user1->setUsername('admin');
        $user1->setRoles(['ROLE_ADMIN']);
        $password = $this->hasher->hashPassword($user1, '123456');
        $user1->setPassword($password);
        $user1->setEmail('admin@todoco.com');
        $manager->persist($user1);
        $this->addReference('user1', $user1);

        // Ajout de l'utilisateur anonyme pour les tâches orphelines.
        $user2 = new User();
        $user2->setUsername('anonyme');
        $password = $this->hasher->hashPassword($user2, '123456');
        $user2->setPassword($password);
        $user2->setEmail('anonyme@todoco.com');
        $manager->persist($user2);
        $this->addReference('user2', $user2);

        $manager->flush();

        $createdAt = new \DateTimeImmutable();

        // Ajout de 10 tâches.
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $task = new Task();
            $task->setCreatedAt($createdAt);
            $task->setDeadline($createdAt->add(new \DateInterval('P' . $faker->numberBetween(1, 30) . 'D')));
            $task->setTitle($faker->sentence($faker->numberBetween(3, 5)));
            $task->setContent($faker->paragraph($faker->numberBetween(1, 3)));
            // Utilisateur admin ou anonyme.
            $task->setUser($this->userRepository->find($this->getReference('user' . $faker->numberBetween(1, 2))));
            $manager->persist($task);
        }

        $manager->flush();
    }
}