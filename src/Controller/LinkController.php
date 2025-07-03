<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use App\Service\CodeGenerator;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

date_default_timezone_set('Europe/Moscow');

class LinkController extends AbstractController
{

    #[Route('/', name: 'link_list', methods: ['GET'])]

    public function index(LinkRepository $repo): Response
    {
        return $this->render('link/index.html.twig', [
            'links' => $repo->findAll()
        ]);
    }

    /**
     * @throws RandomException
     */
    #[Route('/new', name: 'link_new',  methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CodeGenerator $codeGenerator): Response
    {
        $link = new Link();
        $form = $this->createForm(LinkType::class, $link);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $code = $codeGenerator->generate();
            $link
                ->setCreatedAt(new DateTimeImmutable())
                ->setShortUrl($code)
                ->setVisitCount(0);
            $em->persist($link);
            $em->flush();

            return $this->redirectToRoute('link_list');
        }

        return $this->render('link/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/visit/{code}', name: 'link_visit', methods: ['GET'])]
    public function visit(string $code, LinkRepository $repo, EntityManagerInterface $em): Response
    {
        $link = $repo->findOneByShortCode($code);
        if (!$link) {
            return $this->render('link/error.html.twig');
        }
        if ($link->getExpiresAt() && new \DateTime() > $link->getExpiresAt()) {
            return $this->render('link/expired.html.twig');
        }

        if ($link->isOneTime() && $link->getVisitCount() > 0) {
            return $this->render('link/expired.html.twig');
        }

        $link
            ->setVisitCount($link->getVisitCount() + 1)
            ->setLastUsedAt(new DateTime());
        $em->flush();


        return new RedirectResponse($link->getFullUrl(), Response::HTTP_FOUND);
    }

    #[Route('/delete/{id}', name: 'link_delete', methods: ['GET'])]
    public function delete(Link $link, EntityManagerInterface $em): Response
    {

        $em->remove($link);
        $em->flush();

        return $this->redirectToRoute('link_list');
    }

    #[Route('/delete-multiple', name: 'link_delete_multiple', methods: ['POST'])]
    public function deleteMultiple(Request $request, LinkRepository $repo, EntityManagerInterface $em): Response
    {
        $ids = $request->request->all('delete_ids');
        foreach ($ids as $id) {
            $link = $repo->find($id);
            if ($link) {
                $em->remove($link);
            }
        }
        $em->flush();

        return $this->redirectToRoute('link_list');
    }
}

