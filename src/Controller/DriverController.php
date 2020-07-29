<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Form\DriverType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class DriverController
 * @package App\Controller
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER')", statusCode=404, message="Post not found")
 */
class DriverController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/drivers",  methods="GET", name="driver_index")
     *
     * @return Response
     */
    public function index() : Response
    {
        return $this->render('driver/index.html.twig');
    }

    /**
     * Data for datatables
     *
     * @Route("/driver/datatables", methods="POST", name="driver_datatables")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listDatatableAction(Request $request) : JsonResponse
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
        $results = $em->getRepository(Driver::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $em->getRepository(Driver::class)->countDriver();
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        $data = [];
        foreach ($objects as $key => $driver)
        {
            $dataTemp = [];
            foreach ($columns as $key => $column)
            {
                switch($column['name'])
                {
                    case 'fullName':
                        {
                            $elementTemp = "<a href='".$this->generateUrl('driver_edit', ['id' => $driver->getId()])."' class='float-left'>".$driver->getFullName()."</a>";
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'phone':
                        {
                            $elementTemp = $driver->getPhone();
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'control':
                        {
                            $elementTemp = "<div class='btn-group btn-group-sm'><a href='".$this->generateUrl('driver_edit', ['id' => $driver->getId()])."' class='btn btn-info'><i class='fas fa-edit'></i></a><button type='button' class='btn btn-sm btn-danger float-left modal-delete-dialog' data-toggle='modal' data-id='".$driver->getId()."'><i class='fas fa-trash'></i></button></div>";
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
     * Creates a new driver entity.
     *
     * @Route("/driver/new", methods="GET|POST", name="driver_new")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     */
    public function new(Request $request, TranslatorInterface $translator) : Response
    {
        $driver = new Driver();
        $form = $this->createForm(DriverType::class, $driver)->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($driver);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('driver_new');
            }

            return $this->redirectToRoute('driver_index');
        }

        return $this->render('driver/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit driver
     *
     * @Route("/driver/{id}/edit", methods="GET|POST", name="driver_edit", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Driver $driver
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function edit(Request $request, Driver $driver, TranslatorInterface $translator) : Response
    {
        $form = $this->createForm(DriverType::class, $driver);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));
            return $this->redirectToRoute('driver_index');
        }

        return $this->render('driver/edit.html.twig', [
            'form' => $form->createView(),
            'driver' => $driver
        ]);
    }

    /**
     * Delete driver
     *
     * @Route("/driver/{id}/delete", methods="DELETE", name="driver_delete", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Driver $driver
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function delete(Request $request, Driver $driver, TranslatorInterface $translator) : JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($driver);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.deleted_successfully')]);
    }
}
