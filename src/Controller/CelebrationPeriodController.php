<?php

namespace App\Controller;

use App\Entity\CelebrationPeriod;
use App\Form\CelebrationPeriodType;
use App\Repository\CelebrationPeriodRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/celebration-period')]
class CelebrationPeriodController extends AbstractController
{
    #[Route('/', name: 'app_celebration_period_index')]
    public function index(CelebrationPeriodRepository $repo): Response
    {
        $periods = $repo->createQueryBuilder('cp')
            ->orderBy('cp.periodOrder', 'ASC')
            ->addOrderBy('cp.fullName', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('celebrationPeriod/index.html.twig', [
            'periods' => $periods,
        ]);
    }

    #[Route('/view/{id}', name: 'app_celebration_period_view')]
    public function view(CelebrationPeriod $period): Response
    {
        return $this->render('celebrationPeriod/view.html.twig', [
            'period' => $period,
        ]);
    }

    #[Route('/edit/{id?0}', name: 'app_celebration_period_edit')]
    public function edit(Request $request, CelebrationPeriod $period = null, CelebrationPeriodRepository $repo): Response
    {
        if (!$period) {
            $period = new CelebrationPeriod();
        }

        $form = $this->createForm(CelebrationPeriodType::class, $period);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repo->save($period, true);

            $this->addFlash('success', 'Période sauvegardée avec succès.');
            return $this->redirectToRoute('app_celebration_period_index');
        }

        return $this->render('celebrationPeriod/edit.html.twig', [
            'form' => $form->createView(),
            'period' => $period,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_celebration_period_delete', methods: ['POST'])]
    public function delete(Request $request, CelebrationPeriod $period, CelebrationPeriodRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete' . $period->getId(), $request->request->get('_token'))) {
            $repo->remove($period, true);
            $this->addFlash('success', 'Période supprimée avec succès.');
        }

        return $this->redirectToRoute('app_celebration_period_index');
    }
}
