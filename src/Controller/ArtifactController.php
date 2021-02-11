<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\File;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArtifactController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SluggerInterface */
    private $slugger;

    public function __construct(EntityManagerInterface $entityManagerInterface, SluggerInterface $sluggerInterface)
    {
        $this->entityManager = $entityManagerInterface;
        $this->slugger = $sluggerInterface;
    }

    public function raw(Request $request): Response
    {
        $projectRepository = $this->entityManager->getRepository(Project::class);

        /** @var Project $project */
        if (null === $project = $projectRepository->findOneBy(['name' => $request->attributes->get('project')])) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $fileRepository = $this->entityManager->getRepository(File::class);

        /** @var File $file */
        if (null === $file = $fileRepository->findOneBy(['project' => $project, 'name' => $request->attributes->get('artifact')])) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        return new Response(
            file_get_contents(sprintf('%s%s%s', $this->getParameter('files_dir'), DIRECTORY_SEPARATOR, $file->getPath())),
            Response::HTTP_OK,
            ['Content-Type' => $file->getType()],
        );
    }

    public function add(Request $request): Response
    {
        if (!$request->files->count() || !$request->request->has('project')) {
            return new Response(null, Response::HTTP_NOT_ACCEPTABLE);
        }

        $projectRepository = $this->entityManager->getRepository(Project::class);

        /** @var Project $project */
        if (null === $project = $projectRepository->findOneBy(['name' => $projectName = $request->request->get('project')])) {
            $project = (new Project)->setName($projectName);
            $this->entityManager->persist($project);
        }

        $fileRepository = $this->entityManager->getRepository(File::class);

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        foreach ($request->files->all() as $file) {
            $filename = sprintf(
                '%s.%s',
                $this->slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION),
            );

            $hash = hash('sha256', sprintf('%s/%s', $project->getName(), $filename));
            $type = $file->getMimeType();
            $file->move($this->getParameter('files_dir'), $hash);

            /** @var File $file */
            if (null === $file = $fileRepository->findOneBy(['project' => $project, 'name' => $filename])) {
                $file = (new File)->setName($filename)->setPath($hash)->setProject($project)->setType($type);
                $this->entityManager->persist($file);
            }
        }

        $this->entityManager->flush();
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
