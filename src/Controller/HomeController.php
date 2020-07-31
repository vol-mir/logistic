<?php

namespace App\Controller;

use App\Entity\TaskGoods;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class HomeController
 * @package App\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'departments' => User::DEPARTMENTS,
            'statuses' => TaskGoods::STATUSES
        ]);
    }

    /**
     * @Route("/faq", name="faq")
     */
    public function faq()
    {
        return $this->render('home/faq.html.twig');
    }

    /**
     * Data for datatables
     *;
     * @Route("/home/task/goods/datatables", methods="POST", name="home_task_goods_datatables")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function listDatatableAction(Request $request, TranslatorInterface $translator, LoggerInterface $logger): JsonResponse
    {
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->request->get('draw'));
            $start = $request->request->get('start');
            $length = $request->request->get('length');
            $search = $request->request->get('search');
            $orders = $request->request->get('order');
            $columns = $request->request->get('columns');
        } else // If the request is not a POST one, die hard
            die;

        // Orders
        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = null;

        $authUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(TaskGoods::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions, $authUser);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $em->getRepository(TaskGoods::class)->countTaskGoods();
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        $data = [];
        foreach ($objects as $key => $task_goods) {
            $dataTemp = [];
            foreach ($columns as $key => $column) {
                switch ($column['name']) {
                    case 'checkbox':
                        {
                            array_push($dataTemp, "");
                            break;
                        }
                    case 'id':
                        {
                            $elementTemp = "<a href='" . $this->generateUrl('task_goods_show', ['id' => $task_goods->getId()]) . "' class='float-left'>" . $task_goods->getId() . "</a>";
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'dateTaskGoods':
                        {
                            $elementTemp = $task_goods->getDateTaskGoods()->format('d.m.Y');
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'goods':
                        {
                            $elementTemp = $task_goods->getGoods() . ', ' . $task_goods->getWeight() . ' ' . $translator->trans(TaskGoods::LIST_UNITS[$task_goods->getUnit()]);
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'organization':
                        {
                            $elementTemp = $task_goods->getOrganization()->getAbbreviatedName() . ', ' . $task_goods->getOrganization()->getRegistrationNumber();
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'user':
                        {
                            $elementTemp = '<small>'.$task_goods->getUser()->getFullName() . ', ' . $translator->trans(User::DEPARTMENTS[$task_goods->getUser()->getDepartment()]).'</small>';
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'status':
                        {
                            switch ($task_goods->getStatus()) {
                                case 1:
                                    $elementTemp = "<span class='badge badge-primary'>" . $translator->trans(TaskGoods::STATUSES[$task_goods->getStatus()]) . "</span>";
                                    break;
                                case 2:
                                    $elementTemp = "<span class='badge badge-warning'>" . $translator->trans(TaskGoods::STATUSES[$task_goods->getStatus()]) . "</span>";
                                    break;
                                case 3:
                                    $elementTemp = "<span class='badge badge-light'>" . $translator->trans(TaskGoods::STATUSES[$task_goods->getStatus()]) . "</span>";
                                    break;
                                case 4:
                                    $elementTemp = "<span class='badge badge-dark'>" . $translator->trans(TaskGoods::STATUSES[$task_goods->getStatus()]) . "</span>";
                                    break;
                                case 5:
                                    $elementTemp = "<span class='badge badge-success'>" . $translator->trans(TaskGoods::STATUSES[$task_goods->getStatus()]) . "</span>";
                                    break;
                                case 6:
                                    $elementTemp = "<span class='badge badge-danger'>" . $translator->trans(TaskGoods::STATUSES[$task_goods->getStatus()]) . "</span>";
                                    break;
                                default:
                                    $elementTemp = "<span class='badge badge-secondary'>" . $translator->trans(TaskGoods::STATUSES[$task_goods->getStatus()]) . "</span>";
                            }

                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'yid':
                        {
                            $elementTemp = $task_goods->getId();
                            array_push($dataTemp, $elementTemp);
                            break;
                        }
                }
            }
            array_push($data, $dataTemp);
        }

        // Construct response
        $response = [
            'draw' => $draw,
            'recordsTotal' => $total_objects_count,
            'recordsFiltered' => $filtered_objects_count,
            'data' => $data,
        ];


        // Send all this stuff back to DataTables
        $returnResponse = new JsonResponse();
        $returnResponse->setData($response);

        return $returnResponse;
    }

}
