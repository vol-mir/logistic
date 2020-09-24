<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\TaskGoods;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class InvoiceController
 * @package App\Controller
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER')", statusCode=404, message="Post not found")
 */
class InvoiceController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/invoice/task/driver/generation", name="generation_task_driver_invoice")
     *
     * @return Response
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $drivers = $em->getRepository(Driver::class)->findAll();

        return $this->render('invoice/task_driver/generation.html.twig', [
            'drivers' => $drivers

        ]);
    }

    /**
     * Invoice page
     *
     * @Route("/invoice/task/driver",  methods="POST", name="task_driver_invoice")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoice(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('task_driver_invoice', $request->request->get('_token'))) {
            die;
        }

        $period = $request->request->get('period');
        $driver = $request->request->get('driver');

        $em = $this->getDoctrine()->getManager();
        $tasksGoods = $em->getRepository(TaskGoods::class)->selectTasksGoodsForDriver($period, $driver);

        return new JsonResponse(['report' => $this->render('invoice/task_driver/index.html.twig', [
            'tasksGoods' => $tasksGoods,
            'driver' => $em->getRepository(Driver::class)->find($driver),
            'units' => TaskGoods::LIST_UNITS,
            'loadingNatures' => TaskGoods::LIST_LOADING_NATURES,
            'statuses' => TaskGoods::STATUSES,
            'departments' => User::DEPARTMENTS
        ])->getContent()]);
    }
}
