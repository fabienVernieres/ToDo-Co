<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/task')]
class TaskController extends AbstractController
{
    /**
     * Cache du FileSystem.
     * @var FileSystemAdapter
     */
    private FilesystemAdapter $cache;

    /**
     * Nom de fichier cache.
     * @var string
     */
    private string $cacheName;

    public function __construct(Security $security)
    {
        $this->cache = new FilesystemAdapter();
        $this->cacheName = ('tasks' . $security->getUser()->getId());
    }

    #[Route('/', name: 'app_task_index', methods: ['GET'])]
    /**
     * Affiche la liste des tâches de l'utilisateur
     *
     * @param  TaskRepository $taskRepository
     * @param  UserRepository $userRepository
     * @return Response
     */
    public function index(Request $request, TaskRepository $taskRepository, UserRepository $userRepository): Response
    {
        $isdone = $request->get('isdone') == 1 ? 1 : 0;
        $title = $isdone == 0 ? 'programmée' : 'terminée';

        // Mise en cache de la liste des tâches liées à l'utilisateur.
        $tasks = $this->cache->get(
            $isdone . $this->cacheName,
            function (ItemInterface $item) use ($taskRepository, $isdone, $userRepository) {
                $item->expiresAfter(3600);
                $tasks = $taskRepository->findByUser($this->getUser(), $isdone);

                /*
                 * Si l'utilisateur a le rôle ROLE_ADMIN, lui permettre de gérer
                 * les tâches liées à l'utilisateur "anonyme".
                 */
                if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                    $tasksAnonymous = $taskRepository->findByUser(
                        $userRepository->findOneBy(['username' => 'anonyme']),
                        $isdone
                    );
                    $tasks = array_merge($tasks, $tasksAnonymous);
                }
                return $tasks;
            }
        );

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'title' => $title,
            'date'  => new \DateTimeImmutable()
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    /**
     * Ajouter une tâche
     *
     * @param  Request $request
     * @param  TaskRepository $taskRepository
     * @return Response
     */
    public function new(Request $request, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Suppression du cache.
            $this->cache->deleteItem('0' . $this->cacheName);
            $this->cache->deleteItem('1' . $this->cacheName);

            // Ajout de l'utilisateur lié à la tâche.
            $task->setUser($this->getUser());

            $task->setCreatedAt(new \DateTimeImmutable());
            $taskRepository->save($task, true);

            $this->addFlash('success', sprintf('La tâche %s a bien été ajoutée.', $task->getTitle()));

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    /**
     * Modifie une tâche
     *
     * @param  Request $request
     * @param  Task $task
     * @param  TaskRepository $taskRepository
     * @return Response
     */
    public function edit(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        // Vérifie si l'utilisateur à les droits pour modifier la tâche.
        $this->denyAccessUnlessGranted('POST_EDIT', $task);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Suppression du cache (liste).
            $this->cache->deleteItem('0' . $this->cacheName);
            $this->cache->deleteItem('1' . $this->cacheName);

            // Mis à jour de la tâche.
            $taskRepository->save($task, true);

            $this->addFlash('success', sprintf('La tâche %s a bien été modifiée.', $task->getTitle()));

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['GET'])]
    /**
     * Supprime une tâche
     *
     * @param  Request $request
     * @param  Task $task
     * @param  TaskRepository $taskRepository
     * @return Response
     */
    public function delete(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        // Vérifie si l'utilisateur à les droits pour supprimer la tâche.
        $this->denyAccessUnlessGranted('POST_DELETE', $task);

        // Suppression du cache.
        $this->cache->deleteItem('0' . $this->cacheName);
        $this->cache->deleteItem('1' . $this->cacheName);

        // Suppression de la tâche.
        $taskRepository->remove($task, true);
        $this->addFlash('success', sprintf('La tâche %s a bien été supprimée.', $task->getTitle()));

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle', name: 'app_task_toggle', methods: ['GET'])]
    /**
     * Modifie l'état d'une tâche
     *
     * @param  Task $task
     * @param  TaskRepository $taskRepository
     * @return Response
     */
    public function toggle(Task $task, TaskRepository $taskRepository): Response
    {
        // Suppression du cache.
        $this->cache->deleteItem('0' . $this->cacheName);
        $this->cache->deleteItem('1' . $this->cacheName);

        // Changement du status de la tâche.
        $task->setIsDone(!$task->isIsDone());
        $taskRepository->save($task, true);

        if ($task->isIsDone()) {
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
            return $this->redirectToRoute('app_task_index');
        }

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme à faire.', $task->getTitle()));
        return $this->redirectToRoute('app_task_index', ['isdone' => 1]);
    }
}