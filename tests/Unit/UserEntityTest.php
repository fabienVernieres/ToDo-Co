<?php

namespace App\tests\Unit;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Test sur l'entité User
 */
class UserEntityTest extends KernelTestCase
{
    private const VALID_USERNAME = "John Doe";
    private const RESERVED_USERNAME = "admin";
    private const VALID_EMAIL = "name@domain.com";
    private const NO_VALID_EMAIL = "namedomainecom";
    /**
     * Validateur de Symfony.
     * @var ValidatorInterface
     */
    public ValidatorInterface $validator;

    /**
     * Démarre le Kernel et récupère le validator via le container de services
     *
     * @return void
     */
    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->validator = $container->get('validator');
    }

    /**
     * Test une entité User avec un username et un email valides
     *
     * @return void
     */
    public function testUserEntityIsValid(): void
    {
        $user = new User();

        $user
            ->setUsername(self::VALID_USERNAME)
            ->setEmail(self::VALID_EMAIL);

        // 0 erreur attendue.
        $this->getValidationErrors($user, 0);
    }

    /**
     * Test une entité User avec un username non disponible et un email invalide
     *
     * @return void
     */
    public function testUserEntityNotValid(): void
    {
        $user = new User();

        $user
            ->setUsername(self::RESERVED_USERNAME)
            ->setEmail(self::NO_VALID_EMAIL);

        // 2 erreurs attendues.
        $this->getValidationErrors($user, 2);
    }

    /**
     * Retourne une liste des violations de contraintes d'une entité Task
     * en utilisant le validator
     *
     * @param  User $user
     * @param  int $numberExpectedErrors
     * @return ConstraintViolationListInterface
     */
    private function getValidationErrors(User $user, int $numberExpectedErrors): ConstraintViolationListInterface
    {
        $errors = $this->validator->validate($user);

        $this->assertCount($numberExpectedErrors, $errors);

        return $errors;
    }
}