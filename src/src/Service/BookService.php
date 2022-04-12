<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class BookService
{
    private $operation;
    public function Validate($response, $data): array
    {
        $res = false;
        $validator = Validation::createValidator();
        //Валидация ID

        if ($this->operation == 'read') {
            if (!is_null($data) and is_object($data)) {
                $bookId =  $data->get('book_id');
                unset($data);
                $data['id'] = (int) $bookId;
                $name = true;
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
        //Валидация названия книги и id автора
        if ($this->operation == 'update' or $this->operation == 'create') {

            $errors = $validator->validate($data['name'], [
                new Length(['min' => 0, 'max' => 128]),
                new Type('string',  $message = 'Error, name value must have String', $groups = null, $payload = null, $options = []),
                new  NotBlank(),
            ]);
            $errors_author_id = $validator->validate($data['author'], [
                new Length(['min' => 0, 'max' => 65535]),
                new Type('int',  $message = 'Error, Author value must have Integer', $groups = null, $payload = null, $options = []),
                new  NotBlank()
            ]);
            if (!count($errors) > 0) {
                $errors = $errors_author_id;
            }
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $response->setData(['message' => $error->getMessage()]);
                }
                $response->setStatusCode(422);
                $name = false;
            } else {
                $name = true;
            }
        }
        if ($this->operation == 'delete') {
            $name = true;
        }
        if ($id and $name) {
            $res = true;
        }
        unset($validator, $errors, $id, $name);
        $result = ['response' => $response,  'res' => $res];
        return $result;
    }

    private function CheckUnique($em, $response, $data): array
    {

        $res = false;
        $id  = false;
        $name = false;

        if ($this->operation == 'update') {
            //Проверка существования id
            $selected_target_book = $em->getRepository(Book::class)->find($data['id']);
            if (!is_null($selected_target_book) and is_object($selected_target_book)) {
                $id = true;
                //Проверка совпадений name
                $check = $this->CheckDubles($em, $selected_target_book, $data);
                if ($check) {
                    $name = true;
                } else {
                    $name = false;
                    $response->setStatusCode(422);
                    $response->setData(['message' => 'Error, name is Used!!!']);
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
                $response->setData(['message' => 'Error, Name is Used!!!']);
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
    private function CheckExistAuthor($em, $response, $data): array
    {

        $res = false;

        //Проверка существования автора для книги
        $selected_target_author = $em->getRepository(Author::class)->find($data['author']);
        if (!is_null($selected_target_author) and is_object($selected_target_author)) {
            $response->setStatusCode(200);
            $response->setData(['message' => 'Success']);
            $res = true;
        } else {
            //Автор не обнаружен
            $response->setStatusCode(404);
            $response->setData(['message' => 'Error, Author is not found!!!']);
            $res = false;
        }

        unset($selected_target_author);
        $result = ['response' => $response,  'res' => $res];
        return $result;
    }

    private function CheckDubles($em, $targetId, $data)
    {
        $check_exist_book = $em->getRepository(Book::class)->findOneBy([
            'name' => $data['name'],
        ]);
        if ($this->operation == 'update') {
            if (!is_null($check_exist_book) and is_object($check_exist_book)) {
                if ($targetId->getId() == $check_exist_book->getId()) {
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
            if (!is_null($check_exist_book) and is_object($check_exist_book)) {
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
            $selected_target_book = $em->getRepository(Book::class)->find($data['id']);
            $selected_target_book->setName($data['name']);
            $em->persist($selected_target_book);
            $em->flush();
            unset($selected_target_book);
            return true;
        } elseif ($this->operation == 'create') {
            //Запись новых данных 
            $selected_target_author = $em->getRepository(Author::class)->find($data['author']);
            if (!is_null($selected_target_author) and is_object($selected_target_author)) {
                $book = new Book;
                $book->setName($data['name']);
                $book->setAuthor($selected_target_author);
                $em->persist($book);
                $em->flush();
                //unset($book, $selected_target_author);
                if (is_integer($book->getId())) {
                    return true;
                } else {
                    return false;
                }
            }
        } elseif ($this->operation == 'delete') {
            //удаление данных 
            $selected_target_book = $em->getRepository(Book::class)->find($data['id']);
            $em->remove($selected_target_book);
            $em->flush();
            unset($selected_target_book);
            return true;
        }
    }
    public function Create($em, $response, $data)
    {
        $chek_unique = $this->CheckUnique($em, $response, $data);
        if ($chek_unique['res']) {
            $checkExistAuthor = $this->CheckExistAuthor($em, $response, $data);
            if ($checkExistAuthor['res']) {

                $write = $this->WriteData($em, $data);
                $response->setStatusCode(200);
                $response->setData(['message' => 'Success']);
                $result = ['response' => $response,  'res' => $write];
                return $result;
            } else {
                return $checkExistAuthor;
            }
        } else {
            return $chek_unique;
        }
    }
    public function Read($em, $response, $data)
    {
        if (!is_null($data) and is_object($data)) {
            $bookId =  $data->get('book_id');
            unset($data);
            $data['id'] = (int) $bookId;
        }
        $book = $em->getRepository(Book::class)->find($data['id']);
        $read = [];
        if (!is_null($book) and is_object($book)) {
            $author = $em->getRepository(Author::class)->find($book->getAuthor());
            $read = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                //                'author' => serialize($author)
                'author' => [['id' => $author->getId(), 'name' => $author->getName() . ' ' . $author->getSurname() . ' ' . $author->getMiddlename()]],
            ];
            $result = ['response' => $response,  'res' => $read];
            return $result;
        } else {
            $response->setData(['message' => 'Book is not found']);
            $result = ['response' => $response,  'res' => false];
            return $result;
        }
    }
    public function Update($em, $response, $data)
    {
        $chek_unique = $this->CheckUnique($em, $response, $data);
        if ($chek_unique['res']) {
            $checkExistAuthor = $this->CheckExistAuthor($em, $response, $data);
            if ($checkExistAuthor['res']) {
                $write = $this->WriteData($em, $data);
                $response->setStatusCode(200);
                $response->setData(['message' => 'Success']);
                $result = ['response' => $response,  'res' => $write];
                return $result;
            } else {
                return $checkExistAuthor;
            }
        } else {
            return $chek_unique;
        }
    }
    public function Delete($em, $response, $data)
    {
        $check_exist_books = $this->CheckExistAuthor($em, $response, $data);
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
    public function Search($em, $response, $request)
    {
        $search_body = $request->get('search_body');

        $qb = $em->getRepository(Book::class)->createQueryBuilder('books');
        $search_result = $qb->Where($qb->expr()->like('books.name', ':search'))
            ->InnerJoin((Author::class), 'a',  'a =  books.author')
            ->setParameter('search', '%' . $search_body . '%')
            ->getQuery()
            //    ->getSql();
            ->getResult();
        $res_book = [];
        foreach ($search_result as $num_books => $book) {
            $author = $book->getAuthor();
            $loaded_book = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'author' => [['id' => $author->getId(), 'name' => $author->getName() . ' ' . $author->getSurname() . ' ' . $author->getMiddlename()]],
            ];
            $res_book[$num_books] = $loaded_book;
        }
        $result = ['response' => $res_book,  'res' => true];
        return $result;
    }
}
