<?php
namespace App\Tests;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
final class UsersTest extends AbstractTest
{
    public function testAdminResource()
    {
        $response = $this->createClientWithCredentials()->request('GET', '/users');
        $this->assertResponseIsSuccessful();
    }

    public function testLoginAsUser()
    {
        $token = $this->getToken([
            'username' => 'user@example.com',
            'password' => '$3cr3t',
        ]);

        $response = $this->createClientWithCredentials($token)->request('GET', '/users');
        $this->assertJsonContains(['hydra:description' => 'Access Denied.']);
        $this->assertResponseStatusCodeSame(403);
    }
}