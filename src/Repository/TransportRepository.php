<?php

namespace App\Repository;

use App\Entity\Transport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transport|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transport|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transport[]    findAll()
 * @method Transport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transport::class);
    }

    // Get the total number of elements
    public function countTransport()
    {
        return $this
            ->createQueryBuilder('transport')
            ->select("count(transport.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions)
    {
        // Create Main Query
        $query = $this->createQueryBuilder('transport');

        // Create Count Query
        $countQuery = $this->createQueryBuilder('transport');
        $countQuery->select('COUNT(transport)');

        // Other conditions than the ones sent by the Ajax call ?
        if ($otherConditions === null) {
            // No
            // However, add a "always true" condition to keep an uniform treatment in all cases
            $query->where("1=1");
            $countQuery->where("1=1");
        } else {
            // Add condition
            $query->where($otherConditions);
            $countQuery->where($otherConditions);
        }

        // Fields Search
        if ($search['value'] != '') {
            // $searchItem is what we are looking for
            $searchItem = $search['value'];
            $searchQuery = 'transport.number LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or transport.marka LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or transport.model LIKE \'%' . $searchItem . '%\'';

            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'number':
                        {
                            $query->orderBy('transport.number', $order['dir']);
                            break;
                        }
                    case 'marka':
                        {
                            $query->orderBy('transport.marka', $order['dir']);
                            break;
                        }
                    case 'model':
                        {
                            $query->orderBy('transport.model', $order['dir']);
                            $query->orderBy('transport.model', $order['dir']);
                            break;
                        }
                }
            }
        }

        // Execute
        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return array(
            "results" => $results,
            "countResult" => $countResult
        );
    }
}
