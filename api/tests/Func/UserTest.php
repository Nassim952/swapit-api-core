<?php

declare(strict_types=1);
namespace App\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserTest extends AbstractEndPoint{


    public function testGetUsers():void{
      
        $response = $this->getResponseFromRequest(Request::METHOD_GET, uri: '/users?page=1');
        $responseContent = $response->getContent();
        $responseDecoded = json_decode($responseContent);
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertJson($responseContent);
        self::assertNotEmpty($responseDecoded);


    }
    // public function testPostUser():void{
      
    //     $response = $this->getResponseFromRequest(
    //         Request::METHOD_POST,
    //          uri: '/api/users',
    //         );
    //     $responseContent = $response->getContent();
    //     $responseDecoded = json_decode($responseContent);
    //     self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
    //     self::assertJson($responseContent);
    //     self::assertNotEmpty($responseDecoded);


    // }

}