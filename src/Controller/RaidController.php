<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RaidEvent;
use App\Entity\User;
use App\Enum\RaidRole;
use App\Form\RaidEventType;
use App\Repository\RaidEventRepository;
use App\Repository\RaidSignupRepository;
use App\Service\RaidCompositionService;
use App\Service\RaidManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/raids')]
final class RaidController extends AbstractController
{
    /**
     * ✅ Calendrier + création rapide via modal
     *
     * - GET  : affiche le calendrier
     * - POST : traite le formulaire "Créer un raid" du modal
     */
    #[Route('', name: 'raids_calendar', methods: ['GET', 'POST'])]
    public function calendar(
        Request $request,
        RaidEventRepository $raidRepo,
        RaidManager $raidManager,
        RaidCompositionService $raidComp,
        #[Autowire(service: 'limiter.raid_create')] RateLimiterFactory $raidCreateLimiter
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $me */
        $me = $this->getUser();
        \assert($me instanceof User);

        // ✅ Mois affiché : ?month=YYYY-MM, sinon mois actuel
        $month = (string) $request->query->get('month', (new \DateTimeImmutable())->format('Y-m'));
        $firstDay = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $month . '-01 00:00:00')
            ?: new \DateTimeImmutable('first day of this month 00:00:00');
        $nextMonth = $firstDay->modify('first day of next month 00:00:00');

        // ✅ Données calendrier
        $raids = $raidRepo->findBetween($firstDay, $nextMonth);

        // Indexation par jour (Y-m-d) pour le calendrier
        $raidsByDay = [];
        foreach ($raids as $r) {
            $key = $r->getStartsAt()->format('Y-m-d');
            $raidsByDay[$key][] = $r;
        }

        // ✅ Form "Créer un raid" (modal)
        $raid = new RaidEvent();
        $form = $this->createForm(RaidEventType::class, $raid);
        $form->handleRequest($request);

        // ✅ POST : si le modal est soumis
        if ($form->isSubmitted()) {
            // Anti-spam : max 3 créations/minute
            $limit = $raidCreateLimiter->create('user_'.$me->getId());
            if (!$limit->consume(1)->isAccepted()) {
                $this->addFlash('danger', 'Trop de créations de raids, réessaie dans un moment.');
                return $this->redirectToRoute('raids_calendar', ['month' => $firstDay->format('Y-m')]);
            }

            if ($form->isValid()) {
                try {
                    /**
                     * ✅ FIX IMPORTANT :
                     * - Quand un champ TextType est vide, Symfony peut envoyer `null`
                     * - Ton entity attend string -> crash si setTitle(null)
                     *
                     * => On force une string safe + fallback automatique
                     */
                    $safeTitle = trim((string) $raid->getTitle());

                    if ($safeTitle === '') {
                        $raid->setTitle($raidComp->getLabel($raid->getRaidKey()));
                    } else {
                        $raid->setTitle($safeTitle);
                    }

                    $raidManager->createRaid($me, $raid);

                    $this->addFlash('success', 'Raid créé ✅');
                    return $this->redirectToRoute('raids_show', ['id' => $raid->getId()]);
                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            } else {
                $this->addFlash('danger', 'Formulaire invalide : vérifie les champs.');
            }
        }

        return $this->render('raids/calendar.html.twig', [
            'month' => $firstDay,
            'prevMonth' => $firstDay->modify('-1 month'),
            'nextMonth' => $firstDay->modify('+1 month'),
            'raidsByDay' => $raidsByDay,

            // ✅ map raidKey => label (ICC 25, etc.)
            'raidLabels' => $raidComp->getRaidLabels(),

            // ✅ form modal
            'createForm' => $form->createView(),
        ]);
    }

    /**
     * ✅ Page dédiée "new" (optionnelle)
     */
    #[Route('/new', name: 'raids_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        RaidManager $raidManager,
        RaidCompositionService $raidComp,
        #[Autowire(service: 'limiter.raid_create')] RateLimiterFactory $raidCreateLimiter
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $me */
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
                $safeTitle = trim((string) $raid->getTitle());

                if ($safeTitle === '') {
                    $raid->setTitle($raidComp->getLabel($raid->getRaidKey()));
                } else {
                    $raid->setTitle($safeTitle);
                }

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
        RaidSignupRepository $signupRepo,
        RaidCompositionService $raidComp
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $me */
        $me = $this->getUser();
        \assert($me instanceof User);

        $signups = $signupRepo->findForRaid($raid);
        $alreadySigned = $signupRepo->findOneByRaidAndUser($raid, $me) !== null;

        // ✅ Label du raid (ICC 25, Naxx, ...)
        $raidLabel = $raidComp->getLabel($raid->getRaidKey());

        // ✅ Roster bars (targets/counts/pct)
        $targets = $raidComp->getTargets($raid->getRaidKey());
        $counts = $raidComp->countRoles($signups);

        $pct = [
            'tank' => $targets['tank'] > 0 ? (int) round(min(100, ($counts['tank'] / $targets['tank']) * 100)) : 0,
            'heal' => $targets['heal'] > 0 ? (int) round(min(100, ($counts['heal'] / $targets['heal']) * 100)) : 0,
            'dps'  => $targets['dps']  > 0 ? (int) round(min(100, ($counts['dps']  / $targets['dps'])  * 100)) : 0,
        ];

        return $this->render('raids/show.html.twig', [
            'raid' => $raid,
            'raidLabel' => $raidLabel,
            'signups' => $signups,
            'alreadySigned' => $alreadySigned,

            // 👇 pour les progress bars
            'targets' => $targets,
            'counts' => $counts,
            'pct' => $pct,
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

        /** @var User $me */
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
            $role = RaidRole::from($roleValue);
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

        /** @var User $me */
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