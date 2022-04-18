<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;
use App\Interfaces\CrudInterface;

class AuthorService implements CrudInterface
{
    private $operation;
    public function Validate($response, $data): array
    {
        $res = false;
        $validator = Validation::createValidator();
        //Валидация ID

        if ($this->operation == 'read') {
            if (!is_null($data) and is_object($data)) {
                $authorId =  $data->get('author_id');
                unset($data);
                $data['id'] = (int) $authorId;
                $name = true;
                $middlename = true;
                $surname = true;
            }
        }
        if ($this->operation == 'update' or $this->operation == 'delete' or $this->operation == 'read') {
            $errors = $validator->validate($data['id'], [
                new Length(['min' => 0, 'max' => 65535]),
                new Type('int',  $message = 'Error, ID value must have Integer', $groups = null, $payload = null, $options = []),
                new  NotBlank()
            ]);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $response->setData(['message' => $error->getMessage()]);
                }
                $response->setStatusCode(422);
                $id = false;
            } else {
                $id = true;
            }
            unset($errors);
        } elseif ($this->operation == 'create') {
            // при создании id генериться автоматом
            $id = true;
        }
        //Валидация ФИО автора
        if ($this->operation == 'update' or $this->operation == 'create') {

            $errors = $validator->validate($data['name'], [
                new Length(['min' => 0, 'max' => 128]),
                new Type('string',  $message = 'Error, name value must have String', $groups = null, $payload = null, $options = []),
                new  NotBlank(),
            ]);
            $errors_middlename = $validator->validate($data['middlename'], [
                new Length(['min' => 0, 'max' => 128]),
                new Type('string',  $message = 'Error, middlename value must have String', $groups = null, $payload = null, $options = []),
                new  NotBlank(),
            ]);
            if (!count($errors) > 0) {
                $errors = $errors_middlename;
            }
            $errors_surname = $validator->validate($data['surname'], [
                new Length(['min' => 0, 'max' => 128]),
                new Type('string',  $message = 'Error, surname value must have String', $groups = null, $payload = null, $options = []),
                new  NotBlank(),
            ]);
            if (!count($errors) > 0) {
                $errors = $errors_surname;
            }
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $response->setData(['message' => $error->getMessage()]);
                }
                $response->setStatusCode(422);
                $name = false;
            } else {
                $name = true;
                $middlename = true;
                $surname = true;
            }
        }
        if ($this->operation == 'delete') {
            $name = true;
            $middlename = true;
            $surname = true;
        }
        if ($id and $name and $surname and $middlename) {
            $res = true;
        }
        unset($validator, $errors, $id, $surname, $middlename);
        $result = ['response' => $response,  'res' => $res];
        return $result;
    }

    private function CheckUnique($em, $response, $data): array
    {

        $res = false;
        $id  = false;
        $name = false;
        $middlename = true;
        $surname = true;
        if ($this->operation == 'update') {
            //Проверка существования id
            $selected_target_author = $em->getRepository(Author::class)->find($data['id']);
            if (!is_null($selected_target_author) and is_object($selected_target_author)) {
                $id = true;
                //Проверка совпадений name
                $check = $this->CheckDubles($em, $selected_target_author, $data);
                if ($check) {
                    $name = true;
                } else {
                    $name = false;
                    $response->setStatusCode(422);
                    $response->setData(['message' => 'Error, FIO Used!!!']);
                }
            } else {
                $id = false;
                $response->setStatusCode(422);
                $response->setData(['message' => 'Error, ID is not found!!!']);
            }
        } elseif ($this->operation == 'create') {
            $id = true;
            //Проверка совпадений name
            $check = $this->CheckDubles($em, null, $data);
            if ($check) {
                $name = true;
            } else {
                $name = false;
                $response->setStatusCode(422);
                $response->setData(['message' => 'Error, FIO is Used!!!']);
            }
        }

        if ($id and $name) {
            $res = true;
        }
        unset($id, $name);
        $result = ['response' => $response,  'res' => $res];
        //dd($result);
        return $result;
    }
    private function CheckExistBooks($em, $response, $data): array
    {

        $res = false;

        //Поиск книг у автора
        $selected_target_author = $em->getRepository(Author::class)->find($data['id']);
        if (!is_null($selected_target_author) and is_object($selected_target_author)) {
            $selected_exist_book = $em->getRepository(Book::class)->findOneBy([
                'author' => $selected_target_author
            ]);
            if (!is_null($selected_exist_book) and is_object($selected_exist_book)) {
                //У автора есть книги
                $response->setStatusCode(422);
                $response->setData(['message' => 'Error, This ID exist books!!!']);
                $res = false;
            } else {
                //У автора есть книги
                $response->setStatusCode(200);
                $response->setData(['message' => 'Success']);
                $res = true;
            }
        } else {
            //Автор не обнаружен
            $response->setStatusCode(404);
            $response->setData(['message' => 'Error, ID is not found!!!']);
            $res = false;
        }

        unset($selected_target_author, $selected_exist_book);
        $result = ['response' => $response,  'res' => $res];
        return $result;
    }

    private function CheckDubles($em, $targetId, $data)
    {
        $check_exist_author = $em->getRepository(Author::class)->findOneBy([
            'name' => $data['name'],
            'middlename' => $data['middlename'],
            'surname' => $data['surname'],
        ]);
        if ($this->operation == 'update') {
            if (!is_null($check_exist_author) and is_object($check_exist_author)) {
                if ($targetId->getId() == $check_exist_author->getId()) {
                    return true;
                    //Изменяется одна и таже запись
                } else {
                    //Дубликаты есть
                    return false;
                }
            } else {
                //Дубликатов нет
                return true;
            }
        }
        if ($this->operation == 'create') {
            if (!is_null($check_exist_author) and is_object($check_exist_author)) {
                //Дубликаты есть
                return false;
            } else {
                //Дубликатов нет
                return true;
            }
        }
    }

    private function WriteData($em, $data): bool
    {
        //Запись
        if ($this->operation == 'update') {
            //Запись изменений существующий данных
            $selected_target_author = $em->getRepository(Author::class)->find($data['id']);
            $selected_target_author->setName($data['name']);
            $selected_target_author->setMiddlename($data['middlename']);
            $selected_target_author->setSurname($data['surname']);
            $em->persist($selected_target_author);
            $em->flush();
            unset($selected_target_author);
            return true;
        } elseif ($this->operation == 'create') {
            //Запись новых данных 
            $author = new Author;
            $author->setName($data['name']);
            $author->setMiddlename($data['middlename']);
            $author->setSurname($data['surname']);
            $em->persist($author);
            $em->flush();
            return true;
        } elseif ($this->operation == 'delete') {
            //удаление данных 
            $selected_target_author = $em->getRepository(Author::class)->find($data['id']);
            $em->remove($selected_target_author);
            $em->flush();
            unset($selected_target_author);
            return true;
        }
    }
    public function Create($em, $response, $data): array
    {
        $chek_unique = $this->CheckUnique($em, $response, $data);
        if ($chek_unique['res']) {
            $write = $this->WriteData($em, $data);
            $response->setStatusCode(200);
            $response->setData(['message' => 'Success']);
            $result = ['response' => $response,  'res' => $write];
            return $result;
        } else {
            return $chek_unique;
        }
    }
    public function Read($em, $response, $data): array
    {
        if (!is_null($data) and is_object($data)) {
            $authorId =  $data->get('author_id');
            unset($data);
            $data['id'] = (int) $authorId;
        }
        $author = $em->getRepository(Author::class)->find($data['id']);
        $read = [];
        if (!is_null($author) and is_object($author)) {
            $read = [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'middlename' => $author->getMiddlename(),
                'surname' => $author->getSurname(),
            ];
            $result = ['response' => $response,  'res' => $read];
            return $result;
        } else {
            $response->setData(['message' => 'Author is not found']);
            $result = ['response' => $response,  'res' => false];
            return $result;
        }
    }
    public function Update($em, $response, $data): array
    {
        $chek_unique = $this->CheckUnique($em, $response, $data);
        if ($chek_unique['res']) {
            $write = $this->WriteData($em, $data);
            $response->setStatusCode(200);
            $response->setData(['message' => 'Success']);
            $result = ['response' => $response,  'res' => $write];
            return $result;
        } else {
            return $chek_unique;
        }
    }
    public function Delete($em, $response, $data): array
    {
        $check_exist_books = $this->CheckExistBooks($em, $response, $data);
        if ($check_exist_books['res']) {
            $write = $this->WriteData($em, $data);
            $response->setStatusCode(200);
            $response->setData(['message' => 'Success']);
            $result = ['response' => $response,  'res' => $write];
            return $result;
        } else {
            return $check_exist_books;
        }
    }
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }
}
