<?php

namespace App\Service;


use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Interfaces\CrudInterface;


class BookService implements CrudInterface
{
    private $operation;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->response = new JsonResponse;
    }

    private function CheckUnique($data): array
    {

        $res = false;
        $id  = false;
        $name = false;

        if ($this->operation == 'update') {
            //Проверка существования id
            $selected_target_book = $this->em->getRepository(Book::class)->find($data['id']);
            if (!is_null($selected_target_book) and is_object($selected_target_book)) {
                $id = true;
                //Проверка совпадений name
                $check = $this->CheckDubles($selected_target_book, $data);
                if ($check) {
                    $name = true;
                } else {
                    $name = false;
                    $this->response->setStatusCode(422);
                    $this->response->setData(['message' => 'Error, name is Used!!!']);
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
                $this->response->setData(['message' => 'Error, Name is Used!!!']);
            }
        }

        if ($id and $name) {
            $res = true;
        }
        unset($id, $name);
        $result = ['response' => $this->response,  'res' => $res];
        //dd($result);
        return $result;
    }
    private function CheckExistAuthor($data): array
    {

        $res = false;

        //Проверка существования автора для книги
        $selected_target_author = $this->em->getRepository(Author::class)->find($data['author']);
        if (!is_null($selected_target_author) and is_object($selected_target_author)) {
            $this->response->setStatusCode(200);
            $this->response->setData(['message' => 'Success']);
            $res = true;
        } else {
            //Автор не обнаружен
            $this->response->setStatusCode(404);
            $this->response->setData(['message' => 'Error, Author is not found!!!']);
            $res = false;
        }

        unset($selected_target_author);
        $result = ['response' => $this->response,  'res' => $res];
        return $result;
    }

    private function CheckDubles($targetId, $data)
    {
        $check_exist_book = $this->em->getRepository(Book::class)->findOneBy([
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

    private function WriteData($data): bool
    {
        //Запись
        if ($this->operation == 'update') {
            //Запись изменений существующий данных
            $selected_target_book = $this->em->getRepository(Book::class)->find($data['id']);
            $selected_target_book->setName($data['name']);
            $this->em->persist($selected_target_book);
            $this->em->flush();
            unset($selected_target_book);
            return true;
        } elseif ($this->operation == 'create') {
            //Запись новых данных 
            $selected_target_author = $this->em->getRepository(Author::class)->find($data['author']);
            if (!is_null($selected_target_author) and is_object($selected_target_author)) {
                $book = new Book;
                $book->setName($data['name']);
                $book->setAuthor($selected_target_author);
                $this->em->persist($book);
                $this->em->flush();
                //unset($book, $selected_target_author);
                if (is_integer($book->getId())) {
                    return true;
                } else {
                    return false;
                }
            }
        } elseif ($this->operation == 'delete') {
            //удаление данных 
            $selected_target_book = $this->em->getRepository(Book::class)->find($data['id']);
            $this->em->remove($selected_target_book);
            $this->em->flush();
            unset($selected_target_book);
            return true;
        }
    }
    public function Create($data): array
    {
        $this->setOperation('create');
        $chek_unique = $this->CheckUnique($data);
        if ($chek_unique['res']) {
            $checkExistAuthor = $this->CheckExistAuthor($data);
            if ($checkExistAuthor['res']) {

                $write = $this->WriteData($data);
                $this->response->setStatusCode(200);
                $this->response->setData(['message' => 'Success']);
                $result = ['response' => $this->response,  'res' => $write];
                return $result;
            } else {
                return $checkExistAuthor;
            }
        } else {
            return $chek_unique;
        }
    }
    public function Read($data): array
    {
        $this->setOperation('read');
        if (!is_null($data) and is_object($data)) {
            $bookId =  $data->get('book_id');
            unset($data);
            $data['id'] = (int) $bookId;
        }
        $book = $this->em->getRepository(Book::class)->find($data['id']);
        $read = [];
        if (!is_null($book) and is_object($book)) {
            $author = $this->em->getRepository(Author::class)->find($book->getAuthor());
            $read = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                //                'author' => serialize($author)
                'author' => [['id' => $author->getId(), 'name' => $author->getName() . ' ' . $author->getSurname() . ' ' . $author->getMiddlename()]],
            ];
            $result = ['response' => $this->response,  'res' => $read];
            return $result;
        } else {
            $this->response->setData(['message' => 'Book is not found']);
            $result = ['response' => $this->response,  'res' => false];
            return $result;
        }
    }
    public function Update($data): array
    {
        $this->setOperation('update');
        $chek_unique = $this->CheckUnique($data);
        if ($chek_unique['res']) {
            $checkExistAuthor = $this->CheckExistAuthor($data);
            if ($checkExistAuthor['res']) {
                $write = $this->WriteData($data);
                $this->response->setStatusCode(200);
                $this->response->setData(['message' => 'Success']);
                $result = ['response' => $this->response,  'res' => $write];
                return $result;
            } else {
                return $checkExistAuthor;
            }
        } else {
            return $chek_unique;
        }
    }
    public function Delete($data): array
    {
        $this->setOperation('delete');
        $check_exist_books = $this->CheckExistAuthor($data);
        if ($check_exist_books['res']) {
            $write = $this->WriteData($data);
            $this->response->setStatusCode(200);
            $this->response->setData(['message' => 'Success']);
            $result = ['response' => $this->response,  'res' => $write];
            return $result;
        } else {
            return $check_exist_books;
        }
    }
    private function setOperation($operation)
    {
        $this->operation = $operation;
    }
    public function Search($request)
    {
        $search_body = $request->get('search_body');

        $qb = $this->em->getRepository(Book::class)->createQueryBuilder('books');
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
