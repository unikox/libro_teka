<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class AuthorTest extends WebTestCase

{
    public $rnd;
    public function testAddAuthor(): void
    {

        //    \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
        //Добавление нового Автора
        $client = static::createClient();
        $this->rnd = rand(1, 1000);

        $test_body = json_decode('{  "name": "Иван' . $this->rnd . '",  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('POST', '/api/v1/author/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testAddExistAuthor(): void
    {
        //Добавление существуюшего Автора
        $client = static::createClient();
        $test_body = json_decode('{ "name": "Иван' . $this->rnd . '",  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('POST', '/api/v1/author/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        //$this->assertJsonStringEqualsJsonString($response->getContent(),  $result_json);
    }
    public function testUpdateAuthorRandomNameNext(): void
    {
        //Редактирование существуюшего Автора
        $client = static::createClient();
        $test_body = json_decode('{"id": 1, "name": "Иван' . $this->rnd . '",  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('PUT', '/api/v1/author/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testUpdateAuthorRandomName(): void
    {
        $client = static::createClient();
        $test_body = json_decode('{"id": 1, "name": "Иван' . $this->rnd . '",  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('PUT', '/api/v1/author/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    //Проверка валидации ADD
    public function testAddWrongAuthorName(): void
    {
        //Попытка добавить имя Автора цифрой
        $client = static::createClient();

        $test_body = json_decode('{"id": 1, "name": 22' . $this->rnd . ',  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('POST', '/api/v1/author/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testAddWrongAuthorMiddlename(): void
    {
        //Попытка добавить Отчество Автора цифрой
        $client = static::createClient();

        $test_body = json_decode('{"id": 1, "name": "Иван"' . $this->rnd . ',  "middlename": 2,  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('POST', '/api/v1/author/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testAddWrongAuthorSurname(): void
    {
        //Попытка добавить  Фамилию Автора цифрой
        $client = static::createClient();

        $test_body = json_decode('{"id": 1, "name": "Иван"' . $this->rnd . ',  "middlename": "Иванович' . $this->rnd . '",  "surname": 3}', true);
        $client->jsonRequest('POST', '/api/v1/author/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testAddWrongAuthorID(): void
    {
        //Попытка добавить id Автора строкой
        $client = static::createClient();
        $test_body = json_decode('{"id": "zz", "name": "Иван' . $this->rnd . '",  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('POST', '/api/v1/author/create',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }


    //Проверка валидации Update
    public function testUpdateWrongAuthorName(): void
    {
        $this->rnd = rand(1, 1000);
        //Попытка изменить имя Автора цифрой
        $client = static::createClient();
        $test_body = json_decode('{  "id": 5, "name": 33, "middlename": "Иванович", "surname": "Иванов" }', true);
        $client->jsonRequest('PUT', '/api/v1/author/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testUpdateWrongAuthorID(): void
    {
        //Попытка изменить id Автора строкой
        $client = static::createClient();
        $test_body = json_decode('{"id": "zz", "name": "Иван' . $this->rnd . '",  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('PUT', '/api/v1/author/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testUpdateEmptyNameAuthor(): void
    {
        //Попытка изменить Автора без имени
        $client = static::createClient();
        $test_body = json_decode('{"id": 1,  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('PUT', '/api/v1/author/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testUpdateEmptyIdAuthor(): void
    {
        //Попытка изменить Автора без ID
        $client = static::createClient();
        $test_body = json_decode('{ "name": "Иван' . $this->rnd . '",  "middlename": "Иванович' . $this->rnd . '",  "surname": "Иванов' . $this->rnd . '"}', true);
        $client->jsonRequest('PUT', '/api/v1/author/update',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    //Проверка метода удаления
    public function testDeleteWrongIdAuthor(): void
    {
        //Попытка удалить Автора c неправильным id
        $client = static::createClient();
        $test_body = json_decode('{ "id":"zz"}', true);
        $client->jsonRequest('DELETE', '/api/v1/author/delete',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testDeleteEmptyIdAuthor(): void
    {
        //Попытка удалить Автора без id
        $client = static::createClient();
        $test_body = json_decode('{ "name":"zz"}', true);
        $client->jsonRequest('DELETE', '/api/v1/author/delete',  $test_body);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }
}
