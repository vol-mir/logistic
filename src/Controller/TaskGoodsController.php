<?php

namespace App\Controller;

use App\Entity\TaskGoods;
use App\Entity\Organization;
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

class TaskGoodsController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/tasks/goods",  methods="GET", name="task_goods_index")
     *
     * @return Response
     */
    public function index() : Response
    {
        return $this->render('task_goods/index.html.twig');
    }

    /**
     * Data for datatables
     *
     * @Route("/task/goods/datatables", methods="POST", name="task_goods_datatables")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function listDatatableAction(Request $request, TranslatorInterface $translator) : JsonResponse
    {
        // Get the parameters from DataTable Ajax Call
        if ($request->getMethod() == 'POST') {
            $draw = intval($request->request->get('draw'));
            $start = $request->request->get('start');
            $length = $request->request->get('length');
            $search = $request->request->get('search');
            $orders = $request->request->get('order');
            $columns = $request->request->get('columns');
        }
        else // If the request is not a POST one, die hard
            die;

        // Orders
        foreach ($orders as $key => $order)
        {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        // Further filtering can be done in the Repository by passing necessary arguments
        $otherConditions = null;

        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(TaskGoods::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $em->getRepository(TaskGoods::class)->countTaskGoods();
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        $data = [];
        foreach ($objects as $key => $task_goods)
        {
            $dataTemp = [];
            foreach ($columns as $key => $column)
            {
                switch($column['name'])
                {
                    case 'id':
                        {
                            $elementTemp = "<a href='".$this->generateUrl('task_goods_edit', ['id' => $task_goods->getId()])."' class='float-left'>".$task_goods->getId()."</a>";
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'goods':
                        {
                            $elementTemp = $task_goods->getGoods().', '.$task_goods->getWeight().' '.$translator->trans(TaskGoods::LIST_UNITS[$task_goods->getUnit()]);
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'organization':
                        {
                            $elementTemp = $task_goods->getOrganization()->getAbbreviatedName().', '.$task_goods->getOrganization()->getRegistrationNumber();
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'status':
                        {
                            $elementTemp = $translator->trans(TaskGoods::STATUSES[$task_goods->getStatus()]);
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'control':
                        {
                            $elementTemp = "<div class='btn-group btn-group-sm'><a href='".$this->generateUrl('task_goods_edit', ['id' => $task_goods->getId()])."' class='btn btn-info'><i class='fas fa-edit'></i></a><button type='button' class='btn btn-sm btn-danger float-left modal-delete-dialog' data-toggle='modal' data-id='".$task_goods->getId()."'><i class='fas fa-trash'></i></button></div>";
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
    public function new(Request $request, TranslatorInterface $translator) : Response
    {

        $task_goods = new TaskGoods();


        $organization = $this->getDoctrine()
            ->getRepository(Organization::class)
            ->find($this->getParameter('default_organization'));

        $task_goods->setOrganization($organization);

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
     *
     * @return Response
     */
    public function edit(Request $request, TaskGoods $task_goods, TranslatorInterface $translator) : Response
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
     * Delete task_goods
     *
     * @Route("/task/goods/{id}/delete", methods="DELETE", name="task_goods_delete", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param TaskGoods $task_goods
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function delete(Request $request, TaskGoods $task_goods, TranslatorInterface $translator) : JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($task_goods);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.deleted_successfully')]);
    }
}
