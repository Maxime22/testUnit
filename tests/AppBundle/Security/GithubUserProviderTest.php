<?php

namespace Tests\AppBundle\Security;

use PHPUnit\Framework\TestCase;
use AppBundle\Security\GithubUserProvider;
use AppBundle\Entity\User;

class GithubUserProviderTest extends TestCase
{
    private $client;
    private $serializer;
    private $streamedResponse;
    private $response;

    // setUp() n'a pas besoin d'être appelée en début de chaque fonction, c'est fait automatiquement
    public function setUp()
    {
        // Doublure 1
        $this->client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        // Doublure 2
        $this->serializer = $this
            ->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        // Doublure 4
        // D'après la documentation, la méthode getBody retourne un objet de type Psr\Http\Message\StreamInterface.
        $this->streamedResponse = $this
            ->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

        // Doublure 3
        // on redéfinie la response
        // on crée une doublure de response et on indique que get renvoie response (donc plus null) --> stub
        $this->response = $this
            ->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();
    }

    // réinitialise les valeurs à la fin des tests
    public function tearDown()
    {
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;
    }

    public function testLoadUserByUsernameReturningAUser()
    {

        // $client->method('get')->willReturn($response); // redéfinition de la méthode get('https://api.github.com/user?access_token='.$username);
        // en rajoutant une expectation, et donc une assertion
        $client
            ->expects($this->once()) // Nous nous attendons à ce que la méthode get soit appelée une fois
            ->method('get')
            ->willReturn($response)
            ;

        // $response->method('getBody')->willReturn($streamedResponse);
        // en rajoutant une expectation, et donc une assertion
        $response
            ->expects($this->once()) // Nous nous attendons à ce que la méthode getBody soit appelée une fois
            ->method('getBody')
            ->willReturn($streamedResponse);

        // on imagine que userData renvoie bien un array comme-ci dessous
        $userData = ['login' => 'a login', 'name' => 'user name', 'email' => 'adress@mail.com', 'avatar_url' => 'url to the avatar', 'html_url' => 'url to profile'];
        
        // $serializer->method('deserialize')->willReturn($userData);
        // en rajoutant une expectation, et donc une assertion
        $serializer
            ->expects($this->once()) // Nous nous attendons à ce que la méthode deserialize soit appelée une fois
            ->method('deserialize')
            ->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($client, $serializer);

        $user = $githubUserProvider->loadUserByUsername('an-access-token');

        // Assurez-vous maintenant que l'objet retourné par la méthode testée contient bien toutes les informations attendues :
        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);
        
        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('AppBundle\Entity\User', get_class($user));
    }

    public function testloadUserByUsernameWithEmptyUserData()
    {

        // $client->method('get')->willReturn($response); // redéfinition de la méthode get('https://api.github.com/user?access_token='.$username);
        // en rajoutant une expectation, et donc une assertion
        $client
            ->expects($this->once()) // Nous nous attendons à ce que la méthode get soit appelée une fois
            ->method('get')
            ->willReturn($response)
            ;

        // $response->method('getBody')->willReturn($streamedResponse);
        // en rajoutant une expectation, et donc une assertion
        $response
            ->expects($this->once()) // Nous nous attendons à ce que la méthode getBody soit appelée une fois
            ->method('getBody')
            ->willReturn($streamedResponse);

        // on imagine que userData renvoie bien un array comme-ci dessous
        $userData = null;
        
        // $serializer->method('deserialize')->willReturn($userData);
        // en rajoutant une expectation, et donc une assertion
        $serializer
            ->expects($this->once()) // Nous nous attendons à ce que la méthode deserialize soit appelée une fois
            ->method('deserialize')
            ->willReturn($userData);
        
        $githubUserProvider = new GithubUserProvider($client, $serializer);
        $this->expectException('LogicException');
        $githubUserProvider->loadUserByUsername('an-access-token');
    }
}