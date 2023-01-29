<?php

namespace App\tests\Unit;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskTest extends KernelTestCase
{
    private const VALID_TITLE = "Exemple titre";
    private const VALID_CONTENT = "Exemple de contenu";
    public ValidatorInterface $validator;

    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->validator = $container->get('validator');
    }

    public function testTaskEntityIsValid(): void
    {
        $task = new Task();

        $task
            ->setTitle(self::VALID_TITLE)
            ->setContent(self::VALID_CONTENT);

        $this->getValidationErrors($task, 0);
    }

    public function testTaskEntityNotValid(): void
    {
        $task = new Task();

        $this->getValidationErrors($task, 2);
    }

    private function getValidationErrors(Task $task, int $numberExpectedErrors): ConstraintViolationList
    {
        $errors = $this->validator->validate($task);

        $this->assertCount($numberExpectedErrors, $errors);

        return $errors;
    }
}