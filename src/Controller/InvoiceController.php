<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\TaskGoods;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class InvoiceController
 * @package App\Controller
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER')", statusCode=404, message="Post not found")
 */
class InvoiceController extends AbstractController
{
    /**
     * @Route("/invoice/task/driver/generation", name="generation_task_driver_invoice")
     */
    public function index()
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
     * @return Response
     */
    public function invoice(Request $request): Response
    {
        if ($this->isCsrfTokenValid('task_driver_invoice', $request->request->get('_token'))) {

            $period = $request->request->get('period');
            $driver = $request->request->get('driver');

            $em = $this->getDoctrine()->getManager();
            $tasks_goods = $em->getRepository(TaskGoods::class)->selectTasksGoodsForDriver($period, $driver);

            return new JsonResponse(['report' => $this->render('invoice/task_driver/index.html.twig', [
                'tasks_goods' => $tasks_goods,
                'driver' => $em->getRepository(Driver::class)->find($driver),
                'units' => TaskGoods::LIST_UNITS,
                'loading_natures' => TaskGoods::LIST_LOADING_NATURES,
                'statuses' => TaskGoods::STATUSES,
                'departments' => User::DEPARTMENTS
            ])->getContent()]);

        }
    }
}
