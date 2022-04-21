<?php



namespace App\Service\Validators;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BookValidatorService
{
    public function __construct(ValidatorInterface $validator, EntityManagerInterface $em)
    {

        $this->response = new JsonResponse;
        $this->validator = $validator;
        $this->em = $em;
    }
    public function ValidateCreate($data): array
    {
        //Проверка ID автора
        $errors = $this->validator->validate($data['author'], [new Length(['min' => 0, 'max' => 65535]), new Type('int',  null,  null, null, []), new  NotBlank()]);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->response->setData(['message' => '[author] ' . $error->getMessage()]);
            }
            $this->response->setStatusCode(422);
            $res = false;
        } else {
            //Проверка книги
            $selected_target_author = $this->em->getRepository(Author::class)->find($data['author']);
            $book = new Book;
            $book->setName($data['name']);
            $book->setAuthor($selected_target_author);
            $errors = $this->validator->validate($book);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->response->setData(['message' => '[name]' . $error->getMessage()]);
                }
                $this->response->setStatusCode(422);
                $res = false;
            } else {
                $errors = $this->validator->validate($data['name'], [new Length(['min' => 0, 'max' => 128]), new Type('string', null, null, null, []), new  NotBlank()]);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->response->setData(['message' => '[name] ' . $error->getMessage()]);
                    }
                    $this->response->setStatusCode(422);
                    $res = false;
                } else {
                    $res = true;
                }
            }
        }
        $result = ['response' => $this->response,  'res' => $res];
        return $result;
    }
    public function ValidateUpdate($data): array
    {
        //Проверка ID
        $errors = $this->validator->validate($data['id'], [new Length(['min' => 0, 'max' => 65535]), new Type('int',  null,  null, null, []), new  NotBlank()]);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->response->setData(['message' => '[id] ' . $error->getMessage()]);
            }
            $this->response->setStatusCode(422);
            $res = false;
        } else {
            //Проверка ID автора
            $errors = $this->validator->validate($data['author'], [new Length(['min' => 0, 'max' => 65535]), new Type('int',  null,  null, null, []), new  NotBlank()]);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->response->setData(['message' => '[author] ' . $error->getMessage()]);
                }
                $this->response->setStatusCode(422);
                $res = false;
            } else {
                //Проверка книги
                $selected_target_author = $this->em->getRepository(Author::class)->find($data['author']);
                $book = new Book;
                $book->setName($data['name']);
                $book->setAuthor($selected_target_author);
                $errors = $this->validator->validate($book);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->response->setData(['message' => '[name]' . $error->getMessage()]);
                    }
                    $this->response->setStatusCode(422);
                    $res = false;
                } else {
                    $errors = $this->validator->validate($data['name'], [new Length(['min' => 0, 'max' => 128]), new Type('string', null, null, null, []), new  NotBlank()]);
                    if (count($errors) > 0) {
                        foreach ($errors as $error) {
                            $this->response->setData(['message' => '[name] ' . $error->getMessage()]);
                        }
                        $this->response->setStatusCode(422);
                        $res = false;
                    } else {
                        $res = true;
                    }
                }
            }
        }
        $result = ['response' => $this->response,  'res' => $res];
        return $result;
    }
    public function ValidateRead($request): array
    {
        if (!is_null($request) and is_object($request)) {

            //Проверка ID
            $book_id = (int) $request->get('book_id');
            $errors = $this->validator->validate($book_id, [new Length(['min' => 0, 'max' => 65535]), new Type('int',  null,  null, null, []), new  NotBlank()]);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->response->setData(['message' => '[book_id] ' . $error->getMessage()]);
                }
                $this->response->setStatusCode(422);
                $res = false;
            } else {
                $res = true;
            }
            $result = ['response' => $this->response,  'res' => $res];
            return $result;
        }
    }
    public function ValidateDelete($data): array
    {
        //Проверка ID
        $errors = $this->validator->validate($data['id'], [new Length(['min' => 0, 'max' => 65535]), new Type('int',  null,  null, null, []), new  NotBlank()]);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->response->setData(['message' => '[id] ' . $error->getMessage()]);
            }
            $this->response->setStatusCode(422);
            $res = false;
        } else {
            $res = true;
        }
        $result = ['response' => $this->response,  'res' => $res];
        return $result;
    }
}
