<?php

namespace App\Controller;

use App\Entity\TaskGoods;
use App\Entity\User;
use App\Entity\Organization;
use App\Form\TaskGoodsManagementType;
use App\Form\TaskGoodsType;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * Class TaskGoodsController
 * @package App\Controller
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER') or is_granted('ROLE_OPERATOR')", statusCode=404, message="Post not found")
 */
class TaskGoodsController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/tasks/goods",  methods="GET", name="task_goods_index")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('task_goods/index.html.twig', [
            'departments' => User::DEPARTMENTS,
            'statuses' => TaskGoods::STATUSES
        ]);
    }

    /**
     * Data for datatables
     *;
     * @Route("/task/goods/datatables", methods="POST", name="task_goods_datatables")
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

                    case 'control':
                        {
                            $buttonsForEdit = "<a href='" . $this->generateUrl('task_goods_edit', ['id' => $task_goods->getId()]) . "' class='btn btn-info'><i class='fas fa-edit'></i></a>";
                            $buttonsForDelete = "<button type='button' class='btn btn-sm btn-danger float-left modal-delete-dialog' data-toggle='modal' data-id='" . $task_goods->getId() . "'><i class='fas fa-trash'></i></button>";

                            if (in_array('ROLE_OPERATOR', $this->getUser()->getRoles(), true) && !$task_goods->isAuthor($this->getUser())) {
                                $buttonsForEdit = "";
                                $buttonsForDelete = "";
                            }
                            if (in_array('ROLE_OPERATOR', $this->getUser()->getRoles(), true) && $task_goods->isAuthor($this->getUser()) && !$task_goods->isOpen()) {
                                $buttonsForEdit = "";
                                $buttonsForDelete = "";
                            }
                            if (in_array('ROLE_DISPATCHER', $this->getUser()->getRoles(), true)) {

                                $buttonsForEdit = "<a href='" . $this->generateUrl('task_goods_edit_full', ['id' => $task_goods->getId()]) . "' class='btn btn-outline-info'><i class='fas fa-edit'></i></a>";
                            }
                            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {

                                $buttonsForEdit = "<a href='" . $this->generateUrl('task_goods_edit', ['id' => $task_goods->getId()]) . "' class='btn btn-info'><i class='fas fa-edit'></i></a>" .
                                    "<a href='" . $this->generateUrl('task_goods_edit_full', ['id' => $task_goods->getId()]) . "' class='btn btn-outline-info'><i class='fas fa-edit'></i></a>";
                            }

                            $elementTemp = "<div class='btn-group btn-group-sm'>" .
                                "<a href='" . $this->generateUrl('task_goods_show', ['id' => $task_goods->getId()]) . "' class='btn btn-secondary'><i class='fas fa-eye'></i></a>" . $buttonsForEdit . $buttonsForDelete . "</div>";
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

    /**
     * Creates a new task_goods entity.
     *
     * @Route("/task/goods/new", methods="GET|POST", name="task_goods_new")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     */
    public function new(Request $request, TranslatorInterface $translator): Response
    {

        $task_goods = new TaskGoods();

        $organization = $this->getDoctrine()
            ->getRepository(Organization::class)
            ->find($this->getParameter('default_organization'));

        $task_goods->setOrganization($organization);
        $task_goods->setUser($this->getUser());

        $form = $this->createForm(TaskGoodsType::class, $task_goods)->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($task_goods);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('task_goods_new');
            }

            return $this->redirectToRoute('task_goods_index');
        }

        return $this->render('task_goods/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Edit task_goods
     *
     * @Route("/task/goods/{id}/edit", methods="GET|POST", name="task_goods_edit", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param TaskGoods $task_goods
     * @param TranslatorInterface $translator
     * @Security("is_granted('ROLE_ADMIN') or (is_granted('ROLE_OPERATOR') and task_goods.isAuthor(user) and task_goods.isOpen())", statusCode=404, message="Post not found")
     *
     * @return Response
     */
    public function edit(Request $request, TaskGoods $task_goods, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TaskGoodsType::class, $task_goods);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));
            return $this->redirectToRoute('task_goods_index');
        }

        return $this->render('task_goods/edit.html.twig', [
            'form' => $form->createView(),
            'task_goods' => $task_goods
        ]);
    }

    /**
     * Edit task_goods full
     *
     * @Route("/task/goods/{id}/edit/full", methods="GET|POST", name="task_goods_edit_full", requirements={"id" = "\d+"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER')", statusCode=404, message="Post not found")
     * @param Request $request
     * @param TaskGoods $task_goods
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function editFull(Request $request, TaskGoods $task_goods, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TaskGoodsType::class, $task_goods);
        $form->handleRequest($request);

        $formManagement = $this->createForm(TaskGoodsManagementType::class, $task_goods);
        $formManagement->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));
            return $this->redirectToRoute('task_goods_index');
        }

        if ($formManagement->isSubmitted() && $formManagement->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));
            return $this->redirectToRoute('task_goods_index');
        }

        return $this->render('task_goods/edit_full.html.twig', [
            'form' => $form->createView(),
            'formManagement' => $formManagement->createView(),
            'task_goods' => $task_goods
        ]);
    }

    /**
     * Show task_goods
     *
     * @Route("/task/goods/{id}/show", methods="GET", name="task_goods_show", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param TaskGoods $task_goods
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function show(Request $request, TaskGoods $task_goods, TranslatorInterface $translator): Response
    {
        return $this->render('task_goods/show.html.twig', [
            'task_goods' => $task_goods,
            'units' => TaskGoods::LIST_UNITS,
            'loading_natures' => TaskGoods::LIST_LOADING_NATURES,
            'statuses' => TaskGoods::STATUSES
        ]);
    }

    /**
     * Delete task_goods
     *
     * @Route("/task/goods/{id}/delete", methods="DELETE", name="task_goods_delete", requirements={"id" = "\d+"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER') or (is_granted('ROLE_OPERATOR') and task_goods.isAuthor(user) and task_goods.isOpen())", statusCode=404, message="Post not found")
     *
     * @param Request $request
     * @param TaskGoods $task_goods
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function delete(Request $request, TaskGoods $task_goods, TranslatorInterface $translator): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($task_goods);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.deleted_successfully')]);
    }

    /**
     * Invoice page
     *
     * @Route("/tasks/goods/invoice",  methods="POST", name="task_goods_invoice")
     *
     * @return Response
     */
    public function invoice(Request $request, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('task-goods-invoice', $request->request->get('_token'))) {

            $em = $this->getDoctrine()->getManager();
            $tasks_goods = $em->getRepository(TaskGoods::class)->selectTasksGoods($request->request->get('tasksGoodsPint'));

            return new JsonResponse(['report' => $this->render('task_goods/invoice.html.twig', [
                'tasks_goods' => $tasks_goods,
                'units' => TaskGoods::LIST_UNITS,
                'loading_natures' => TaskGoods::LIST_LOADING_NATURES,
                'statuses' => TaskGoods::STATUSES,
                'departments' => User::DEPARTMENTS,
                'user' => $this->getUser()
            ])->getContent()]);

        }
    }
}
