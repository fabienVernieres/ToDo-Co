<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task')]
class TaskController extends AbstractController
{
    #[Route('/', name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository, UserRepository $userRepository): Response
    {
        // Obtenir toutes les tâches liées à l'utilisateur
        $tasks = $taskRepository->findBy(['user' => $this->getUser()]);

        /** 
         * Si l'utilisateur a le rôle ROLE_ADMIN, lui permettre de gérer
         * les tâches liées à l'utilisateur "anonyme".
         */
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $tasksAnonymous = $taskRepository->findBy([
                'user' => $userRepository->findBy(['username' => 'anonyme'])
            ]);
            $tasks = array_merge($tasks, $tasksAnonymous);
        }
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TaskRepository $taskRepository): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Ajout de l'utilisateur lié à la tâche
            $task->setUser($this->getUser());

            $task->setCreatedAt(new \DateTimeImmutable());
            $taskRepository->save($task, true);

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        // Vérifie si l'utilisateur à les droits pour voir la tâche
        $this->denyAccessUnlessGranted('POST_VIEW', $task);
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        // Vérifie si l'utilisateur à les droits pour modifier la tâche
        $this->denyAccessUnlessGranted('POST_EDIT', $task);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task, true);

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, TaskRepository $taskRepository): Response
    {
        // Vérifie si l'utilisateur à les droits pour supprimer la tâche
        $this->denyAccessUnlessGranted('POST_DELETE', $task);

        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $taskRepository->remove($task, true);
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
}