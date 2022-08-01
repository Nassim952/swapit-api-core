<?php

declare(strict_types=1);
namespace App\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AbstractEndPoint extends WebTestCase{
    private $serverInformations = ['ACCEPT' => 'application/json', 'CONTENT_TYPE'=> 'application/merge-patch+json'];
    
    public function getResponseFromRequest(string $method, string $uri, string $payload=''): Response{
        $client = self::createClient();
        $client->request(
            $method,
            $uri,
            [],
            [],
            $this->serverInformations,
            $payload
        );
       
            
    return $client->getResponse();
    }
}