<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Driver;
use App\Entity\Organization;
use App\Entity\Transport;
use App\Entity\User;
use App\Entity\TaskGoods;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixture extends Fixture
{
    private $passwordEncoder;
    private $adminUser;
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create();

        $this->loadUser($manager);
        $this->loadDriver($manager);
        $this->loadOrganization($manager);
        $this->loadTransport($manager);
        $this->loadTaskGoods($manager);
    }

    public function loadUser($manager)
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

        $this->adminUser = $user;
    }

    public function loadDriver($manager)
    {
        $faker = $this->faker;

        for ($i = 1; $i <= 20; $i++) {
            $driver = new Driver();
            $driver->setFirstName($faker->firstName);
            $driver->setLastName($faker->lastName);
            $driver->setMiddleName($faker->suffix);
            $driver->setPhone($faker->tollFreePhoneNumber);
            $manager->persist($driver);
            $manager->flush();
        }

    }

    public function loadOrganization($manager)
    {
        $faker = $this->faker;

        for ($i = 1; $i <= 20; $i++) {
            $organization = new Organization();
            $organization->setRegistrationNumber($faker->numberBetween($min = 100000000, $max = 900000000));
            $organization->setAbbreviatedName($faker->companySuffix);
            $organization->setFullName($faker->company);
            $organization->setBaseContactPerson($faker->name);
            $organization->setBaseWorkingHours($faker->text($maxNbChars = 20));
            $manager->persist($organization);
            $manager->flush();

            for ($j = 1; $j <= 5; $j++) {
                $address = new Address();
                $address->setStreet($faker->streetName);
                $address->setCity($faker->city);
                $address->setCountry($faker->country);
                $address->setPostcode($faker->postcode);
                $address->setPointName($faker->text($maxNbChars = 20));
                $address->setRegion($faker->state);
                $address->setOrganization($organization);
                $manager->persist($address);
                $manager->flush();
            }

        }
    }

    public function loadTransport($manager)
    {
        $faker = $this->faker;

        for ($i = 1; $i <= 20; $i++) {
            $transport = new Transport();
            $transport->setMarka($faker->word);
            $transport->setModel($faker->word);
            $transport->setNumber($faker->randomLetter . $faker->randomLetter . '-' . $faker->numberBetween($min = 100000, $max = 900000));
            $transport->setKind($faker->numberBetween($min = 1, $max = 8));
            $transport->setCarrying($faker->randomNumber);
            $manager->persist($transport);
            $manager->flush();
        }
    }

    public function loadTaskGoods($manager)
    {
        $faker = $this->faker;

        for ($i = 1; $i <= 20; $i++) {
            $taskGoods = new TaskGoods();
            $taskGoods->setGoods($faker->sentence);
            $taskGoods->setWeight($faker->randomDigit);
            $taskGoods->setUnit($faker->numberBetween($min = 1, $max = 4));
            $taskGoods->setDimensions($faker->bothify('### x ###'));
            $taskGoods->setNumberOfPackages($faker->randomDigit);
            $taskGoods->setLoadingNature($faker->numberBetween($min = 1, $max = 4));
            $taskGoods->setContactPerson($faker->name);
            $taskGoods->setWorkingHours($faker->text($maxNbChars = 20));
            $taskGoods->setNote($faker->sentence($nbWords = 6, $variableNbWords = true));

            $organization = $manager->getRepository(Organization::class)->findOneOrganizationRandom();
            $taskGoods->setOrganization($organization);

            $addressGoodsYard = $manager->getRepository(Address::class)->findOneAddressForOrganizationRandom($organization->getId());
            $taskGoods->setAddressGoodsYard($addressGoodsYard);

            $addressOffice = $manager->getRepository(Address::class)->findOneAddressForOrganizationRandom($organization->getId());
            $taskGoods->setAddressOffice($addressOffice);

            $taskGoods->setDateTaskGoods($faker->dateTime($max = 'now', $timezone = null));
            $taskGoods->setStatus($faker->numberBetween($min = 1, $max = 6));

            $taskGoods->setUser($this->adminUser);

            $manager->persist($taskGoods);
            $manager->flush();
        }
    }
}


