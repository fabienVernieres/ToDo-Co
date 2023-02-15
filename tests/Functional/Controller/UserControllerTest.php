<?php

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test le contrôleur UserController
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Test l'accès à la liste des utilisateurs.
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

        // Test l'accès à la page des utilisateurs.
        $client->request('GET', '/user/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Utilisateurs');
    }

    /**
     * Test l'ajout d'un utilisateur par un administrateur.
     *
     * @return void
     */
    public function testNew(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Recherche l'utilisateur admin.
        $testUser = $userRepository->findOneBy(['username' => 'admin']);

        // Simule admin est connecté.
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/user/new');

        // Sélectionne le bouton du formulaire.
        $buttonCrawlerNode = $crawler->selectButton('Envoyer');

        // Récupére l'objet Form du formulaire appartenant à ce bouton.
        $form = $buttonCrawlerNode->form();

        // Récupère le token du formulaire.
        $token = (string) $form->get('user[_token]')->getValue();

        // On définit les valeurs saisies dans le formulaire.
        $form['user[username]'] = 'John';
        $form['user[password][first]'] = '123456';
        $form['user[password][second]'] = '123456';
        $form['user[email]'] = 'john@doe.fr';
        $form['user[_token]'] = $token;

        // On soumet le formulaire.
        $client->submit($form);

        // On attend une redirection vers la page de connexion.
        $this->assertResponseRedirects('/user/');
    }

    /**
     * Test l'édition d'un utilisateur par admin.
     *
     * @return void
     */
    public function testEdit(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Recherche l'utilisateur admin.
        $testUser = $userRepository->findOneBy(['username' => 'admin']);

        // Recherche l'utilisateur à modifier.
        $testUserToEdit = $userRepository->findOneBy(['username' => 'anonyme']);

        // URI pour l'édition de l'utilisateur $testUserToEdit.
        $testUri = '/user/' . $testUserToEdit->getId() . '/edit';

        // Simule admin est connecté.
        $client->loginUser($testUser);

        $crawler = $client->request('GET', $testUri);

        // Sélectionne le bouton du formulaire.
        $buttonCrawlerNode = $crawler->selectButton('Modifier');

        // Récupére l'objet Form du formulaire appartenant à ce bouton.
        $form = $buttonCrawlerNode->form();

        // Récupère le token du formulaire.
        $token = (string) $form->get('user[_token]')->getValue();

        // On définit les valeurs saisies dans le formulaire.
        $form['user[password][first]'] = '123456';
        $form['user[password][second]'] = '123456';
        $form['user[_token]'] = $token;

        // On soumet le formulaire.
        $client->submit($form);

        // On attend une redirection vers la page des utilisateurs.
        $this->assertResponseRedirects('/user/');
    }
}
