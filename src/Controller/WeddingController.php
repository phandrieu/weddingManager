<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Entity\Song;
use App\Entity\User;
use App\Entity\Wedding;
use App\Entity\WeddingSongSelection;
use App\Form\WeddingFormType;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Repository\WeddingRepository;
use App\Repository\SongRepository;
use App\Repository\SongTypeRepository;
use App\Service\InvitationWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[Route('/mariages')]
class WeddingController extends AbstractController
{
    #[Route('/', name: 'app_wedding_index')]
    public function index(Request $request, WeddingRepository $repo): Response
    {
        $filter = $request->query->get('filter', 'upcoming');
        if (!in_array($filter, ['upcoming', 'archived', 'all'], true)) {
            $filter = 'upcoming';
        }

        $search = trim((string) $request->query->get('q', ''));
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = 10;

        $qb = $repo->createQueryBuilder('w')
            ->leftJoin('w.marie', 'marie')
            ->leftJoin('w.mariee', 'mariee')
            ->leftJoin('w.musicians', 'musician')
            ->addSelect('marie', 'mariee')
            ->distinct();

        if ($filter === 'archived') {
            $qb->andWhere('w.archive = true');
        } elseif ($filter === 'upcoming') {
            $qb->andWhere('(w.archive = false OR w.archive IS NULL)');
        }

        if ($search !== '') {
            $normalizedSearch = mb_strtolower($search);
            $qb->andWhere(<<<'DQL'
                LOWER(CONCAT(COALESCE(marie.firstName, ''), CONCAT(' ', COALESCE(marie.name, '')))) LIKE :search
                OR LOWER(CONCAT(COALESCE(mariee.firstName, ''), CONCAT(' ', COALESCE(mariee.name, '')))) LIKE :search
                OR LOWER(CONCAT(COALESCE(marie.firstName, ''), CONCAT(' & ', COALESCE(mariee.firstName, '')))) LIKE :search
                OR LOWER(
                    CONCAT(
                        CONCAT(
                            CONCAT('Mariage de ', COALESCE(marie.firstName, '')),
                            CONCAT(' ', COALESCE(marie.name, ''))
                        ),
                        CONCAT(
                            ' & ',
                            CONCAT(COALESCE(mariee.firstName, ''), CONCAT(' ', COALESCE(mariee.name, '')))
                        )
                    )
                ) LIKE :search
            DQL);
            $qb->setParameter('search', '%' . $normalizedSearch . '%');
        }

        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && $currentUser) {
            if ($this->isGranted('ROLE_MUSICIAN')) {
                $qb->andWhere(':currentUser MEMBER OF w.musicians');
                $qb->setParameter('currentUser', $currentUser);
            } else {
                $qb->leftJoin('w.parishUsers', 'parishUser');
                $qb->addSelect('parishUser');
                $qb->andWhere('w.createdBy = :currentUser OR w.marie = :currentUser OR w.mariee = :currentUser OR parishUser = :currentUser');
                $qb->setParameter('currentUser', $currentUser);
            }
        }

        $qb->orderBy('w.date', 'DESC');

        $countQb = clone $qb;
        $paginatorForCount = new Paginator($countQb, true);
        $total = count($paginatorForCount);
        $pages = max(1, (int) ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $paginator = new Paginator($qb, true);
        $weddings = iterator_to_array($paginator);

        return $this->render('wedding/index.html.twig', [
            'weddings' => $weddings,
            'filter' => $filter,
            'search' => $search,
            'pagination' => [
                'total' => $total,
                'pages' => $pages,
                'page' => $page,
                'perPage' => $perPage,
            ],
        ]);
    }

