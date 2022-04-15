<?php

namespace App\Controller;

use App\Entity\Author;
use App\Service\AuthorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;



class ApiAuthorController extends BaseController
{
    /**
     * @OA\Post(
     * summary="Добавить нового автора",
     * description="Данный метод позволяет добавить нового автора",
     * tags={"Author"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Передать Данные автора",
     *    @OA\JsonContent(
     *       required={"name", "middlename","surname"},
     *       @OA\Property(property="name", type="string", format="name", example="Лев"),
     *       @OA\Property(property="middlename", type="string", format="middlename", example="Николаевич"),
     *       @OA\Property(property="surname", type="string", format="surname", example="Толстой"),
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

    public function Create(EntityManagerInterface $em, AuthorService $authorService): JsonResponse
    {
        $data = $this->getRequestJSON();
        $response = new JsonResponse();
        if (array_key_exists('name', $data) and array_key_exists('name', $data) and array_key_exists('middlename', $data) and array_key_exists('surname', $data)) {
            $authorService->setOperation('create');
            //Валидация 
            $validate = $authorService->Validate($response, $data);
            if ($validate['res']) {
                unset($validate);
                //Проверка на уникальность и запись в бд
                $create = $authorService->Create($em, $response, $data);
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
     * summary="Изменить существующего автора",
     * description="Данный метод позволяет изменить существующего автора",
     * tags={"Author"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Изменить автора",
     *    @OA\JsonContent(
     *       required={"id","name"},
     *       @OA\Property(property="id", type="integer", format="id", example="1"),
     *       @OA\Property(property="name", type="string", format="name", example="Александр"),
     *       @OA\Property(property="middlename", type="string", format="middlename", example="Сергеевич"),
     *       @OA\Property(property="surname", type="string", format="surname", example="Пушкин"),
     *    ),
     * ),
     * @OA\Response(
     *    response=400,
     *    description="Error, The FIO is used",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Error, The FIO is used!!!")
     *        )
     *     ),
     * @OA\Response(
     *    response=404,
     *    description="Error, The FIO is not found",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Error, The ID is not found")
     *        )
     *     )
     * )
     */

    public function Update(EntityManagerInterface $em, Request $request, AuthorService $authorService): JsonResponse
    {
        $data = $this->getRequestJSON();
        $response = new JsonResponse();
        //Валидация 
        if (array_key_exists('id', $data) and array_key_exists('name', $data) and array_key_exists('name', $data) and array_key_exists('middlename', $data) and array_key_exists('surname', $data)) {
            $authorService->setOperation('update');
            $validate = $authorService->Validate($response, $data);
            if ($validate['res']) {
                //Проверка на уникальность и запись в бд
                $update = $authorService->Update($em, $response, $data);
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
     * summary="Запросить автора",
     * description="получить сведения об авторе",
     * tags={"Author"},
     * )
     *
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     */
    public function Read(Request $request, EntityManagerInterface $em, AuthorService $authorService): JsonResponse
    {

        $authorService->setOperation('read');
        $response = new JsonResponse();
        $validate = $authorService->Validate($response, $request);
        if ($validate['res']) {
            $read = $authorService->Read($em, $response, $request);

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
     * summary="Удалить существующего автора",
     * description="Данный метод позволяет удалить существующего автора",
     * tags={"Author"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Передать id автора ",
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

    public function Delete(EntityManagerInterface $em, AuthorService $authorService): JsonResponse

    {
        $response = new JsonResponse();
        $data = $this->getRequestJSON();
        if (array_key_exists('id', $data)) {
            //Валидация 
            $authorService->setOperation('delete');
            $validate = $authorService->Validate($response, $data);
            if ($validate['res']) {
                $delete = $authorService->Delete($em, $response, $data);
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
}
