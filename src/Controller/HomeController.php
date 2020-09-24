<?php

namespace App\Controller;

use App\Entity\TaskGoods;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class HomeController
 * @package App\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class HomeController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/", name="home")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'departments' => User::DEPARTMENTS,
            'statuses' => TaskGoods::STATUSES
        ]);
    }

    /**
     * FAQ page
     *
     * @Route("/faq", name="faq")
     *
     * @return Response
     */
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig');
    }

    /**
     * Data for tables
     *;
     * @Route("/home/task/goods/datatables", methods="POST", name="home_task_goods_datatables")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function listDatatableAction(Request $request, TranslatorInterface $translator): JsonResponse
    {
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() === 'POST') {
            $draw = (int)$request->request->get('draw');
            $start = $request->request->get('start');
            $length = $request->request->get('length');
            $search = $request->request->get('search');
            $orders = $request->request->get('order');
            $columns = $request->request->get('columns');
        } else // If the request is not a POST one, die hard
        {
            die;
        }

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
        $totalObjectsCount = $em->getRepository(TaskGoods::class)->countTaskGoods();
        // Get total number of filtered data
        $filteredObjectsCount = $results["countResult"];

        $data = [];
        foreach ($objects as $taskGoods) {
            $dataTemp = [];
            foreach ($columns as $column) {
                switch ($column['name']) {
                    case 'checkbox':
                        {
                            $dataTemp[] = "";
                            break;
                        }
                    case 'id':
                        {
                            $elementTemp = $this->render('default/table_href.html.twig', [
                                'url' => $this->generateUrl('task_goods_show', ['id' => $taskGoods->getId()]),
                                'urlName' => $taskGoods->getId()
                            ])->getContent();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'dateTaskGoods':
                        {
                            $elementTemp = $taskGoods->getDateTaskGoods()->format('d.m.Y');
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'goods':
                        {
                            $elementTemp = $taskGoods->getGoods() . ', ' . $taskGoods->getWeight() . ' ' . $translator->trans(TaskGoods::LIST_UNITS[$taskGoods->getUnit()]);
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'organization':
                        {
                            $elementTemp = $taskGoods->getOrganization()->getAbbreviatedName() . ', ' . $taskGoods->getOrganization()->getRegistrationNumber();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'user':
                        {
                            $elementTemp = '<small>' . $taskGoods->getUser()->getFullName() . ', ' . $translator->trans(User::DEPARTMENTS[$taskGoods->getUser()->getDepartment()]) . '</small>';
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'status':
                        {
                            switch ($taskGoods->getStatus()) {
                                case 1:
                                    $elementTemp = "<span class='badge badge-primary'>" . $translator->trans(TaskGoods::STATUSES[$taskGoods->getStatus()]) . "</span>";
                                    break;
                                case 2:
                                    $elementTemp = "<span class='badge badge-warning'>" . $translator->trans(TaskGoods::STATUSES[$taskGoods->getStatus()]) . "</span>";
                                    break;
                                case 3:
                                    $elementTemp = "<span class='badge badge-light'>" . $translator->trans(TaskGoods::STATUSES[$taskGoods->getStatus()]) . "</span>";
                                    break;
                                case 4:
                                    $elementTemp = "<span class='badge badge-dark'>" . $translator->trans(TaskGoods::STATUSES[$taskGoods->getStatus()]) . "</span>";
                                    break;
                                case 5:
                                    $elementTemp = "<span class='badge badge-success'>" . $translator->trans(TaskGoods::STATUSES[$taskGoods->getStatus()]) . "</span>";
                                    break;
                                case 6:
                                    $elementTemp = "<span class='badge badge-danger'>" . $translator->trans(TaskGoods::STATUSES[$taskGoods->getStatus()]) . "</span>";
                                    break;
                                default:
                                    $elementTemp = "<span class='badge badge-secondary'>" . $translator->trans(TaskGoods::STATUSES[$taskGoods->getStatus()]) . "</span>";
                            }

                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'yid':
                        {
                            $elementTemp = $taskGoods->getId();
                            $dataTemp[] = $elementTemp;
                            break;
                        }
                }
            }
            $data[] = $dataTemp;
        }

        // Construct response
        $response = [
            'draw' => $draw,
            'recordsTotal' => $totalObjectsCount,
            'recordsFiltered' => $filteredObjectsCount,
            'data' => $data,
        ];

        // Send all this stuff back to DataTables
        $returnResponse = new JsonResponse();
        $returnResponse->setData($response);

        return $returnResponse;
    }

}