    #[Route('/duplicates/check', name: 'app_wedding_check_duplicates', methods: ['POST'])]
    public function checkDuplicates(Request $request, WeddingRepository $repo): JsonResponse
    {
    $payload = json_decode($request->getContent(), true) ?? [];
        $emails = [];

        if (is_array($payload)) {
            $emails = array_filter([
                $payload['email_marie'] ?? null,
                $payload['email_mariee'] ?? null,
            ]);
        }

        if (empty($emails)) {
            return new JsonResponse([
                'weddings' => [],
                'message' => 'Aucun email fourni.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $weddings = $repo->findPotentialDuplicatesByEmails($emails);

        $results = array_map(function (Wedding $wedding) {
            $participants = array_filter([
                $this->formatParticipant($wedding->getMarie(), 'Marié'),
                $this->formatParticipant($wedding->getMariee(), 'Mariée'),
            ]);

            foreach ($wedding->getMusicians() as $musician) {
                $participants[] = $this->formatParticipant($musician, 'Musicien');
            }

            foreach ($wedding->getParishUsers() as $parishUser) {
                $participants[] = $this->formatParticipant($parishUser, 'Paroisse');
            }

            $participants = array_values(array_unique(array_filter($participants)));

            $groomName = $this->resolveDisplayName($wedding->getMarie(), 'Marié inconnu');
            $brideName = $this->resolveDisplayName($wedding->getMariee(), 'Mariée inconnue');

            return [
                'id' => $wedding->getId(),
                'title' => sprintf('Mariage de %s & %s', $groomName, $brideName),
                'date' => $wedding->getDate()?->format('d/m/Y'),
                'church' => $wedding->getChurch(),
                'participants' => $participants,
                'viewUrl' => $this->generateUrl('app_wedding_view', ['id' => $wedding->getId()]),
            ];
        }, $weddings);

        return new JsonResponse([
            'weddings' => $results,
            'message' => empty($results)
                ? 'Aucun mariage correspondant n’a été trouvé.'
                : sprintf('%d mariage(s) potentiellement correspondant(s) ont été trouvés.', count($results)),
        ]);
    }

    #[Route('/{id}/request-access', name: 'app_wedding_request_access', methods: ['POST'])]
    public function requestAccess(
        Wedding $wedding,
        Request $request,
        MailerInterface $mailer
    ): JsonResponse {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            return new JsonResponse([
                'message' => 'Connectez-vous pour effectuer cette demande.',
            ], Response::HTTP_FORBIDDEN);
        }

    $payload = json_decode($request->getContent(), true) ?? [];
        $message = isset($payload['message']) ? trim((string) $payload['message']) : '';
        $searchedEmails = isset($payload['emails']) && is_array($payload['emails']) ? $payload['emails'] : [];

        $recipientEmails = [];
        if ($wedding->getMarie()?->getEmail()) {
            $recipientEmails[] = $wedding->getMarie()->getEmail();
        }
        if ($wedding->getMariee()?->getEmail()) {
            $recipientEmails[] = $wedding->getMariee()->getEmail();
        }

        foreach ($wedding->getMusicians() as $musician) {
            if ($musician->getEmail()) {
                $recipientEmails[] = $musician->getEmail();
            }
        }

        foreach ($wedding->getParishUsers() as $parishUser) {
            if ($parishUser->getEmail()) {
                $recipientEmails[] = $parishUser->getEmail();
            }
        }

        $recipientEmails = array_values(array_unique(array_filter($recipientEmails)));

        if (empty($recipientEmails)) {
            return new JsonResponse([
                'message' => 'Aucun contact n’est rattaché à ce mariage pour envoyer la demande.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $requesterName = trim(($currentUser->getFirstName() ?? '') . ' ' . ($currentUser->getName() ?? ''));
        if ($requesterName === '') {
            $requesterName = $currentUser->getEmail() ?? 'Un utilisateur';
        }

        $viewUrl = $this->generateUrl('app_wedding_view', ['id' => $wedding->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from(new Address('noreply@notremessedemariage.fr', $requesterName." via Notre Messe de Mariage"))
            ->subject('Demande d’ajout au mariage ' . $requesterName)
            ->html($this->renderView('emails/wedding/request_access.html.twig', [
                'wedding' => $wedding,
                'requester' => $currentUser,
                'message' => $message,
                'searchedEmails' => $searchedEmails,
                'viewUrl' => $viewUrl,
            ]));

        foreach ($recipientEmails as $recipientEmail) {
            $email->addTo(new Address($recipientEmail));
        }

        $mailer->send($email);

        return new JsonResponse([
            'message' => 'Votre demande a été envoyée aux responsables du mariage.',
        ]);
    }

    private function formatParticipant(?User $user, string $role): ?string
    {
        if (!$user) {
            return null;
        }

        $name = $this->resolveDisplayName($user, 'Identité inconnue');

        return sprintf('%s (%s)', $name, $role);
    }

    private function resolveDisplayName(?User $user, string $fallback): string
    {
        if (!$user) {
            return $fallback;
        }

        $name = trim(($user->getFirstName() ?? '') . ' ' . ($user->getName() ?? ''));
        if ($name !== '') {
            return $name;
        }

        if ($user->getEmail()) {
            return $user->getEmail();
        }

        return $fallback;
    }

    #[Route('/view/{id}', name: 'app_wedding_view')]
    public function view(Wedding $wedding, SongTypeRepository $songTypeRepo): Response
    {
        $includeMesseTypes = true === $wedding->isMesse();
        if ($includeMesseTypes) {
            $songTypes = $songTypeRepo->findAll();
        } else {
            $songTypes = $songTypeRepo->findBy(['messe' => false], ['id' => 'ASC']);
        }

        $songs = [];
        foreach ($wedding->getMusicians() as $musician) {
            foreach ($musician->getRepertoire() as $song) {
                $songs[$song->getId()] = $song;
            }
        }

        return $this->render('wedding/view.html.twig', [
            'wedding' => $wedding,
            'songTypes' => $songTypes,
            'availableSongs' => $songs,
        ]);
    }

    #[Route('/edit/{id?0}', name: 'app_wedding_edit')]
    public function edit(
        Request $request,
        WeddingRepository $repo,
        SongRepository $songRepo,
        SongTypeRepository $songTypeRepo,
        Wedding $wedding = null
    ): Response {
        if (!$wedding) {
            $wedding = new Wedding();
        }

        $isNewWedding = $wedding->getId() === null;
        $currentUser = $this->getUser();
        if ($isNewWedding && $currentUser instanceof User && !$wedding->getCreatedBy()) {
            $wedding->setCreatedBy($currentUser);
            
            // Assignation automatique selon le rôle
            if ($this->isGranted('ROLE_MUSICIAN')) {
                $wedding->addMusician($currentUser);
            }
            if ($this->isGranted('ROLE_PARISH')) {
                $wedding->addParishUser($currentUser);
            }
            // Pour ROLE_USER uniquement (ni musicien ni paroisse), on demandera marié/mariée via modal
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isParishOrMusician = $this->isGranted('ROLE_PARISH') || $this->isGranted('ROLE_MUSICIAN');
        $isOnlyUser = !$this->isGranted('ROLE_MUSICIAN') && !$this->isGranted('ROLE_PARISH') && !$this->isGranted('ROLE_ADMIN');

        $form = $this->createForm(WeddingFormType::class, $wedding);
        $form->handleRequest($request);

        $selectedPaymentOption = (string) $request->request->get('payment_option', '');
        $selectedUserRole = (string) $request->request->get('user_role', ''); // marie ou mariee
        $shouldOpenPaymentModal = false;
        $shouldOpenRoleModal = false;
        $paymentOptionError = null;

        $includeMesseTypes = true === $wedding->isMesse();
        if ($includeMesseTypes) {
            $songTypes = $songTypeRepo->findAll();
        } else {
            $songTypes = $songTypeRepo->findBy(['messe' => false], ['id' => 'ASC']);
        }
        $songTypesById = [];
        foreach ($songTypes as $songType) {
            if ($songType->getId() !== null) {
                $songTypesById[$songType->getId()] = $songType;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $canPersist = true;
            
            // Gérer le rôle de l'utilisateur simple (marié/mariée)
            if ($isNewWedding && $isOnlyUser && $currentUser instanceof User) {
                if ($selectedUserRole === '') {
                    $shouldOpenRoleModal = true;
                    $canPersist = false;
                    $this->addFlash('warning', 'Veuillez sélectionner si vous êtes le marié ou la mariée.');
                } elseif ($selectedUserRole === 'marie') {
                    $wedding->setMarie($currentUser);
                } elseif ($selectedUserRole === 'mariee') {
                    $wedding->setMariee($currentUser);
                } else {
                    $shouldOpenRoleModal = true;
                    $canPersist = false;
                    $this->addFlash('danger', 'Rôle invalide sélectionné.');
                }
            }

            $selectedSongs = $form->get('songs')->getData();
            $songsToAttach = [];

            if (is_iterable($selectedSongs)) {
                foreach ($selectedSongs as $selectedSong) {
                    if ($selectedSong instanceof Song && $selectedSong->getId() !== null) {
                        $songsToAttach[$selectedSong->getId()] = $selectedSong;
                    }
                }
            }

            foreach ($wedding->getSongs()->toArray() as $song) {
                $wedding->removeSong($song);
            }

            foreach ($songsToAttach as $song) {
                $wedding->addSong($song);
            }

            $derouleRaw = $request->request->all('deroule');
            $musicianValidationRaw = $request->request->all('deroule_validation_musician');
            $parishValidationRaw = $request->request->all('deroule_validation_parish');

            $selectedSongIdsByType = [];
            if (is_array($derouleRaw)) {
                foreach ($derouleRaw as $group) {
                    if (!is_array($group)) {
                        continue;
                    }
                    foreach ($group as $typeId => $songId) {
                        $typeKey = (int) $typeId;
                        $trimmed = is_string($songId) ? trim($songId) : $songId;
                        $selectedSongIdsByType[$typeKey] = $trimmed !== '' ? (int) $trimmed : null;
                    }
                }
            }

            $musicianValidationByType = [];
            if (is_array($musicianValidationRaw)) {
                foreach ($musicianValidationRaw as $typeId => $value) {
                    $musicianValidationByType[(int) $typeId] = (string) $value === '1';
                }
            }

            $parishValidationByType = [];
            if (is_array($parishValidationRaw)) {
                foreach ($parishValidationRaw as $typeId => $value) {
                    $parishValidationByType[(int) $typeId] = (string) $value === '1';
                }
            }

            $existingSelections = [];
            foreach ($wedding->getSongSelections() as $selection) {
                $type = $selection->getSongType();
                if ($type && $type->getId() !== null) {
                    $existingSelections[$type->getId()] = $selection;
                }
            }

            foreach ($songTypesById as $typeId => $songType) {
                $selectedSongId = $selectedSongIdsByType[$typeId] ?? null;
                $musicianApproved = $musicianValidationByType[$typeId] ?? false;
                $parishApproved = $parishValidationByType[$typeId] ?? false;

                $selection = $existingSelections[$typeId] ?? null;

                if ($selectedSongId === null && !$musicianApproved && !$parishApproved) {
                    if ($selection) {
                        $wedding->removeSongSelection($selection);
                    }
                    continue;
                }

                if (!$selection) {
                    $selection = new WeddingSongSelection();
                    $selection->setWedding($wedding);
                    $selection->setSongType($songType);
                    $wedding->addSongSelection($selection);
                }

                $song = null;
                if ($selectedSongId !== null) {
                    $song = $songsToAttach[$selectedSongId] ?? $songRepo->find($selectedSongId);
                }

                $selection->setSong($song);
                $selection->setValidatedByMusician($musicianApproved);
                $selection->setValidatedByParish($parishApproved);

                if (!$selection->getSong() && !$selection->isValidatedByMusician() && !$selection->isValidatedByParish()) {
                    $wedding->removeSongSelection($selection);
                }
            }

            if ($isNewWedding && !$isAdmin) {
                if ($isParishOrMusician) {
                    // Musicien ou Paroisse : choix entre crédit, CB, ou laisser les mariés payer
                    if ($selectedPaymentOption === '') {
                        $shouldOpenPaymentModal = true;
                        $paymentOptionError = 'Veuillez sélectionner une option de paiement pour finaliser la création du mariage.';
                        $canPersist = false;
                    } else {
                        switch ($selectedPaymentOption) {
                            case 'credit':
                                if ($currentUser instanceof User && $currentUser->hasCredits()) {
                                    $currentUser->removeCredits(1);
                                    $wedding->setCreatedWithCredit(true);
                                    $wedding->setRequiresCouplePayment(false);
                                    $wedding->setIsPaid(true);
                                    $wedding->setPaymentOption('credit');

                                    $repo->save($wedding, true);
                                    $this->addFlash('success', 'Un crédit a été débité pour créer ce mariage. Les invitations sont désormais gratuites.');

                                    return $this->redirectToRoute('app_wedding_index');
                                }

                                $shouldOpenPaymentModal = true;
                                $paymentOptionError = 'Vous ne disposez pas de crédit suffisant pour cette option.';
                                $this->addFlash('danger', $paymentOptionError);
                                $canPersist = false;
                                break;

                            case 'card':
                                $wedding->setCreatedWithCredit(false);
                                $wedding->setRequiresCouplePayment(false);
                                $wedding->setIsPaid(false);
                                $wedding->setPaymentOption('card_pending_partner');

                                $session = $request->getSession();
                                $session->set('wedding_data', $wedding);
                                $session->set('wedding_payment_option', 'card_partner');

                                $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
                                $checkoutSession = $stripe->checkout->sessions->create([
                                    'payment_method_types' => ['card'],
                                    'line_items' => [[
                                        'price_data' => [
                                            'currency' => 'eur',
                                            'product_data' => ['name' => 'Création de mariage (partenaire)'],
                                            'unit_amount' => 3990,
                                        ],
                                        'quantity' => 1,
                                    ]],
                                    'mode' => 'payment',
                                    'success_url' => $this->generateUrl('app_wedding_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                                    'cancel_url' => $this->generateUrl('app_wedding_edit', ['id' => 0], UrlGeneratorInterface::ABSOLUTE_URL),
                                ]);

                                return $this->redirect($checkoutSession->url);

                            case 'later':
                                $wedding->setCreatedWithCredit(false);
                                $wedding->setRequiresCouplePayment(true);
                                $wedding->setIsPaid(false);
                                $wedding->setPaymentOption('delegated');

                                $repo->save($wedding, true);
                                $this->addFlash('success', 'Mariage créé. Les mariés recevront une demande de paiement.');

                                return $this->redirectToRoute('app_wedding_index');

                            default:
                                $shouldOpenPaymentModal = true;
                                $paymentOptionError = 'Option de paiement inconnue.';
                                $this->addFlash('warning', $paymentOptionError);
                                $canPersist = false;
                                break;
                        }
                    }
                } else {
                    // Utilisateur simple (ROLE_USER uniquement) : paiement CB obligatoire 49,99€
                    $wedding->setCreatedWithCredit(false);
                    $wedding->setRequiresCouplePayment(false);
                    $wedding->setIsPaid(false);
                    $wedding->setPaymentOption('card_user');

                    $session = $request->getSession();
                    $session->set('wedding_data', $wedding);
                    $session->set('wedding_payment_option', 'card_user');
                    $session->set('wedding_user_role', $selectedUserRole);

                    $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
                    $checkoutSession = $stripe->checkout->sessions->create([
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => ['name' => 'Création de mariage'],
                                'unit_amount' => 4990,
                            ],
                            'quantity' => 1,
                        ]],
                        'mode' => 'payment',
                        'success_url' => $this->generateUrl('app_wedding_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'cancel_url' => $this->generateUrl('app_wedding_edit', ['id' => 0], UrlGeneratorInterface::ABSOLUTE_URL),
                    ]);

                    return $this->redirect($checkoutSession->url);
                }
            }

            if ($canPersist) {
                $repo->save($wedding, true);
                $this->addFlash('success', 'Mariage sauvegardé avec succès.');

                return $this->redirectToRoute('app_wedding_index');
            }
        }

        $availableSongs = [];
        foreach ($wedding->getMusicians() as $musician) {
            foreach ($musician->getRepertoire() as $song) {
                $availableSongs[$song->getId()] = $song;
            }
        }

        $availableSongsByType = [];

        foreach ($songTypes as $songType) {
            $songsForType = [];

            if (count($wedding->getMusicians()) > 0) {
                foreach ($wedding->getMusicians() as $musician) {
                    foreach ($musician->getRepertoire() as $song) {
                        if ($song->getTypes()->contains($songType)) {
                            $songsForType[$song->getId()] = $song;
                        }
                    }
                }
            }

            if (empty($songsForType)) {
                foreach ($songType->getSongs() as $song) {
                    $songsForType[$song->getId()] = $song;
                }
            }

            $availableSongsByType[$songType->getId()] = array_values($songsForType);
        }

        $songSelectionsByType = [];
        foreach ($wedding->getSongSelections() as $selection) {
            $type = $selection->getSongType();
            if ($type && $type->getId() !== null) {
                $songSelectionsByType[$type->getId()] = [
                    'songId' => $selection->getSong()?->getId(),
                    'validatedByMusician' => $selection->isValidatedByMusician(),
                    'validatedByParish' => $selection->isValidatedByParish(),
                ];
            }
        }

        if (empty($songSelectionsByType)) {
            foreach ($wedding->getSongs() as $song) {
                if (!$song instanceof Song || $song->getId() === null) {
                    continue;
                }
                foreach ($song->getTypes() as $type) {
                    if ($type && $type->getId() !== null && !array_key_exists($type->getId(), $songSelectionsByType)) {
                        $songSelectionsByType[$type->getId()] = [
                            'songId' => $song->getId(),
                            'validatedByMusician' => false,
                            'validatedByParish' => false,
                        ];
                    }
                }
            }
        }

        $maxStepIndex = 4;
        $requestedStep = max(0, min($maxStepIndex, $request->query->getInt('step', 0)));

        return $this->render('wedding/edit.html.twig', [
            'form' => $form->createView(),
            'wedding' => $wedding,
            'songTypes' => $songTypes,
            'availableSongs' => $availableSongs,
            'availableSongsByType' => $availableSongsByType,
            'songSelectionsByType' => $songSelectionsByType,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
            'shouldOpenPaymentModal' => $shouldOpenPaymentModal,
            'shouldOpenRoleModal' => $shouldOpenRoleModal ?? false,
            'shouldPromptPaymentOption' => $isNewWedding && !$isAdmin && $isParishOrMusician,
            'selectedPaymentOption' => $selectedPaymentOption,
            'paymentOptionError' => $paymentOptionError,
            'userCredits' => $currentUser instanceof User ? $currentUser->getCredits() : 0,
            'isOnlyUser' => $isOnlyUser,
            'initialWizardStep' => $requestedStep,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_wedding_delete', methods: ['POST'])]
    public function delete(Request $request, Wedding $wedding, WeddingRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete' . $wedding->getId(), $request->request->get('_token'))) {
            $repo->remove($wedding, true);
            $this->addFlash('success', 'Mariage supprimé avec succès.');
        }

        return $this->redirectToRoute('app_wedding_index');
    }

    #[Route('/payment/success', name: 'app_wedding_payment_success')]
    public function paymentSuccess(Request $request, WeddingRepository $repo): Response
    {
        $session = $request->getSession();
        $weddingSession = $session->get('wedding_data');
        $paymentOption = (string) $session->get('wedding_payment_option', '');
        $userRole = (string) $session->get('wedding_user_role', '');

        if ($weddingSession) {
            $em = $repo->getEntityManager();

            // Si le wedding existe déjà en base, on le recharge
            if ($weddingSession->getId()) {
                $wedding = $repo->find($weddingSession->getId());
            } else {
                $wedding = $weddingSession;
            }

            // Attacher les Users existants pour que Doctrine les gère correctement
            if ($wedding->getMarie()) {
                $wedding->setMarie($em->getReference(\App\Entity\User::class, $wedding->getMarie()->getId()));
            }

            if ($wedding->getMariee()) {
                $wedding->setMariee($em->getReference(\App\Entity\User::class, $wedding->getMariee()->getId()));
            }

            if ($wedding->getCreatedBy()) {
                $wedding->setCreatedBy($em->getReference(\App\Entity\User::class, $wedding->getCreatedBy()->getId()));
            }

            foreach ($wedding->getMusicians() as $i => $musician) {
                $wedding->getMusicians()->set(
                    $i,
                    $em->getReference(\App\Entity\User::class, $musician->getId())
                );
            }
            
            foreach ($wedding->getParishUsers() as $i => $parishUser) {
                $wedding->getParishUsers()->set(
                    $i,
                    $em->getReference(\App\Entity\User::class, $parishUser->getId())
                );
            }

            $wedding->setCreatedWithCredit(false);
            $wedding->setRequiresCouplePayment(false);
            $wedding->setIsPaid(true);

            // Met à jour l'option de paiement une fois le règlement confirmé
            if ($paymentOption !== '') {
                $wedding->setPaymentOption($paymentOption);
            } elseif ($wedding->getPaymentOption() === 'card_pending_partner') {
                $wedding->setPaymentOption('card_partner');
            }

            $repo->save($wedding, true);
            $session->remove('wedding_data');
            $session->remove('wedding_payment_option');
            $session->remove('wedding_user_role');
            $this->addFlash('success', 'Paiement réussi et mariage créé !');
        }

        return $this->redirectToRoute('app_wedding_index');
    }

    #[Route('/{id}/intervenants/remove/{type}/{userId}', name: 'app_wedding_intervenants_remove', methods: ['POST'])]
    public function removeIntervenant(
        Request $request,
        Wedding $wedding,
        string $type,
        int $userId,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $tokenId = sprintf('remove_intervenant_%s_%d_%d', $type, $wedding->getId(), $userId);
        if (!$this->isCsrfTokenValid($tokenId, (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'La suppression a été refusée. Rafraîchissez la page puis réessayez.');

            return $this->redirectToRoute('app_wedding_edit', ['id' => $wedding->getId(), 'step' => 2]);
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            $this->addFlash('warning', 'Intervenant introuvable.');

            return $this->redirectToRoute('app_wedding_edit', ['id' => $wedding->getId(), 'step' => 2]);
        }

        $updated = false;

        switch ($type) {
            case 'musician':
                if ($wedding->getMusicians()->contains($user)) {
                    $wedding->removeMusician($user);
                    $updated = true;
                }
                break;
            case 'parish':
                if ($wedding->getParishUsers()->contains($user)) {
                    $wedding->removeParishUser($user);
                    $updated = true;
                }
                break;
            default:
                $this->addFlash('danger', 'Type d\'intervenant inconnu.');

                return $this->redirectToRoute('app_wedding_edit', ['id' => $wedding->getId(), 'step' => 2]);
        }

        if ($updated) {
            $em->persist($wedding);
            $em->flush();
            $this->addFlash('success', 'Intervenant retiré du mariage.');
        } else {
            $this->addFlash('info', 'Cet intervenant n\'est plus associé au mariage.');
        }

        return $this->redirectToRoute('app_wedding_edit', ['id' => $wedding->getId(), 'step' => 2]);
    }

    #[Route('/{id}/invite', name: 'app_wedding_invite')]
public function invite(
    Wedding $wedding,
    Request $request,
    EntityManagerInterface $em,
    MailerInterface $mailer,
    SongTypeRepository $songTypeRepo,
    SongRepository $songRepo
): Response {
    $generatedInvitationLink = null;

    if ($request->isMethod('POST')) {
        $email = $request->request->get('email');
        $role = $request->request->get('role');

        $invitation = new Invitation();
        $invitation->setEmail($email);
        $invitation->setWedding($wedding);
        $invitation->setRole($role);
        $invitation->setToken(bin2hex(random_bytes(32)));
        $invitation->setUsed(false);
        $invitation->setCreatedAt(new \DateTimeImmutable());
        $em->persist($invitation);
        $em->flush();

        if (in_array($role, ['marie', 'mariee'], true) && $wedding->isRequiresCouplePayment()) {
            $this->addFlash('info', 'Cette invitation demandera aux mariés de régler leur participation pour rejoindre le mariage.');
        }

        // Génération du lien
        $generatedInvitationLink = $this->generateUrl(
            'app_invitation_accept',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Envoi de l'email
        $emailMessage = (new Email())
            ->from('noreply@monsite.com')
            ->to($email)
            ->subject('Invitation à rejoindre un mariage')
            ->html("Vous avez été invité à rejoindre un mariage.<br>
                    <a href='$generatedInvitationLink'>Cliquez ici pour accepter l’invitation</a>");

        $mailer->send($emailMessage);

        $this->addFlash('success', 'Invitation envoyée !');

        // 🔑 Reprend les mêmes données que edit()
        $songTypes = $songTypeRepo->findAll();

        $availableSongs = [];
        foreach ($wedding->getMusicians() as $musician) {
            foreach ($musician->getRepertoire() as $song) {
                $availableSongs[$song->getId()] = $song;
            }
        }

        $availableSongsByType = [];
        foreach ($songTypes as $songType) {
            $songsForType = [];

            if (count($wedding->getMusicians()) > 0) {
                foreach ($wedding->getMusicians() as $musician) {
                    foreach ($musician->getRepertoire() as $song) {
                        if ($song->getTypes()->contains($songType)) {
                            $songsForType[$song->getId()] = $song;
                        }
                    }
                }
            }

            if (empty($songsForType)) {
                foreach ($songType->getSongs() as $song) {
                    $songsForType[$song->getId()] = $song;
                }
            }

            $availableSongsByType[$songType->getId()] = array_values($songsForType);
        }

        return $this->render('wedding/edit.html.twig', [
            'form' => $this->createForm(WeddingFormType::class, $wedding)->createView(),
            'wedding' => $wedding,
            'songTypes' => $songTypes,
            'availableSongs' => $availableSongs,
            'availableSongsByType' => $availableSongsByType,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
            'generatedInvitationLink' => $generatedInvitationLink,
        ]);
    }

    return $this->redirectToRoute('app_wedding_edit', ['id' => $wedding->getId()]);
}

    #[Route('/{id}/invitation/checkout', name: 'app_wedding_invitation_checkout')]
    public function invitationCheckout(
        Wedding $wedding,
        Request $request,
        InvitationRepository $invitationRepo
    ): Response {
        $session = $request->getSession();
        $token = $session->get('pending_invitation_token');

        if (!$token) {
            $this->addFlash('danger', 'Aucun paiement d’invitation en attente.');

            return $this->redirectToRoute('app_wedding_view', ['id' => $wedding->getId()]);
        }

        $invitation = $invitationRepo->findOneBy(['token' => $token, 'used' => false]);
        if (!$invitation || $invitation->getWedding()?->getId() !== $wedding->getId()) {
            $session->remove('pending_invitation_token');
            $this->addFlash('danger', 'Cette invitation est invalide ou déjà utilisée.');

            return $this->redirectToRoute('home');
        }

        if (!$this->getUser()) {
            $this->addFlash('info', 'Connectez-vous pour poursuivre le paiement.');

            return $this->redirectToRoute('app_login');
        }

        if (!$wedding->isRequiresCouplePayment()) {
            $session->remove('pending_invitation_token');
            $this->addFlash('success', 'Ce mariage n’exige plus de paiement.');

            return $this->redirectToRoute('app_wedding_view', ['id' => $wedding->getId()]);
        }

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);
        $checkout = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Validation de mariage'],
                    'unit_amount' => 5000,
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'invitation_token' => $invitation->getToken(),
                'wedding_id' => (string) $wedding->getId(),
            ],
            'success_url' => $this->generateUrl(
                'app_wedding_invitation_checkout_success',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl(
                'app_wedding_view',
                ['id' => $wedding->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $this->redirect($checkout->url);
    }

    #[Route('/invitation/payment/success', name: 'app_wedding_invitation_checkout_success')]
    public function invitationCheckoutSuccess(
        Request $request,
        InvitationRepository $invitationRepo,
        InvitationWorkflow $invitationWorkflow,
        WeddingRepository $weddingRepo
    ): Response {
        $sessionId = $request->query->get('session_id');
        if (!$sessionId) {
            $this->addFlash('danger', 'Session Stripe manquante.');

            return $this->redirectToRoute('home');
        }

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);

        try {
            $checkout = $stripe->checkout->sessions->retrieve($sessionId);
        } catch (\Exception $exception) {
            $this->addFlash('danger', 'Impossible de vérifier le paiement.');

            return $this->redirectToRoute('home');
        }

        if ($checkout->payment_status !== 'paid') {
            $this->addFlash('warning', 'Le paiement n’a pas été finalisé.');

            return $this->redirectToRoute('home');
        }

        $metadataToken = $checkout->metadata->invitation_token ?? null;
        $session = $request->getSession();
        $token = $metadataToken ?: $session->get('pending_invitation_token');

        if (!$token) {
            $this->addFlash('danger', 'Invitation introuvable après paiement.');

            return $this->redirectToRoute('home');
        }

        $invitation = $invitationRepo->findOneBy(['token' => $token, 'used' => false]);
        if (!$invitation) {
            $session->remove('pending_invitation_token');
            $this->addFlash('danger', 'Invitation invalide ou déjà acceptée.');

            return $this->redirectToRoute('home');
        }

        $wedding = $invitation->getWedding();
        $wedding->setRequiresCouplePayment(false);
        $wedding->setCreatedWithCredit(false);
        $weddingRepo->save($wedding, true);

        $session->remove('pending_invitation_token');
        $session->set('invitation_token', $invitation->getToken());

        if ($this->getUser()) {
            $invitationWorkflow->attachUser($this->getUser(), $invitation);
            $session->remove('invitation_token');

            $this->addFlash('success', 'Paiement confirmé, vous avez rejoint le mariage.');

            return $this->redirectToRoute('app_wedding_view', ['id' => $wedding->getId()]);
        }

        $this->addFlash('success', 'Paiement confirmé. Connectez-vous pour finaliser votre accès.');

        return $this->redirectToRoute('app_login');
    }
    #[Route('/checkout', name: 'app_wedding_create_checkout', methods: ['POST'])]
    public function createCheckout(Request $request): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Admins n’ont pas besoin de payer'], 403);
        }

        $stripe = new StripeClient($_ENV['STRIPE_SECRET_KEY']);

        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => 'Création mariage'],
                        'unit_amount' => 5000,
                    ],
                    'quantity' => 1,
                ]
            ],
            'success_url' => $this->generateUrl(
                'app_wedding_edit',
                ['id' => 0],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '?paid=1',
            'cancel_url' => $this->generateUrl(
                'app_wedding_edit',
                ['id' => 0],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $this->json(['sessionId' => $session->id]);
    }
    #[Route('/{id}/archive', name: 'app_wedding_archive', methods: ['POST'])]
    public function archive(Request $request, Wedding $wedding, WeddingRepository $repo): Response
    {
        if (!$this->isCsrfTokenValid('archive' . $wedding->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_wedding_index');
        }

        $wedding->setArchive(true);
        $repo->save($wedding, true);

        $this->addFlash('success', 'Mariage archivé.');
        return $this->redirectToRoute('app_wedding_index');
    }

    #[Route('/{id}/unarchive', name: 'app_wedding_unarchive', methods: ['POST'])]
    public function unarchive(Request $request, Wedding $wedding, WeddingRepository $repo): Response
    {
        if (!$this->isCsrfTokenValid('unarchive' . $wedding->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_wedding_index');
        }

        $wedding->setArchive(false);
        $repo->save($wedding, true);

        $this->addFlash('success', 'Mariage désarchivé.');
        return $this->redirectToRoute('app_wedding_index');
    }
}