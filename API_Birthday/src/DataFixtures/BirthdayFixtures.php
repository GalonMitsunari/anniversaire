<?php
// src/DataFixtures/BirthdayFixtures.php

namespace App\DataFixtures;

use App\Entity\Birthday;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BirthdayFixtures extends Fixture
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $names = ['John', 'Alice', 'Bob', 'Emma', 'Michael', 'Olivia', 'David', 'Sophia', 'James', 'Charlotte'];

        foreach ($names as $name) {
            $birthday = new Birthday();
            $birthday->setName($name);
            $birthday->setBirthday($this->faker->dateTimeThisDecade());
            $manager->persist($birthday);
        }

        $manager->flush();

        UserFactory::createMany(10, ['password'=> '$2y$13$QTjcHmnufBwmdWMacTFHoeGa8AvmEZPyq4/cwKkPWJ3XLKlztXByu']) ;
    }

}
