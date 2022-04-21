<?php

namespace App\Controller;

use App\Entity\Book;
use App\Service\BookService;
use App\Service\Validators\BookValidatorService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;

class ApiBookController extends BaseController
{
    /**
     * @OA\Post(
     * summary="Добавить новую книгу",
     * description="Данный метод позволяет добавить новую книгу",
     * tags={"Books"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Передать данные книги",
     *    @OA\JsonContent(
     *       required={"name", "author"},
     *       @OA\Property(property="name", type="string", format="name", example="Война и мир"),
     *       @OA\Property(property="author", type="integer", format="id", example="3"),
     *    ),
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Неправильные учетные данные",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Sorry, wrong Name. Please try again")
     *        )
     *     )
     * )
     *
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function Create(BookService $bookService, BookValidatorService $bookValidator): JsonResponse
    {
        $data = $this->getRequestJSON();
        $response = new JsonResponse();
        if (array_key_exists('name', $data) and array_key_exists('author', $data)) {
            //Валидация 
            $validate = $bookValidator->ValidateCreate($data);
            if ($validate['res']) {
                //Проверка на уникальность и запись в бд
                $create = $bookService->Create($data);
                if ($create['res']) {
                    $response->setStatusCode(200);
                    $response->setData(['message' => 'Success']);
                }
                return $create['response'];
            } else {
                return $validate['response'];
            }
        } else {
            $response->setStatusCode(422);
            $response->setData(['message' => 'Not enough parameters']);
        }
        return $response;
    }
    /**

     * @OA\Put(
     * summary="Изменить существующию книгу",
     * description="Данный метод позволяет изменить существующию книгу",
     * tags={"Books"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"id","name","author"},
     *       @OA\Property(property="id", type="integer", format="id", example="3"),
     *       @OA\Property(property="name", type="string", format="name", example="Капитанская дочка"),
     *       @OA\Property(property="author", type="integer", format="author_id", example="1"),
     *    ),
     * ),
     * @OA\Response(
     *    response=400,
     *    description="Error, The name is used",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Error, The name is used!!!")
     *        )
     *     ),
     * @OA\Response(
     *    response=404,
     *    description="Error,  The ID is not found",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Error, The ID is not found")
     *        )
     *     )
     * )
     */

    public function Update(BookService $bookService, BookValidatorService $bookValidator): JsonResponse
    {
        $data = $this->getRequestJSON();
        $response = new JsonResponse();
        if (array_key_exists('id', $data) and array_key_exists('name', $data) and array_key_exists('author', $data)) {
            //Валидация 
            $validate = $bookValidator->ValidateUpdate($data);
            if ($validate['res']) {
                //Проверка на уникальность и запись в бд
                $update = $bookService->Update($data);
                if ($update['res']) {
                    $response->setStatusCode(200);
                    $response->setData(['message' => 'Success']);
                }
                return $update['response'];
            } else {
                return $validate['response'];
            }
        } else {
            $response->setStatusCode(422);
            $response->setData(['message' => 'Not enough parameters']);
        }
        return $response;
    }


    /**
     * @OA\Get(
     * summary="Запросить книгу",
     * description="Данный метод позволяет запросить книгу",
     * tags={"Books"},
     * @OA\RequestBody(
     *    required=false,
     *    description="Запрос книги",
     *    ),
     * )
     *
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function Read(Request $request,  BookService $bookService, BookValidatorService $bookValidator): JsonResponse
    {
        $response = new JsonResponse();
        $validate = $bookValidator->ValidateRead($request);
        if ($validate['res']) {
            $read = $bookService->Read($request);
            if ($read['res']) {
                $response->setStatusCode(200);
                $response->setData($read['res']);
                return $response;
            } else {
                return $read['response'];
            }
        } else {
            return $validate['response'];
        }
    }

    /**
     * @OA\Delete(
     * summary="Удалить существующию книгу",
     * description="Данный метод позволяет удалить существующию книгу",
     * tags={"Books"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Передать id книги ",
     *    @OA\JsonContent(
     *       required={"id"},
     *       @OA\Property(property="id", type="integer", format="id", example="3"),
     *    ),
     * ),
     * @OA\Response(
     *    response=404,
     *    description="несуществующий id",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Error, The ID is not found!!!")
     *        )
     *     )
     * )
     *
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */

    public function Delete(BookService $bookService, BookValidatorService $bookValidator): JsonResponse

    {
        $response = new JsonResponse();
        $data = $this->getRequestJSON();
        if (array_key_exists('id', $data)) {
            //Валидация 

            $validate = $bookValidator->ValidateDelete($data);
            if ($validate['res']) {
                //Проверка на уникальность и запись в бд
                $delete = $bookService->Delete($data);
                if ($delete['res']) {
                    $response->setStatusCode(200);
                    $response->setData(['message' => 'Success']);
                }
                return $delete['response'];
            } else {
                return $validate['response'];
            }
        } else {
            $response->setStatusCode(422);
            $response->setData(['message' => 'Not enough parameters']);
        }
        return $response;
    }
    /**
     * @OA\Get(
     * summary="Искать книгу",
     * description="Данный метод позволяет найти книгу",
     * tags={"Books"},
     * @OA\RequestBody(
     *    required=false,
     *    description="Запрос книги",
     *    ),
     * )
     *
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function Search(Request $request,  BookService $bookService): JsonResponse
    {


        $response = new JsonResponse();
        $search = $bookService->Search($request);

        if ($search['res']) {
            $response->setStatusCode(200);
            $response->setData($search['response']);
            return $response;
        } else {
            return $search['response'];
        }
    }
}
