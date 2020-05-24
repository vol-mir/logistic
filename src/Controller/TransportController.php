<?php

namespace App\Controller;

use App\Entity\Transport;
use App\Form\TransportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TransportController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/transports",  methods="GET", name="transport_index")
     *
     * @return Response
     */
    public function index() : Response
    {
        return $this->render('transport/index.html.twig');
    }

    /**
     * Data for datatables
     *
     * @Route("/transport/datatables", methods="POST", name="transport_datatables")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
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
        $otherConditions = "array or whatever is needed";

        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(Transport::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions = null);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $em->getRepository(Transport::class)->countTransport();
        // Get total number of results
        $selected_objects_count = count($objects);
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        // Construct response
        $response = '{
            "draw": '.$draw.',
            "recordsTotal": '.$total_objects_count.',
            "recordsFiltered": '.$filtered_objects_count.',
            "data": [';

        $i = 0;

        foreach ($objects as $key => $transport)
        {
            $response .= '["';

            $j = 0;
            $nbColumn = count($columns);
            foreach ($columns as $key => $column)
            {
                // In all cases where something does not exist or went wrong, return -
                $responseTemp = "-";

                switch($column['name'])
                {
                    case 'number':
                        {
                            $responseTemp = "<a href='".$this->generateUrl('transport_edit', ['id' => $transport->getId()])."' class='float-left'>".$transport->getNumber()."</a>";
                            break;
                        }

                    case 'marka':
                        {
                            $responseTemp = $transport->getMarka();
                            break;
                        }

                    case 'model':
                        {
                            $responseTemp = $transport->getModel();
                            break;
                        }

                    case 'control':
                        {
                            $responseTemp = "<div class='btn-group btn-group-sm'><a href='".$this->generateUrl('transport_edit', ['id' => $transport->getId()])."' class='btn btn-info'><i class='fas fa-eye'></i></a><button type='button' class='btn btn-sm btn-danger float-left modal-delete-dialog' data-toggle='modal' data-id='".$transport->getId()."'><i class='fas fa-trash'></i></button></div>";
                            break;
                        }
                }

                // Add the found data to the json
                $response .= $responseTemp;

                if(++$j !== $nbColumn)
                    $response .='","';
            }

            $response .= '"]';

            // Not on the last item
            if(++$i !== $selected_objects_count)
                $response .= ',';
        }

        $response .= ']}';

        // Send all this stuff back to DataTables
        $returnResponse = new JsonResponse();
        $returnResponse->setJson($response);

        return $returnResponse;
    }

    /**
     * Creates a new transport entity.
     *
     * @Route("/transport/new", methods="GET|POST", name="transport_new")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     */
    public function new(Request $request, TranslatorInterface $translator) : Response
    {
        $transport = new Transport();
        $form = $this->createForm(TransportType::class, $transport)->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($transport);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('transport_new');
            }

            return $this->redirectToRoute('transport_index');
        }

        return $this->render('transport/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit transport
     *
     * @Route("/transport/{id}/edit", methods="GET|POST", name="transport_edit", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Transport $transport
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function edit(Request $request, Transport $transport, TranslatorInterface $translator) : Response
    {
        $form = $this->createForm(TransportType::class, $transport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));
            return $this->redirectToRoute('transport_index');
        }

        return $this->render('transport/edit.html.twig', [
            'form' => $form->createView(),
            'transport' => $transport
        ]);
    }

    /**
     * Delete transport
     *
     * @Route("/transport/{id}/delete", methods="DELETE", name="transport_delete", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Transport $transport
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function delete(Request $request, Transport $transport, TranslatorInterface $translator) : JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($transport);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.deleted_successfully')]);
    }
}
