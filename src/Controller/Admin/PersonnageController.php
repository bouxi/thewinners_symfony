<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Personnage;
use App\Entity\User;
use App\Form\PersonnageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/personnage', name: 'admin_personnage_')]
final class PersonnageController extends AbstractController
{
    #[Route('/nouveau', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // ✅ Sécurité : admin only
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User $user */
        $user = $this->getUser();
        \assert($user instanceof User);

        $personnage = new Personnage();

        // ✅ Relation "propre" (synchronise les deux côtés)
        $user->addPersonnage($personnage);

        $form = $this->createForm(PersonnageType::class, $personnage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($personnage);
            $em->flush();

            $this->addFlash('success', 'Ton personnage a été ajouté ✅');

            // ✅ adapte si tu veux une route admin dédiée
            return $this->redirectToRoute('guild_members');
        }

        return $this->render('admin/personnage/new.html.twig', [
            'form' => $form->createView(),
            'wow'  => \App\Service\WowData::CLASSES, // TEMP (prochaine étape: provider JSON)
        ]);
    }
}