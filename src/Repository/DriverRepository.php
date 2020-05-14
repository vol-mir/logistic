<?php

namespace App\Repository;

use App\Entity\Driver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Driver|null find($id, $lockMode = null, $lockVersion = null)
 * @method Driver|null findOneBy(array $criteria, array $orderBy = null)
 * @method Driver[]    findAll()
 * @method Driver[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DriverRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Driver::class);
    }


    // Get the total number of elements
    public function countDriver()
    {
        return $this
            ->createQueryBuilder('driver')
            ->select("count(driver.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions)
    {
        // Create Main Query
        $query = $this->createQueryBuilder('driver');

        // Create Count Query
        $countQuery = $this->createQueryBuilder('driver');
        $countQuery->select('COUNT(driver)');

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
            $searchQuery = 'driver.first_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or driver.last_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or driver.middle_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or driver.phone LIKE \'%' . $searchItem . '%\'';

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
                    case 'fullName':
                        {
                            $query->orderBy('driver.last_name', $order['dir']);
                            $query->addOrderBy('driver.first_name', $order['dir']);
                            $query->addOrderBy('driver.middle_name', $order['dir']);
                            break;
                        }
                    case 'phone':
                        {
                            $query->orderBy('driver.phone', $order['dir']);
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
