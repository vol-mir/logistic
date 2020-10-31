<?php

namespace App\Repository;

use App\Entity\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Address|null find($id, $lockMode = null, $lockVersion = null)
 * @method Address|null findOneBy(array $criteria, array $orderBy = null)
 * @method Address[]    findAll()
 * @method Address[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    // Get addresses for organization
    public function getAddressesOrganization(int $organizationId = null)
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.organization = :organizationId')
            ->orderBy('a.point_name', 'ASC')
            ->addOrderBy('a.postcode', 'ASC')
            ->addOrderBy('a.country', 'ASC')
            ->addOrderBy('a.region', 'ASC')
            ->addOrderBy('a.city', 'ASC')
            ->addOrderBy('a.locality', 'ASC')
            ->addOrderBy('a.street', 'ASC')
            ->setParameter('organizationId', $organizationId)
            ->getQuery()
            ->getResult();
    }

    // Get the total number of elements
    public function countAddress($otherConditions = null)
    {
        $countQuery = $this->createQueryBuilder('t0');
        $countQuery->select("count(t0.id)");

        foreach ($otherConditions as $value) {
            $countQuery->where($value);
        }

        return $countQuery->getQuery()->getSingleScalarResult();
    }

    public function getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions = null): array
    {
        // Create Main Query
        $query = $this->createQueryBuilder('t0');

        // Create Count Query
        $countQuery = $this->createQueryBuilder('t0');
        $countQuery->select('COUNT(t0)');

        // Other conditions than the ones sent by the Ajax call ?
        if ($otherConditions === null) {
            // No
            // However, add a "always true" condition to keep an uniform treatment in all cases
            $query->where("1=1");
            $countQuery->where("1=1");
        } else {
            foreach ($otherConditions as $value) {
                $query->where($value);
                $countQuery->where($value);
            }
        }

        // Fields Search
        if ($search['value'] !== '') {
            // $searchItem is what we are looking for
            $searchItem = $search['value'];
            $searchQuery = 't0.point_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.postcode LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.country LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.region LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.city LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.locality LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.street LIKE \'%' . $searchItem . '%\'';

            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] !== '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'pointName':
                        {
                            $query->orderBy('t0.point_name', $order['dir']);
                            break;
                        }
                    case 'fullAddress':
                        {
                            $query->orderBy('t0.postcode', $order['dir']);
                            $query->addOrderBy('t0.country', $order['dir']);
                            $query->addOrderBy('t0.region', $order['dir']);
                            $query->addOrderBy('t0.city', $order['dir']);
                            $query->addOrderBy('t0.locality', $order['dir']);
                            $query->addOrderBy('t0.street', $order['dir']);
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

    public function findOneAddressForOrganizationRandom(int $organizationId = null)
    {
        return $this->createQueryBuilder('entity')
            ->where('entity.organization = :organizationId')
            ->orderBy('RAND()')
            ->setParameter('organizationId', $organizationId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
