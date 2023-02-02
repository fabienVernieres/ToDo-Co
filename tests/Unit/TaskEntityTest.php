<?php

namespace App\tests\Unit;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Test sur l'entité Task
 */
class TaskEntityTest extends KernelTestCase
{
    private const VALID_TITLE = "Exemple titre";
    private const VALID_CONTENT = "Exemple de contenu";
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
     * Test une entité Task avec un titre et un contenu valides
     *
     * @return void
     */
    public function testTaskEntityIsValid(): void
    {
        $task = new Task();

        $task
            ->setTitle(self::VALID_TITLE)
            ->setContent(self::VALID_CONTENT);

        // Aucune erreur attendue.
        $this->getValidationErrors($task, 0);
    }

    /**
     * Test une entité Task sans titre et sans contenu
     *
     * @return void
     */
    public function testTaskEntityNotValid(): void
    {
        $task = new Task();

        // 2 erreurs attendues.
        $this->getValidationErrors($task, 2);
    }

    /**
     * Retourne une liste des violations de contraintes d'une entité Task
     * en utilisant le validator
     *
     * @param  Task $task
     * @param  int $numberExpectedErrors
     * @return ConstraintViolationListInterface
     */
    private function getValidationErrors(Task $task, int $numberExpectedErrors): ConstraintViolationListInterface
    {
        $errors = $this->validator->validate($task);

        $this->assertCount($numberExpectedErrors, $errors);

        return $errors;
    }
}