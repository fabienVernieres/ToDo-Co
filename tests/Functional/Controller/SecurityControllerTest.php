<?php

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test le contrôleur SecurityControllerTest
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Test l'accès à la page de login
     *
     * @return void
     */
    public function testLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Veuillez vous connecter');
    }

    /**
     * Test une tentative de connexion avec de mauvais identifiants
     *
     * @return void
     */
    public function testInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        // Sélectionne le bouton du formulaire.
        $buttonCrawlerNode = $crawler->selectButton('S\'identifier');

        // Récupére l'objet Form du formulaire appartenant à ce bouton.
        $form = $buttonCrawlerNode->form();

        // On définit les valeurs saisies dans le formulaire.
        $form['username'] = 'inconnu';
        $form['password'] = '123';

        // On soumet le formulaire.
        $client->submit($form);

        // On attend une redirection vers la page de login.
        $this->assertResponseRedirects('/login');
    }

    /**
     * Test une tentative de connexion avec un mauvais mot de passe
     *
     * @return void
     */
    public function testErrorPassword(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        // Sélectionne le bouton du formulaire.
        $buttonCrawlerNode = $crawler->selectButton('S\'identifier');

        // Récupére l'objet Form du formulaire appartenant à ce bouton.
        $form = $buttonCrawlerNode->form();

        // On définit les valeurs saisies dans le formulaire.
        $form['username'] = 'admin';
        $form['password'] = 'mauvaismotdepasse';

        // On soumet le formulaire.
        $client->submit($form);

        // On attend une redirection vers la page de login.
        $this->assertResponseRedirects('/login');
    }

    /**
     * Test une tentative de connexion réussie
     *
     * @return void
     */
    public function testValidLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        // Sélectionne le bouton du formulaire.
        $buttonCrawlerNode = $crawler->selectButton('S\'identifier');

        // Récupére l'objet Form du formulaire appartenant à ce bouton.
        $form = $buttonCrawlerNode->form();

        // On définit les valeurs saisies dans le formulaire.
        $form['username'] = 'admin';
        $form['password'] = '123456';

        // On soumet le formulaire.
        $client->submit($form);

        // On attend une redirection vers la page d'accueil.
        $this->assertResponseRedirects('/');
    }

    /**
     * Test si l'utilisateur est déjà connecté
     *
     * @return void
     */
    public function testUserIsAlreadyConnected(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Recherche l'utilisateur admin.
        $testUser = $userRepository->findOneBy(['username' => 'admin']);

        // Simule $testUser est connecté.
        $client->loginUser($testUser);

        $client->request('GET', '/login');

        // On attend une redirection vers la page d'accueil.
        $this->assertResponseRedirects('/');
    }
}