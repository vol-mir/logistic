<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Organization;
use App\Form\AddressType;
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

/**
 *
 * Class AddressController
 * @package App\Controller
 *
 * @Route("/organization/{organization_id}", requirements={"organization_id" = "\d+"});
 * @ParamConverter("organization", options={"id" = "organization_id"})
 */
class AddressController extends AbstractController
{
    /**
     * Data for datatables
     *
     * @Route("/address/datatables", methods="POST", name="address_datatables")
     *
     * @param Organization $organization
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listDatatableAction(Request $request, Organization $organization) : JsonResponse
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
        $otherConditions = [
            "t0.organization = ".$organization->getId()
        ];

        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(Address::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $em->getRepository(Address::class)->countAddress($otherConditions);
        // Get total number of filtered data
        $filtered_objects_count = $results["countResult"];

        $data = [];
        foreach ($objects as $key => $address)
        {
            $dataTemp = [];
            foreach ($columns as $key => $column)
            {
                switch($column['name'])
                {
                    case 'pointName':
                        {
                            $elementTemp = "<a href='".$this->generateUrl('address_edit', ['organization_id' => $organization->getId(), 'id' => $address->getId()])."' class='float-left'>".$address->getPointName()."</a>";
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'fullAddress':
                        {
                            $elementTemp = $address->getFullAddress();
                            array_push($dataTemp, $elementTemp);
                            break;
                        }

                    case 'control':
                        {
                            $elementTemp = "<div class='btn-group btn-group-sm'><a href='".$this->generateUrl('address_edit', ['organization_id' => $organization->getId(), 'id' => $address->getId()])."' class='btn btn-info'><i class='fas fa-edit'></i></a><button type='button' class='btn btn-sm btn-danger float-left modal-delete-dialog' data-toggle='modal' data-id='".$address->getId()."'><i class='fas fa-trash'></i></button></div>";
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
     * Creates a new address entity.
     *
     * @Route("/address/new", methods="GET|POST", name="address_new")
     *
     * @param Request $request
     * @param Organization $organization
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     */
    public function new(Request $request, Organization $organization, TranslatorInterface $translator) : Response
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address)->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $address->setOrganization($organization);
            $em->persist($address);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('address_new', [
                    'organization_id' => $organization->getId(),
                ]);
            }

            return $this->redirectToRoute('organization_show', [
                'id' => $organization->getId(),
            ]);
        }

        return $this->render('address/new.html.twig', [
            'form' => $form->createView(),
            'organization' => $organization
        ]);
    }

    /**
     * Edit address
     *
     * @Route("/address/{id}/edit", methods="GET|POST", name="address_edit", requirements={"id" = "\d+"})
     * @ParamConverter("address", options={"id" = "id"})
     *
     * @param Request $request
     * @param Organization $organization
     * @param Address $address
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function edit(Request $request, Organization $organization, Address $address, TranslatorInterface $translator) : Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));
            return $this->redirectToRoute('organization_show', [
                'id' => $organization->getId(),
            ]);
        }

        return $this->render('address/edit.html.twig', [
            'form' => $form->createView(),
            'organization' => $organization,
            'address' => $address
        ]);
    }

    /**
     * Delete address
     *
     * @Route("/address/{id}/delete", methods="DELETE", name="address_delete", requirements={"id" = "\d+"})
     * @ParamConverter("address", options={"id" = "id"})
     *
     * @param Request $request
     * @param Address $address
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function delete(Request $request, Address $address, TranslatorInterface $translator) : JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($address);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.deleted_successfully')]);
    }
}
