<?php

namespace SMARTASK\APIBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
#use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\EntityBody;

class DefaultControllerTest extends WebTestCase
{
    public function testGetUser()
    {   	
    	$client = new Client();
    	$response = $client->request('GET', 'http://localhost/web/app_dev.php/api/users/2', [
    			'auth' => ['user', 'pass']
    	]);
    	echo $response->getStatusCode();
    	
    	$this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testGetUserTasks()
    {
    	$client = new Client();
    	$response = $client->request('GET', 'http://localhost/web/app_dev.php/api/users/2/tasks', [
    			'auth' => ['user', 'pass']
    	]);
    	 
    	$this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testGetUserContacts()
    {
    	$client = new Client();
    	$response = $client->request('GET', 'http://localhost/web/app_dev.php/api/users/2/contacts', [
    			'auth' => ['user', 'pass']
    	]);
    
    	$this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testGetUserGroups()
    {
    	$client = new Client();
    	$response = $client->request('GET', 'http://localhost/web/app_dev.php/api/users/2/groups', [
    			'auth' => ['user', 'pass']
    	]);
    
    	$this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testPostUser()
    {
    	$client = new Client();
    	$response = $client->request('POST', 'http://localhost/web/app_dev.php/api/users', [
    			'username' => 'testapi',
    			'email' =>'testapi@test.com',
    			'plainPassword' =>'testapi'
    	]);
    
    	$this->assertEquals(201, $response->getStatusCode());
    }
    
    
}
