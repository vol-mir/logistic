<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function get_class;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // Get the total number of elements
    public function countUser()
    {
        return $this
            ->createQueryBuilder('t0')
            ->select("count(t0.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions = null)
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
            // Add condition
            $query->where($otherConditions);
            $countQuery->where($otherConditions);
        }

        // Fields Search
        if ($search['value'] !== '') {
            // $searchItem is what we are looking for
            $searchItem = $search['value'];
            $searchQuery = 't0.first_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.last_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.middle_name LIKE \'%' . $searchItem . '%\'';
            $searchQuery .= ' or t0.username LIKE \'%' . $searchItem . '%\'';

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
                    case 'fullName':
                        {
                            $query->orderBy('t0.last_name', $order['dir']);
                            $query->addOrderBy('t0.first_name', $order['dir']);
                            $query->addOrderBy('t0.middle_name', $order['dir']);
                            break;
                        }
                    case 'userName':
                        {
                            $query->orderBy('t0.username', $order['dir']);
                            break;
                        }
                    case 'department':
                        {
                            $query->orderBy('t0.department', $order['dir']);
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
