<?php

namespace App\Controller;

use App\Entity\Driver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DriverController extends AbstractController
{
    /**
     * Index page
     *
     * @Route("/drivers", name="drivers")
     *
     * @return Response
     */
    public function index() : Response
    {
        return $this->render('driver/index.html.twig', [
            'controller_name' => 'DriverController',
        ]);
    }

    /**
     * Data for datatables
     *
     * @Route("/driver/list/datatables", name="driver_list_datatables")
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function listDatatableAction(Request $request, EntityManagerInterface $em) : JsonResponse
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

        $results = $em->getRepository(Driver::class)->getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions = null);

        // Returned objects are of type Town
        $objects = $results["results"];
        // Get total number of objects
        $total_objects_count = $em->getRepository(Driver::class)->countDriver();
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

        foreach ($objects as $key => $driver)
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
                    case 'fullName':
                        {
                            $responseTemp = "<a href='#' class='float-left'>".$driver->getFullName()."</a>";
                            break;
                        }

                    case 'phone':
                        {
                            $responseTemp = $driver->getPhone();
                            break;
                        }

                    case 'control':
                        {
                            $responseTemp = "<button type='button' class='btn btn-sm btn-danger float-left modal-delete-dialog' data-toggle='modal' data-id='".$driver->getId()."'>Удалить</button>";
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
     * Delete driver
     *
     * @Route("/driver/{id}/delete", name="driver.delete", methods="DELETE", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Driver $driver
     *
     * @return JsonResponse
     */
    public function delete(Request $request, EntityManagerInterface $em, Driver $driver) : JsonResponse
    {
        if ($this->isCsrfTokenValid('delete-item', $request->request->get('_token'))) {
            $em->remove($driver);
            $em->flush();
        }

        return new JsonResponse(['message' => 'Successfully delete driver']);
    }
}
