<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Interfaces\CrudInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthorService implements CrudInterface
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->response = new JsonResponse;
    }
    private $operation;

    private function CheckUnique($data): array
    {

        $res = false;
        $id  = false;
        $name = false;
        $middlename = true;
        $surname = true;
        if ($this->operation == 'update') {
            //Проверка существования id
            $selected_target_author = $this->em->getRepository(Author::class)->find($data['id']);
            if (!is_null($selected_target_author) and is_object($selected_target_author)) {
                $id = true;
                //Проверка совпадений name
                $check = $this->CheckDubles($selected_target_author, $data);
                if ($check) {
                    $name = true;
                } else {
                    $name = false;
                    $this->response->setStatusCode(422);
                    $this->response->setData(['message' => 'Error, FIO Used!!!']);
                }
            } else {
                $id = false;
                $this->response->setStatusCode(422);
                $this->response->setData(['message' => 'Error, ID is not found!!!']);
            }
        } elseif ($this->operation == 'create') {
            $id = true;
            //Проверка совпадений name
            $check = $this->CheckDubles(null, $data);
            if ($check) {
                $name = true;
            } else {
                $name = false;
                $this->response->setStatusCode(422);
                $this->response->setData(['message' => 'Error, FIO is Used!!!']);
            }
        }

        if ($id and $name) {
            $res = true;
        }
        unset($id, $name);
        $result = ['response' =>  $this->response, 'res' => $res];
        //dd($result);
        return $result;
    }
    private function CheckExistBooks($data): array
    {

        $res = false;

        //Поиск книг у автора
        $selected_target_author = $this->em->getRepository(Author::class)->find($data['id']);
        if (!is_null($selected_target_author) and is_object($selected_target_author)) {
            $selected_exist_book = $this->em->getRepository(Book::class)->findOneBy([
                'author' => $selected_target_author
            ]);
            if (!is_null($selected_exist_book) and is_object($selected_exist_book)) {
                //У автора есть книги
                $this->response->setStatusCode(422);
                $this->response->setData(['message' => 'Error, This ID exist books!!!']);
                $res = false;
            } else {
                //У автора есть книги
                $this->response->setStatusCode(200);
                $this->response->setData(['message' => 'Success']);
                $res = true;
            }
        } else {
            //Автор не обнаружен
            $this->response->setStatusCode(404);
            $this->response->setData(['message' => 'Error, ID is not found!!!']);
            $res = false;
        }

        unset($selected_target_author, $selected_exist_book);
        $result = ['response' =>  $this->response, 'res' => $res];
        return $result;
    }

    private function CheckDubles($targetId, $data)
    {
        $check_exist_author = $this->em->getRepository(Author::class)->findOneBy([
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

    private function WriteData($data): bool
    {
        //Запись
        if ($this->operation == 'update') {
            //Запись изменений существующий данных
            $selected_target_author = $this->em->getRepository(Author::class)->find($data['id']);
            $selected_target_author->setName($data['name']);
            $selected_target_author->setMiddlename($data['middlename']);
            $selected_target_author->setSurname($data['surname']);
            $this->em->persist($selected_target_author);
            $this->em->flush();
            unset($selected_target_author);
            return true;
        } elseif ($this->operation == 'create') {
            //Запись новых данных 
            $author = new Author;
            $author->setName($data['name']);
            $author->setMiddlename($data['middlename']);
            $author->setSurname($data['surname']);
            $this->em->persist($author);
            $this->em->flush();
            return true;
        } elseif ($this->operation == 'delete') {
            //удаление данных 
            $selected_target_author = $this->em->getRepository(Author::class)->find($data['id']);
            $this->em->remove($selected_target_author);
            $this->em->flush();
            unset($selected_target_author);
            return true;
        }
    }
    public function Create($data): array
    {
        $this->setOperation('create');
        $chek_unique = $this->CheckUnique($data);
        if ($chek_unique['res']) {
            $write = $this->WriteData($data);
            $this->response->setStatusCode(200);
            $this->response->setData(['message' => 'Success']);
            $result = ['response' =>  $this->response, 'res' => $write];
            return $result;
        } else {
            return $chek_unique;
        }
    }
    public function Read($data): array
    {
        $this->setOperation('read');
        if (!is_null($data) and is_object($data)) {
            $authorId =  $data->get('author_id');
            unset($data);
            $data['id'] = (int) $authorId;
        }
        $author = $this->em->getRepository(Author::class)->find($data['id']);
        $read = [];
        if (!is_null($author) and is_object($author)) {
            $read = [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'middlename' => $author->getMiddlename(),
                'surname' => $author->getSurname(),
            ];
            $result = ['response' =>  $this->response, 'res' => $read];
            return $result;
        } else {
            $this->response->setData(['message' => 'Author is not found']);
            $result = ['response' =>  $this->response, 'res' => false];
            return $result;
        }
    }
    public function Update($data): array
    {
        $this->setOperation('update');
        $chek_unique = $this->CheckUnique($data);
        if ($chek_unique['res']) {
            $write = $this->WriteData($data);
            $this->response->setStatusCode(200);
            $this->response->setData(['message' => 'Success']);
            $result = ['response' =>  $this->response, 'res' => $write];
            return $result;
        } else {
            return $chek_unique;
        }
    }
    public function Delete($data): array
    {
        $this->setOperation('delete');
        $check_exist_books = $this->CheckExistBooks($data);
        if ($check_exist_books['res']) {
            $write = $this->WriteData($data);
            $this->response->setStatusCode(200);
            $this->response->setData(['message' => 'Success']);
            $result = ['response' =>  $this->response, 'res' => $write];
            return $result;
        } else {
            return $check_exist_books;
        }
    }
    private function setOperation($operation)
    {
        $this->operation = $operation;
    }
}
