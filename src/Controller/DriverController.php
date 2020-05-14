<?php

namespace App\Controller;

use App\Entity\Driver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DriverController extends AbstractController
{
    /**
     * @Route("/drivers", name="drivers")
     */
    public function index()
    {
        return $this->render('driver/index.html.twig', [
            'controller_name' => 'DriverController',
        ]);
    }

    /**
     * @Route("/driver/list/datatables", name="driver_list_datatables")
     */
    public function listDatatableAction(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        // Get the parameters from DataTable Ajax Call
        // $logger->info(json_encode($search));

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

        foreach ($objects as $key => $town)
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
                            $responseTemp = "<a href='#' class='float-left'>".$town->getFullName()."</a>";
                            break;
                        }

                    case 'phone':
                        {
                            $responseTemp = $town->getPhone();
                            break;
                        }

                    case 'control':
                        {
                            $responseTemp = "<a href='#' class='btn btn-sm btn-danger float-left'>Удалить</a>";
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
}
