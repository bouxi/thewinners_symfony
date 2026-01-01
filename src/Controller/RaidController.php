<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RaidEvent;
use App\Entity\User;
use App\Form\RaidEventType;
use App\Repository\RaidEventRepository;
use App\Repository\RaidSignupRepository;
use App\Service\RaidManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Enum\RaidRole;


#[Route('/raids')]
final class RaidController extends AbstractController
{
    #[Route('', name: 'raids_calendar', methods: ['GET'])]
    public function calendar(Request $request, RaidEventRepository $raidRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Mois affiché : ?month=YYYY-MM, sinon mois actuel
        $month = (string) $request->query->get('month', (new \DateTimeImmutable())->format('Y-m'));
        $firstDay = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $month . '-01 00:00:00') ?: new \DateTimeImmutable('first day of this month 00:00:00');
        $nextMonth = $firstDay->modify('first day of next month 00:00:00');

        $raids = $raidRepo->findBetween($firstDay, $nextMonth);

        // Indexation par jour (Y-m-d) pour le calendrier
        $raidsByDay = [];
        foreach ($raids as $raid) {
            $key = $raid->getStartsAt()->format('Y-m-d');
            $raidsByDay[$key][] = $raid;
        }

        return $this->render('raids/calendar.html.twig', [
            'month' => $firstDay,
            'prevMonth' => $firstDay->modify('-1 month'),
            'nextMonth' => $firstDay->modify('+1 month'),
            'raidsByDay' => $raidsByDay,
        ]);
    }

    #[Route('/new', name: 'raids_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        RaidManager $raidManager,
        #[Autowire(service: 'limiter.raid_create')] RateLimiterFactory $raidCreateLimiter
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        \assert($me instanceof User);

        // Anti-spam : max 3 créations/minute
        $limit = $raidCreateLimiter->create('user_'.$me->getId());
        if (!$limit->consume(1)->isAccepted()) {
            $this->addFlash('danger', 'Trop de créations de raids, réessaie dans un moment.');
            return $this->redirectToRoute('raids_calendar');
        }

        $raid = new RaidEvent();
        $form = $this->createForm(RaidEventType::class, $raid);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $raidManager->createRaid($me, $raid);
                $this->addFlash('success', 'Raid créé ✅');
                return $this->redirectToRoute('raids_show', ['id' => $raid->getId()]);
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('raids/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'raids_show', methods: ['GET'])]
    public function show(
        RaidEvent $raid,
        RaidSignupRepository $signupRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        \assert($me instanceof User);

        $signups = $signupRepo->findForRaid($raid);
        $alreadySigned = $signupRepo->findOneByRaidAndUser($raid, $me) !== null;

        return $this->render('raids/show.html.twig', [
            'raid' => $raid,
            'signups' => $signups,
            'alreadySigned' => $alreadySigned,
        ]);
    }

    #[Route('/{id}/signup', name: 'raids_signup', methods: ['POST'])]
    public function signup(
        RaidEvent $raid,
        Request $request,
        RaidManager $raidManager,
        #[Autowire(service: 'limiter.raid_signup')] RateLimiterFactory $raidSignupLimiter
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        \assert($me instanceof User);

        if (!$this->isCsrfTokenValid('raid_signup_'.$raid->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('raids_show', ['id' => $raid->getId()]);
        }

        // Anti-spam inscriptions
        $limit = $raidSignupLimiter->create('user_'.$me->getId());
        if (!$limit->consume(1)->isAccepted()) {
            $this->addFlash('danger', 'Trop d’actions rapides, réessaie dans un moment.');
            return $this->redirectToRoute('raids_show', ['id' => $raid->getId()]);
        }



        $roleValue = (string) $request->request->get('role', RaidRole::DPS->value);

        try {
            $role = RaidRole::from($roleValue); // valide automatiquement
        } catch (\ValueError) {
            $this->addFlash('danger', 'Rôle invalide.');
            return $this->redirectToRoute('raids_show', ['id' => $raid->getId()]);
        }

        $note = trim((string) $request->request->get('note', '')) ?: null;

        try {
            $raidManager->signup($me, $raid, $role, $note);
            $this->addFlash('success', 'Inscription enregistrée ✅');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('raids_show', ['id' => $raid->getId()]);
    }

    #[Route('/{id}/leave', name: 'raids_leave', methods: ['POST'])]
    public function leave(
        RaidEvent $raid,
        Request $request,
        RaidManager $raidManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        \assert($me instanceof User);

        if (!$this->isCsrfTokenValid('raid_leave_'.$raid->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('raids_show', ['id' => $raid->getId()]);
        }

        $raidManager->leave($me, $raid);
        $this->addFlash('success', 'Tu es désinscrit ✅');

        return $this->redirectToRoute('raids_show', ['id' => $raid->getId()]);
    }
}
