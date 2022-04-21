<?php



namespace App\Service\Validators;

use App\Entity\Author;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthorValidatorService
{
    public function __construct(ValidatorInterface $validator)
    {

        $this->response = new JsonResponse;
        $this->validator = $validator;
    }
    public function ValidateCreate($data): array
    {
        $author = new Author;
        $author->setName($data['name']);
        $author->setSurname($data['surname']);
        $author->setMiddlename($data['middlename']);
        $errors = $this->validator->validate($author);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->response->setData(['message' => $error->getMessage()]);
            }
            $this->response->setStatusCode(422);
            $res = false;
        } else {
            $res = true;
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


            //Проверка Имени
            $errors = $this->validator->validate($data['name'], [new Length(['min' => 0, 'max' => 128]), new Type('string', null, null, null, []), new  NotBlank()]);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->response->setData(['message' => '[name] ' . $error->getMessage()]);
                }
                $this->response->setStatusCode(422);
                $res = false;
            } else {
                //Проверка Отчества
                $errors = $this->validator->validate($data['middlename'], [new Length(['min' => 0, 'max' => 128]), new Type('string', null, null, null, []), new  NotBlank()]);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->response->setData(['message' =>  '[midlename] ' . $error->getMessage()]);
                    }
                    $this->response->setStatusCode(422);
                    $res = false;
                } else {
                    //Проверка Фамилии
                    $errors = $this->validator->validate($data['surname'], [new Length(['min' => 0, 'max' => 128]), new Type('string', null, null, null, []), new  NotBlank()]);
                    if (count($errors) > 0) {
                        foreach ($errors as $error) {
                            $this->response->setData(['message' =>  '[surname] ' . $error->getMessage()]);
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
            $errors = $this->validator->validate($request->get('author_id'), [new Length(['min' => 0, 'max' => 65535]), new Type('int',  null,  null, null, []), new  NotBlank()]);
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
