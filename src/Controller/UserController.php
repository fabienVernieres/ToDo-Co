<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\AdminType;
use App\Repository\UserRepository;
use App\Security\AppCustomAuthenticator;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * Cache du FileSystem.
     * @var FileSystemAdapter
     */
    private FilesystemAdapter $cache;

    /**
     * Nom du fichier cache.
     * @var string
     */
    private string $cacheName;

    public function __construct(Security $security)
    {
        $this->cache = new FilesystemAdapter();
        $this->cacheName = $security->getUser() ? ('users' . $security->getUser()->getId()) : 'users';
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    /**
     * Affiche la liste des utilisateurs
     *
     * @param  UserRepository $userRepository
     * @return Response
     */
    public function index(UserRepository $userRepository): Response
    {
        // Mise en cache de la liste des utilisateurs.
        $users = $this->cache->get($this->cacheName, function (ItemInterface $item) use ($userRepository) {
            $item->expiresAfter(3600);
            $users = $userRepository->findAll();

            return $users;
        });

        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    /**
     * Ajoute un utilisateur
     *
     * @param  Request $request
     * @param  UserRepository $userRepository
     * @param  UserPasswordHasherInterface $passwordHasher
     * @param  Security $security
     * @return Response
     */
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, Security $security): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Suppression du cache.
            $this->cache->deleteItem($this->cacheName);

            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $this->addFlash('success', "L'utilisateur a bien ??t?? ajout??.");
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    /**
     * Modifie le r??le d'un utilisateur
     *
     * @param  Request $request
     * @param  User $user
     * @param  UserRepository $userRepository
     * @param  UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Suppression du cache.
            $this->cache->deleteItem($this->cacheName);

            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
