<?php

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Organization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organization[]    findAll()
 * @method Organization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    // Get the total number of elements
    public function countOrganization()
    {
        return $this
            ->createQueryBuilder('organization')
            ->select("count(organization.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions)
    {
        // Create Main Query
        $query = $this->createQueryBuilder('organization');

        // Create Count Query
        $countQuery = $this->createQueryBuilder('organization');
        $countQuery->select('COUNT(organization)');

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
            $searchQuery = 'organization.abbreviated_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or organization.registration_number LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or organization.full_name LIKE \'%' . $searchItem . '%\'';

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
                    case 'abbreviatedName':
                        {
                            $query->orderBy('organization.abbreviated_name', $order['dir']);
                            break;
                        }
                    case 'registrationNumber':
                        {
                            $query->orderBy('organization.registration_number', $order['dir']);
                            break;
                        }
                    case 'fullName':
                        {
                            $query->orderBy('organization.full_name', $order['dir']);
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
