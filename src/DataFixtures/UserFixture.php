<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user->setUsername('admin');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'admin'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setUsername('Admin');
        $user->setLastName('Admin');
        $user->setMiddleName('Admin');

        $user->setDepartment(15);

        $manager->persist($user);

        $manager->flush();
    }
}
