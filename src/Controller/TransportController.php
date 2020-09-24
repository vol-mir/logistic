<?php

namespace App\Controller;

use App\Entity\Transport;
use App\Form\TransportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TransportController
 * @package App\Controller
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER')", statusCode=404, message="Post not found")
 */
class TransportController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/transports",  methods="GET", name="transport_index")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('transport/index.html.twig');
    }

    /**
     * Data for tables
     *
     * @Route("/transport/datatables", methods="POST", name="transport_datatables")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listDatatableAction(Request $request): JsonResponse
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

        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(Transport::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $totalObjectsCount = $em->getRepository(Transport::class)->countTransport();
        // Get total number of filtered data
        $filteredObjectsCount = $results["countResult"];

        $data = [];
        foreach ($objects as $transport) {
            $dataTemp = [];
            foreach ($columns as $column) {
                switch ($column['name']) {
                    case 'number':
                        {
                            $elementTemp = $this->render('default/table_href.html.twig', [
                                'url' => $this->generateUrl('transport_edit', ['id' => $transport->getId()]),
                                'urlName' => $transport->getNumber()
                            ])->getContent();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'marka':
                        {
                            $elementTemp = $transport->getMarka();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'model':
                        {
                            $elementTemp = $transport->getModel();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'control':
                        {
                            $elementTemp = $this->render('default/table_group_btn.html.twig', [
                                'urlEdit' => $this->generateUrl('transport_edit', ['id' => $transport->getId()]),
                                'idDelete' => $transport->getId()
                            ])->getContent();
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
     * Creates a new transport entity
     *
     * @Route("/transport/new", methods="GET|POST", name="transport_new")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     */
    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $transport = new Transport();
        $form = $this->createForm(TransportType::class, $transport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($transport);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->getClickedButton() && 'saveAndCreateNew' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('transport_new');
            }

            return $this->redirectToRoute('transport_index');
        }

        return $this->render('transport/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit the transport entity
     *
     * @Route("/transport/{id}/edit", methods="GET|POST", name="transport_edit", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Transport $transport
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function edit(Request $request, Transport $transport, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(TransportType::class, $transport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));

            if ($form->getClickedButton() && 'saveAndStay' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('transport_edit', ['id' => $transport->getId()]);
            }

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
    public function delete(Request $request, Transport $transport, TranslatorInterface $translator): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($transport);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.deleted_successfully')]);
    }
}
