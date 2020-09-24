<?php

namespace App\Repository;

use App\Entity\TaskGoods;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TaskGoods|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskGoods|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskGoods[]    findAll()
 * @method TaskGoods[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskGoodsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskGoods::class);
    }

    // Get the total number of elements
    public function countTaskGoods()
    {
        return $this
            ->createQueryBuilder('t0')
            ->select("count(t0.id)")
            ->join('t0.organization', 't1')
            ->join('t0.user', 't2')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions = null, $authUser = null): array
    {
        // Create Main Query
        $query = $this->createQueryBuilder('t0');

        // Create Count Query
        $countQuery = $this->createQueryBuilder('t0');
        $countQuery->select('COUNT(t0)');

        $query->join('t0.organization', 't1');
        $query->join('t0.user', 't2');
        $countQuery->join('t0.organization', 't1');
        $countQuery->join('t0.user', 't2');

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
        if ($search['value'] !== '') {
            // $searchItem is what we are looking for
            $searchItem = $search['value'];
            $searchQuery = 't0.id LIKE \'%' . $searchItem . '%\'';
            // $searchQuery .= 't0.date_task_goods LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.goods LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.weight LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t1.abbreviated_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t1.registration_number LIKE \'%' . $searchItem . '%\'';

            $searchQuery .= ' or t2.first_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t2.last_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t2.middle_name LIKE \'%' . $searchItem . '%\'';

            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }

        foreach ($columns as $key => $column) {
            if ($column['name'] !== '') {
                switch ($column['name']) {
                    case 'dateTaskGoods':
                        {
                            $columnSearch = $column['search'];
                            if ($columnSearch['value'] !== '') {

                                $period = json_decode($columnSearch['value'], false);

                                $startDate = ($period->startDate);
                                $endDate = ($period->endDate);

                                $query
                                    ->andWhere('t0.date_task_goods BETWEEN :from AND :to')
                                    ->setParameter('from', $startDate)
                                    ->setParameter('to', $endDate);

                                $countQuery
                                    ->andWhere('t0.date_task_goods BETWEEN :from AND :to')
                                    ->setParameter('from', $startDate)
                                    ->setParameter('to', $endDate);
                            }
                            break;
                        }

                    case 'user':
                        {
                            $columnUser = $column['search'];
                            if ($columnUser['value'] !== '') {

                                if ($columnUser['value'] === 1000) {
                                    $searchQuery = 't2.id = \'' . $authUser->getId() . '\'';
                                } else {
                                    $searchQuery = 't2.department LIKE \'%' . $columnUser['value'] . '%\'';
                                }

                                $query->andWhere($searchQuery);
                                $countQuery->andWhere($searchQuery);
                            }
                            break;
                        }

                    case 'status':
                        {
                            $columnSearch = $column['search'];
                            if ($columnSearch['value'] !== '') {
                                $searchQuery = 't0.status LIKE \'%' . $columnSearch['value'] . '%\'';

                                $query->andWhere($searchQuery);
                                $countQuery->andWhere($searchQuery);
                            }
                            break;
                        }
                }
            }

        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] !== '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'id':
                        {
                            $query->orderBy('t0.id', $order['dir']);
                            break;
                        }
                    case 'dateTaskGoods':
                        {
                            $query->orderBy('t0.date_task_goods', $order['dir']);
                            break;
                        }
                    case 'goods':
                        {
                            $query->orderBy('t0.goods', $order['dir']);
                            break;
                        }
                    case 'organization':
                        {
                            $query->orderBy('t1.registration_number', $order['dir']);
                            break;
                        }
                    case 'status':
                        {
                            $query->orderBy('t0.status', $order['dir']);
                            break;
                        }
                    case 'user':
                        {
                            $query->orderBy('t2.last_name', $order['dir']);
                            $query->addOrderBy('t2.first_name', $order['dir']);
                            $query->addOrderBy('t2.middle_name', $order['dir']);
                            break;
                        }
                }
            }
        }

        // Execute
        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return [
            "results" => $results,
            "countResult" => $countResult
        ];
    }

    public function selectTasksGoods($ids)
    {
        return $this
            ->createQueryBuilder('t0')
            ->join('t0.organization', 't1')
            ->join('t0.user', 't2')
            ->andWhere('t0.id IN (:ids)')
            ->andWhere('t0.status = 2')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function selectTasksGoodsForDriver($period, $driver)
    {
        $period = json_decode($period, false);
        $startDate = $period->startDate;
        $endDate = $period->endDate;

        return $this
            ->createQueryBuilder('t0')
            ->join('t0.organization', 't1')
            ->join('t0.user', 't2')
            ->leftJoin('t0.drivers', 't3')
            ->andWhere('t0.status = 3')
            ->andWhere("t3 IN (:driver)")->setParameter(':driver', $driver)
            ->andWhere('t0.date_task_goods BETWEEN :from AND :to')
            ->setParameter('from', $startDate)
            ->setParameter('to', $endDate)
            ->getQuery()
            ->getResult();
    }
}
