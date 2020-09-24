<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Organization;
use App\Form\OrganizationType;
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
 * Class OrganizationController
 * @package App\Controller
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_DISPATCHER') or is_granted('ROLE_OPERATOR')", statusCode=404, message="Post not found")
 */
class OrganizationController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/organizations",  methods="GET", name="organization_index")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('organization/index.html.twig');
    }

    /**
     * Data for tables
     *
     * @Route("/organization/{id}/data", methods="POST", name="organization_data_base_dynamic")
     * @ParamConverter("organization", options={"id" = "id"})
     *
     * @param Request $request
     * @param Organization $organization
     *
     * @return JsonResponse
     */
    public function dataBaseOrganizationDynamic(Request $request, Organization $organization): JsonResponse
    {
        if (!$request->getMethod() === 'POST' && !$this->isCsrfTokenValid('dynamic-data-organization', $request->request->get('_token'))) {
            die;
        }
        $em = $this->getDoctrine()->getManager();
        $addressesOrganization = $em->getRepository(Address::class)->getAddressesOrganization($organization->getId());

        $listAddressesOrganization = [];

        foreach ($addressesOrganization as $address) {
            $temp = ['id' => $address->getId(), 'text' => $address->getFullAddress()];
            $listAddressesOrganization[] = $temp;
        }

        $response = [
            'baseContactPerson' => $organization->getBaseContactPerson(),
            'baseWorkingHours' => $organization->getBaseWorkingHours(),
            'addressesOrganization' => $listAddressesOrganization
        ];


        $returnResponse = new JsonResponse();
        $returnResponse->setData($response);

        return $returnResponse;

    }

    /**
     * Data for tables
     *
     * @Route("/organization/datatables", methods="POST", name="organization_datatables")
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
        $results = $em->getRepository(Organization::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $totalObjectsCount = $em->getRepository(Organization::class)->countOrganization();
        // Get total number of filtered data
        $filteredObjectsCount = $results["countResult"];

        $data = [];
        foreach ($objects as $organization) {
            $dataTemp = [];
            foreach ($columns as $column) {
                switch ($column['name']) {
                    case 'abbreviatedName':
                        {
                            $elementTemp = "<a href='" . $this->generateUrl('organization_show', ['id' => $organization->getId()]) . "' class='float-left'>" . $organization->getAbbreviatedName() . "</a>";
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'registrationNumber':
                        {
                            $elementTemp = $organization->getRegistrationNumber();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'fullName':
                        {
                            $elementTemp = $organization->getFullName();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'control':
                        {
                            $elementTemp = "<div class='btn-group btn-group-sm'><a href='" . $this->generateUrl('organization_show', ['id' => $organization->getId()]) . "' class='btn btn-secondary'><i class='fas fa-eye'></i></a><a href='" . $this->generateUrl('organization_edit', ['id' => $organization->getId()]) . "' class='btn btn-info'><i class='fas fa-edit'></i></a><button type='button' class='btn btn-sm btn-danger float-left modal-delete-dialog' data-toggle='modal' data-id='" . $organization->getId() . "'><i class='fas fa-trash'></i></button></div>";
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
     * Creates a new organization entity
     *
     * @Route("/organization/new", methods="GET|POST", name="organization_new")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse|Response
     */
    public function new(Request $request, TranslatorInterface $translator): Response
    {
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($organization);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->getClickedButton() && 'saveAndCreateNew' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('organization_new');
            }

            return $this->redirectToRoute('organization_index');
        }

        return $this->render('organization/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Show organization
     *
     * @Route("/organization/{id}", methods="GET", name="organization_show", requirements={"id" = "\d+"})
     *
     * @param Organization $organization
     *
     * @return Response
     */
    public function show(Organization $organization): Response
    {
        return $this->render('organization/show.html.twig', [
            'organization' => $organization
        ]);
    }


    /**
     * Edit the organization entity
     *
     * @Route("/organization/{id}/edit", methods="GET|POST", name="organization_edit", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Organization $organization
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function edit(Request $request, Organization $organization, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));

            if ($form->getClickedButton() && 'saveAndStay' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('organization_edit', ['id' => $organization->getId()]);
            }

            return $this->redirectToRoute('organization_index');
        }

        return $this->render('organization/edit.html.twig', [
            'form' => $form->createView(),
            'organization' => $organization
        ]);
    }

    /**
     * Delete organization
     *
     * @Route("/organization/{id}/delete", methods="DELETE", name="organization_delete", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Organization $organization
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function delete(Request $request, Organization $organization, TranslatorInterface $translator): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {

            if ($organization->getTasksGoods()->count() > 0) {
                return new JsonResponse(['messageError' => $translator->trans('item.deleted_use')]);
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($organization);
            $em->flush();
        }

        return new JsonResponse(['messageSuccess' => $translator->trans('item.deleted_successfully')]);
    }

}
