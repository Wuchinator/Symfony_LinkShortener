<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\LinkType;
use App\Repository\LinkRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class LinkController extends AbstractController
{
    #[Route('/', name: 'link_list')]
    public function index(LinkRepository $repo): Response
    {
        return $this->render('link/index.html.twig', [
            'links' => $repo->findAll()
        ]);
    }

    /**
     * @throws RandomException
     */
    #[Route('/new', name: 'link_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $link = new Link();
        $form = $this->createForm(LinkType::class, $link);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $link
                ->setCreatedAt(new \DateTimeImmutable())
                ->setShortUrl(bin2hex(random_bytes(3)))
                ->setVisitCount(0);
            $em->persist($link);
            $em->flush();

            return $this->redirectToRoute('link_list');
        }

        return $this->render('link/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'link_delete')]
    public function delete(Link $link, EntityManagerInterface $em): Response
    {
        $em->remove($link);
        $em->flush();

        return $this->redirectToRoute('link_list');
    }

    #[Route('/visit/{code}', name: 'link_visit')]
    public function visit(string $code, LinkRepository $repo, EntityManagerInterface $em): RedirectResponse
    {
        $link = $repo->findOneByShortCode($code);
        if (!$link) {
            throw $this->createNotFoundException('Ссылка не найдена');
        }

        $link
            ->setVisitCount($link->getVisitCount() + 1)
            ->setLastUsedAt(new DateTime());
        $em->flush();

        return new RedirectResponse($link->getFullUrl(), Response::HTTP_FOUND);
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

