<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Organization;
use App\Form\AddressType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
 * Class AddressController
 * @package App\Controller
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER') or is_granted('ROLE_OPERATOR')", statusCode=404, message="Post not found")
 *
 * @Route("/organization/{organization_id}", requirements={"organization_id" = "\d+"});
 * @ParamConverter("organization", options={"id" = "organization_id"})
 */
class AddressController extends AbstractController
{
    /**
     * Data for tables
     *
     * @Route("/address/datatables", methods="POST", name="address_datatables")
     *
     * @param Organization $organization
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listDatatableAction(Request $request, Organization $organization): JsonResponse
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
        $otherConditions = [
            "t0.organization = " . $organization->getId()
        ];

        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(Address::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $totalObjectsCount = $em->getRepository(Address::class)->countAddress($otherConditions);
        // Get total number of filtered data
        $filteredObjectsCount = $results["countResult"];

        $data = [];
        foreach ($objects as $key => $address) {
            $dataTemp = [];
            foreach ($columns as $column) {
                switch ($column['name']) {
                    case 'pointName':
                        {
                            $elementTemp = $this->render('default/table_href.html.twig', [
                                'url' => $this->generateUrl('address_edit', ['organization_id' => $organization->getId(), 'id' => $address->getId()]),
                                'urlName' => $address->getPointName()
                            ])->getContent();

                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'fullAddress':
                        {
                            $elementTemp = $address->getFullAddress();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'control':
                        {
                            $elementTemp = $this->render('default/table_group_btn.html.twig', [
                                'urlEdit' => $this->generateUrl('address_edit', ['organization_id' => $organization->getId(), 'id' => $address->getId()]),
                                'idDelete' => $address->getId()
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
     * Creates a new address entity
     *
     * @Route("/address/new", methods="GET|POST", name="address_new")
     *
     * @param Request $request
     * @param Organization $organization
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     */
    public function new(Request $request, Organization $organization, TranslatorInterface $translator): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $address->setOrganization($organization);
            $em->persist($address);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->getClickedButton() && 'saveAndCreateNew' === $form->getClickedButton()->getName()) {

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
     * Edit the address entity
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
    public function edit(Request $request, Organization $organization, Address $address, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));

            if ($form->getClickedButton() && 'saveAndStay' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('address_edit', [
                    'organization_id' => $organization->getId(),
                    'id' => $address->getId()
                ]);
            }

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
    public function delete(Request $request, Address $address, TranslatorInterface $translator): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {

            if ($address->getTasksGoodsAddressGoodsYard()->count() > 0) {
                return new JsonResponse(['messageError' => $translator->trans('item.deleted_use')]);
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($address);
            $em->flush();
        }

        return new JsonResponse(['messageSuccess' => $translator->trans('item.deleted_successfully')]);
    }
}
