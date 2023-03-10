<?php

namespace App\Tests\Functional\Controller;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test le contrôleur TaskController
 */
class TaskControllerTest extends WebTestCase
{
    /**
     * Test l'affichage de la liste des tâches par un utilisateur connecté.
     *
     * @return void
     */
    public function testIndex(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Recherche l'utilisateur admin.
        $testUser = $userRepository->findOneBy(['username' => 'admin']);

        // Simule $testUser est connecté.
        $client->loginUser($testUser);

        // Test l'accès à la liste des tâches.
        $client->request('GET', '/task/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des tâches programmées');
    }

    /**
     * Test l'accès à la page d'ajout d'une tâche.
     *
     * @return void
     */
    public function testAccessToNew(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Recherche l'utilisateur admin.
        $testUser = $userRepository->findOneBy(['username' => 'admin']);

        // Simule $testUser est connecté.
        $client->loginUser($testUser);

        // Test l'accès au formulaire d'ajout de tâche.
        $client->request('GET', '/task/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une nouvelle tâche');
    }

    /**
     * Test l'ajout d'une tâche par admin.
     *
     * @return void
     */
    public function testNew(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Recherche l'utilisateur admin.
        $testUser = $userRepository->findOneBy(['username' => 'admin']);

        // Simule $testUser est connecté.
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/task/new');

        // Sélectionne le bouton du formulaire.
        $buttonCrawlerNode = $crawler->selectButton('Valider');

        // Récupére l'objet Form du formulaire appartenant à ce bouton.
        $form = $buttonCrawlerNode->form();

        // Récupère le token du formulaire.
        $token = (string) $form->get('task[_token]')->getValue();

        // On définit les valeurs saisies dans le formulaire.
        $form['task[title]'] = 'Test titre';
        $form['task[content]'] = 'Test contenu';
        $form['task[deadline][day]'] = '1';
        $form['task[deadline][month]'] = '1';
        $form['task[deadline][year]'] = '2023';
        $form['task[_token]'] = $token;

        // On soumet le formulaire.
        $client->submit($form);

        // On attend une redirection vers la page des tâches.
        $this->assertResponseRedirects('/task/');
    }

    /**
     * Test l'édition d'une tâche par admin.
     *
     * @return void
     */
    public function testEdit(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        // Recherche l'utilisateur admin.
        $testUser = $userRepository->findOneBy(['username' => 'admin']);

        // Simule $testUser est connecté.
        $client->loginUser($testUser);

        // Recherche une tâche.
        $task = $taskRepository->findOneBy([], ['id' => 'desc']);
        $testURI = '/task/' . $task->getId() . '/edit';

        $crawler = $client->request('GET', $testURI);

        // Sélectionne le bouton du formulaire.
        $buttonCrawlerNode = $crawler->selectButton('Modifier');

        // Récupére l'objet Form du formulaire appartenant à ce bouton.
        $form = $buttonCrawlerNode->form();

        // Récupère le token du formulaire.
        $token = (string) $form->get('task[_token]')->getValue();

        // On définit les valeurs saisies dans le formulaire.
        $form['task[title]'] = 'autre titre';
        $form['task[content]'] = 'autre contenu';
        $form['task[deadline][day]'] = '1';
        $form['task[deadline][month]'] = '1';
        $form['task[deadline][year]'] = '2023';
        $form['task[_token]'] = $token;

        // On soumet le formulaire.
        $client->submit($form);

        // On attend une redirection vers la page des tâches.
        $this->assertResponseRedirects('/task/');
    }

    /**
     * Test la suppression d'une tâche.
     *
     * @return void
     */
    public function testDelete(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        // Recherche des utilisateurs.
        $admin = $userRepository->findOneBy(['username' => 'admin']);

        // Recherche une tâche de l'utilisateur admin.
        $task = $taskRepository->findOneBy(['user' => $admin], ['id' => 'desc']);

        // Url de suppression.
        $url = '/task/' . $task->getId() . '/delete';

        // Simule anonyme est connecté.
        $client->loginUser($admin);

        // Test la suppression de la tâche par anonyme.
        $client->request('GET', $url);

        $this->assertResponseRedirects('/task/');
    }

    /**
     * Test la suppression d'une tâche non autorisée.
     *
     * @return void
     */
    public function testDeleteUnauthorized(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        // Recherche des utilisateurs.
        $admin = $userRepository->findOneBy(['username' => 'admin']);
        $anonyme = $userRepository->findOneBy(['username' => 'anonyme']);

        // Recherche une tâche de l'utilisateur admin.
        $task = $taskRepository->findOneBy(['user' => $admin], ['id' => 'desc']);

        // Url de suppression.
        $url = '/task/' . $task->getId() . '/delete';

        // Simule anonyme est connecté.
        $client->loginUser($anonyme);

        // Test la suppression de la tâche par anonyme.
        $client->request('GET', $url);

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Marquer une tâche comme faite.
     *
     * @return void
     */
    public function testSetIsDone(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        // Recherche des utilisateurs.
        $admin = $userRepository->findOneBy(['username' => 'admin']);

        // Recherche une tâche de l'utilisateur admin.
        $task = $taskRepository->findOneBy(['user' => $admin, 'isDone' => 0], ['id' => 'desc']);

        // Url de suppression.
        $url = '/task/' . $task->getId() . '/toggle';

        // Simule anonyme est connecté.
        $client->loginUser($admin);

        // Test la suppression de la tâche par anonyme.
        $client->request('GET', $url);

        // On contrôle que la tâche est bien validée.
        $this->assertEquals($task->isIsDone(), 1);

        // On attend une redirection vers l'index des tâches.
        $this->assertResponseRedirects('/task/');

        // Vérifie la présence d'un message de confirmation.
        $crawler = $client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }
}