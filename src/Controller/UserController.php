<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Security("is_granted('ROLE_ADMIN')", statusCode=404, message="Post not found")
 */
class UserController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/users", methods="GET", name="user_index")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig');
    }

    /**
     * Data for tables
     *
     * @Route("/user/datatables", methods="POST", name="user_datatables")
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

        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(User::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $totalObjectsCount = $em->getRepository(User::class)->countUser();
        // Get total number of filtered data
        $filteredObjectsCount = $results["countResult"];

        $data = [];
        foreach ($objects as $user) {
            $dataTemp = [];
            foreach ($columns as $column) {
                switch ($column['name']) {
                    case 'fullName':
                        {
                            $elementTemp = $this->render('default/table_href.html.twig', [
                                'url' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
                                'urlName' => $user->getFullName()
                            ])->getContent();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'userName':
                        {
                            $elementTemp = $user->getUsername();
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'department':
                        {
                            $elementTemp = $translator->trans(User::DEPARTMENTS[$user->getDepartment()]);
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'roles':
                        {
                            $elementTemp = "";
                            foreach ($user->getRoles() as $key => $val) {
                                $elementTemp .= "<span class='badge badge-primary'>" . $val . "</span> ";
                            }
                            $dataTemp[] = $elementTemp;
                            break;
                        }

                    case 'control':
                        {
                            $elementTemp = $this->render('default/table_group_btn.html.twig', [
                                'urlEdit' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
                                'idDelete' => $user->getId()
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
     * Creates a new user entity
     *
     * @Route("/user/new", methods="GET|POST", name="user_new")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param UserPasswordEncoderInterface $encoder
     *
     *
     * @return RedirectResponse|Response
     */
    public function new(Request $request, TranslatorInterface $translator, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('plain_password')->getData();
            if ($plainPassword) {
                $encoded = $encoder->encodePassword($user, $plainPassword);
                $user->setPassword($encoded);
            }

            $arrRoles = $form->get('roles')->getData();
            if ($arrRoles) {
                $user->setRoles($arrRoles);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('item.created_successfully'));

            if ($form->getClickedButton() && 'saveAndCreateNew' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('user_new');
            }

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit the user entity
     * @Route("/user/{id}/edit", methods="GET|POST", name="user_edit", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param User $user
     * @param TranslatorInterface $translator
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return Response
     */
    public function edit(Request $request, User $user, TranslatorInterface $translator, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('plain_password')->getData();
            if ($plainPassword) {
                $encoded = $encoder->encodePassword($user, $plainPassword);
                $user->setPassword($encoded);
            }

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $translator->trans('item.edited_successfully'));

            if ($form->getClickedButton() && 'saveAndStay' === $form->getClickedButton()->getName()) {

                return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
            }

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Delete user
     *
     * @Route("/user/{id}/delete", methods="DELETE", name="user_delete", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param User $user
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function delete(Request $request, User $user, TranslatorInterface $translator): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return new JsonResponse(['message' => $translator->trans('item.deleted_successfully')]);
    }
}
