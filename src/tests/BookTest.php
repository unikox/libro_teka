<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class BookTest extends WebTestCase

{
    public $rnd;
    public function testAddBook(): void
    {

        //    \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
        //Добавление новой книги
        $client = static::createClient();
        $this->rnd = rand(1, 1000);

        $test_body = json_decode('{  "name": "Война и мир' . $this->rnd . '", "author":1}', true);
        $client->jsonRequest('POST', '/api/v1/book/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testAddExistBook(): void
    {
        //Добавление существуюшей книги
        $client = static::createClient();
        $test_body = json_decode('{  "name": "Война и мир' . $this->rnd . '", "author":1}', true);
        $client->jsonRequest('POST', '/api/v1/book/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        //$this->assertJsonStringEqualsJsonString($response->getContent(),  $result_json);
    }
    public function testUpdateBookRandomNameNext(): void
    {
        //Редактирование существуюшей книги
        $client = static::createClient();
        $test_body = json_decode('{ "id": 1, "name": "Война и мир' . $this->rnd . '" , "author":1 }', true);
        $client->jsonRequest('PUT', '/api/v1/book/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testUpdateBookRandomName(): void
    {
        $client = static::createClient();
        $test_body = json_decode('{ "id": 1, "name": "Война и мир' . $this->rnd . '", "author":1}', true);
        $client->jsonRequest('PUT', '/api/v1/book/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    //Проверка валидации ADD
    public function testAddWrongBookName(): void
    {
        //Попытка добавить имя книги цифрой
        $client = static::createClient();
        $test_body = json_decode('{ "name": 22, "author":1}', true);
        $client->jsonRequest('POST', '/api/v1/book/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAddWrongBookID(): void
    {
        //Попытка добавить id книги строкой
        $client = static::createClient();
        $test_body = json_decode('{ "id": "zz", "name": 33, "author":1}', true);
        $client->jsonRequest('POST', '/api/v1/book/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }


    //Проверка валидации Update
    public function testUpdateWrongBookName(): void
    {
        $this->rnd = rand(1, 1000);
        //Попытка изменить имя книги цифрой
        $client = static::createClient();
        $test_body = json_decode('{ "id": 1, "name": 33, "author":1}', true);
        $client->jsonRequest('PUT', '/api/v1/book/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testUpdateWrongBookID(): void
    {
        //Попытка изменить id книги строкой
        $client = static::createClient();
        $test_body = json_decode('{ "id": "zz", "name": "Война и мир' . $this->rnd . '", "author":1}', true);
        $client->jsonRequest('PUT', '/api/v1/book/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testUpdateEmptyNameBook(): void
    {
        //Попытка изменить книги без имени
        $client = static::createClient();
        $test_body = json_decode('{ "id": "zz", "author":1}', true);
        $client->jsonRequest('PUT', '/api/v1/book/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testUpdateEmptyIdBook(): void
    {
        //Попытка изменить книги без ID
        $client = static::createClient();

        $test_body = json_decode('{ "name": "Война и мир' . $this->rnd . '", "author":1}', true);
        $client->jsonRequest('PUT', '/api/v1/book/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    //Проверка метода удаления
    public function testDeleteWrongIdBook(): void
    {
        //Попытка удалить книгу c неправильным id
        $client = static::createClient();
        $test_body = json_decode('{ "id":"zz"}', true);
        $client->jsonRequest('DELETE', '/api/v1/book/delete',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testDeleteEmptyIdBook(): void
    {
        //Попытка удалить книгу без id
        $client = static::createClient();
        $test_body = json_decode('{ "name":"zz"}', true);
        $client->jsonRequest('DELETE', '/api/v1/book/delete',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
}
