<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('Testuser');
        $user->setRoles(['ROLE_USER', 'ROLE_API']);
        $user->setPassword('test123');

        $manager->persist($user);

        $apiToken = new ApiToken($user);
        $apiToken->setToken('b8d50f223c311b744603c5c0a71a51af4f8df68a352ed83066dfe731a58869ac1d757e8e5b7c2c82fbe4a049ad569e55831037587dbefb40aa88f1b2');

        $manager->persist($apiToken);

        $manager->flush();
    }
}
