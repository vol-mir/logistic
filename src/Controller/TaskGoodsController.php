<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\Organization;
use App\Entity\TaskGoods;
use App\Entity\Transport;
use App\Entity\User;
use App\Form\TaskGoodsManagementType;
use App\Form\TaskGoodsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;



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
        $em = $this->getDoctrine()->getManager();

        return $this->render('task_goods/index.html.twig', [
            'departments' => User::DEPARTMENTS,
            'statuses' => TaskGoods::STATUSES,
            'transports' => $em->getRepository(Transport::class)->getAllTransports(),
            'drivers' => $em->getRepository(Driver::class)->getAllDrivers()
        ]);
    }

    /**
     * Data for tables
     *
     * @Route("/task/goods/datatables", methods="POST", name="task_goods_datatables")
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
                            if ($taskGoods->getNote()) {
                                $elementTemp .= ', ' . $taskGoods->getNote();
                            }
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
                            $elementTemp = '<small>' . $taskGoods->getUser()->getFullName() . ', ' . $translator->trans(User::DEPARTMENTS[$taskGoods->getUser()->getDepartment()]) . ', время создания: ' . $taskGoods->getCreatedAt()->format('d.m.Y H:i') . '</small>';
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

                    case 'control':
                        {
                            $isOperator = in_array('ROLE_OPERATOR', $this->getUser()->getRoles(), true);
                            $isDispatcher = in_array('ROLE_DISPATCHER', $this->getUser()->getRoles(), true);
                            $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true);

                            $isAuthor = $taskGoods->isAuthor($this->getUser());
                            $isOpen = $taskGoods->isOpen(); // status = 1

                            $buttonsForDelete = "";
                            $buttonsForEdit = "";
                            $buttonsForEditFull = "";
                            $buttonsForReview = "";

                            $buttonsForShow = "<a href='" . $this->generateUrl('task_goods_show', ['id' => $taskGoods->getId()]) . "' class='btn btn-secondary'><i class='fas fa-eye'></i></a>";

                            if ($isAdmin || $isDispatcher || ($isOperator && $isAuthor && $isOpen)) {
                                $buttonsForDelete = "<button type='button' class='btn btn-sm btn-danger float-left modal-delete-dialog' data-toggle='modal' data-id='" . $taskGoods->getId() . "'><i class='fas fa-trash'></i></button>";
                            }

                            if ($isAdmin || ($isOperator && $isAuthor && $isOpen)) {
                                $buttonsForEdit = "<a href='" . $this->generateUrl('task_goods_edit', ['id' => $taskGoods->getId()]) . "' class='btn btn-info'><i class='fas fa-edit'></i></a>";
                            }

                            if (($isAdmin || $isDispatcher || ($isOperator && $isAuthor)) && $isOpen) {
                                $buttonsForReview = "<button type='button' class='btn btn-sm btn-warning float-left modal-review-dialog' data-toggle='modal' data-id='" . $taskGoods->getId() . "'><i class='fas fa-check'></i></button>";
                            }

                            if ($isAdmin || $isDispatcher) {
                                $buttonsForEditFull = "<a href='" . $this->generateUrl('task_goods_edit_full', ['id' => $taskGoods->getId()]) . "' class='btn btn-outline-info'><i class='fas fa-edit'></i></a>";
                            }

                            $elementTemp = "<div class='btn-group btn-group-sm'>" . $buttonsForShow . $buttonsForReview . $buttonsForEdit . $buttonsForEditFull . $buttonsForDelete . "</div>";
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

    /**
     * Creates a new task goods entity
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
        $taskGoods = new TaskGoods();

        $organization = $this->getDoctrine()
            ->getRepository(Organization::class)
            ->find($this->getParameter('default_organization'));

        $taskGoods->setOrganization($organization);
        $taskGoods->setUser($this->getUser());

        $form = $this->createForm(TaskGoodsType::class, $taskGoods);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($taskGoods);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->getClickedButton() && 'saveAndCreateNew' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('task_goods_new');
            }

            return $this->redirectToRoute('task_goods_index');
        }

        return $this->render('task_goods/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Edit the task goods entity
     *
     * @Route("/task/goods/{id}/edit", methods="GET|POST", name="task_goods_edit", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param TaskGoods $taskGoods
     * @param TranslatorInterface $translator
     * @Security("is_granted('ROLE_ADMIN') or (is_granted('ROLE_OPERATOR') and taskGoods.isAuthor(user) and taskGoods.isOpen())", statusCode=404, message="Post not found")
     *
     * @return Response
     */
    public function edit(Request $request, TaskGoods $taskGoods, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TaskGoodsType::class, $taskGoods);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));

            if ($form->getClickedButton() && 'saveAndStay' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('task_goods_edit', ['id' => $taskGoods->getId()]);
            }

            return $this->redirectToRoute('task_goods_index');
        }

        return $this->render('task_goods/edit.html.twig', [
            'form' => $form->createView(),
            'taskGoods' => $taskGoods
        ]);
    }

    /**
     * Edit the task goods full entity
     *
     * @Route("/task/goods/{id}/edit/full", methods="GET|POST", name="task_goods_edit_full", requirements={"id" = "\d+"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER')", statusCode=404, message="Post not found")
     * @param Request $request
     * @param TaskGoods $taskGoods
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function editFull(Request $request, TaskGoods $taskGoods, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TaskGoodsType::class, $taskGoods);
        $form->handleRequest($request);

        $formManagement = $this->createForm(TaskGoodsManagementType::class, $taskGoods);
        $formManagement->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid()) || ($formManagement->isSubmitted() && $formManagement->isValid())) {

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));

            if (($form->getClickedButton() && 'saveAndStay' === $form->getClickedButton()->getName()) ||
                ($formManagement->getClickedButton() && 'saveAndStay' === $formManagement->getClickedButton()->getName())) {

                return $this->redirectToRoute('task_goods_edit_full', ['id' => $taskGoods->getId()]);
            }

            return $this->redirectToRoute('task_goods_index');
        }

        return $this->render('task_goods/edit_full.html.twig', [
            'form' => $form->createView(),
            'formManagement' => $formManagement->createView(),
            'taskGoods' => $taskGoods
        ]);
    }

    /**
     * Show task goods
     *
     * @Route("/task/goods/{id}/show", methods="GET", name="task_goods_show", requirements={"id" = "\d+"})
     *
     * @param TaskGoods $taskGoods
     *
     * @return Response
     */
    public function show(TaskGoods $taskGoods): Response
    {
        return $this->render('task_goods/show.html.twig', [
            'taskGoods' => $taskGoods,
            'units' => TaskGoods::LIST_UNITS,
            'loadingNatures' => TaskGoods::LIST_LOADING_NATURES,
            'statuses' => TaskGoods::STATUSES
        ]);
    }

    /**
     * Delete task goods
     *
     * @Route("/task/goods/{id}/delete", methods="DELETE", name="task_goods_delete", requirements={"id" = "\d+"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER') or (is_granted('ROLE_OPERATOR') and taskGoods.isAuthor(user) and taskGoods.isOpen())", statusCode=404, message="Post not found")
     *
     * @param Request $request
     * @param TaskGoods $taskGoods
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function delete(Request $request, TaskGoods $taskGoods, TranslatorInterface $translator): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($taskGoods);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.deleted_successfully')]);
    }

    /**
     * Review task goods
     *
     * @Route("/task/goods/{id}/review", methods="PUT", name="task_goods_review", requirements={"id" = "\d+"})
     * @Security("(is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER') or (is_granted('ROLE_OPERATOR') and taskGoods.isAuthor(user))) and taskGoods.isOpen()", statusCode=404, message="Post not found")
     *
     * @param Request $request
     * @param TaskGoods $taskGoods
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function review(Request $request, TaskGoods $taskGoods, TranslatorInterface $translator): JsonResponse
    {
        if ($this->isCsrfTokenValid('review-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $taskGoods->setStatus(2);
            $em->persist($taskGoods);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.edited_successfully')]);
    }

    /**
     * Invoice page
     *
     * @Route("/tasks/goods/invoice",  methods="POST", name="task_goods_invoice")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoice(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('task-goods-invoice', $request->request->get('_token'))) {
            die;
        }

        $em = $this->getDoctrine()->getManager();
        $taskGoods = $em->getRepository(TaskGoods::class)->selectTasksGoods($request->request->get('tasksGoodsPint'));

        return new JsonResponse(['report' => $this->render('task_goods/invoice.html.twig', [
            'tasksGoods' => $taskGoods,
            'units' => TaskGoods::LIST_UNITS,
            'loadingNatures' => TaskGoods::LIST_LOADING_NATURES,
            'statuses' => TaskGoods::STATUSES,
            'departments' => User::DEPARTMENTS,
            'user' => $this->getUser()
        ])->getContent()]);
    }

    /**
     * Tasks goods select edit
     *
     * @Route("/tasks/goods/list/edit",  methods="POST", name="tasks_goods_list_edit")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function editList(Request $request, TranslatorInterface $translator, LoggerInterface $logger): JsonResponse
    {
        if (!$this->isCsrfTokenValid('tasks-goods-list-edit', $request->request->get('_token'))) {
            die;
        }

        $tasksGoodsEdit = json_decode($request->request->get('tasksGoodsEdit'), true);
        $formData = json_decode($request->request->get('formData'), false);

        foreach ($tasksGoodsEdit as $taskId) {
            $em = $this->getDoctrine()->getManager();

            $taskGoods = $em->getRepository(TaskGoods::class)->find($taskId);

            if (!$taskGoods) {
                continue;
            }

            if (property_exists($formData, 'statusSelect')) {
                $taskGoods->setStatus($formData->statusSelect);
            }

            if (property_exists($formData, 'driversSelect')) {
                $taskGoods->removeAllDrivers();

                foreach ($formData->driversSelect as $driverId) {
                    $driver = $em->getRepository(Driver::class)->find($driverId);

                    if (!$driver) {
                        continue;
                    }

                    $taskGoods->addDriver($driver);
                }
            }

            if (property_exists($formData, 'transportsSelect')) {
                $taskGoods->removeAllTransports();

                foreach ($formData->transportsSelect as $transportId) {
                    $transport = $em->getRepository(Transport::class)->find($transportId);

                    if (!$transport) {
                        continue;
                    }

                    $taskGoods->addTransport($transport);
                }
            }

            if (property_exists($formData, 'reportTextarea')) {
                $taskGoods->setReport($formData->reportTextarea);
            }

            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('items.edited_successfully')]);
    }
}
