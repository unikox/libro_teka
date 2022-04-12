<?php

namespace App\Controller;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class BaseController extends AbstractController
{

    protected ?Request $request;

    public function __construct(RequestStack $request)
    {
        $this->request = $request->getCurrentRequest();
    }

    protected function useForm(string $type, $entity, array $data, array $options = [])
    {
        $form = $this->createForm($type, $entity, $options);
        $form->submit($data, false);
        return $form->getData();
    }

    protected function getRequestJSON(): ?array
    {
        try {
            return json_decode($this->request?->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BadRequestException("Request content is not valid JSON");
        }
    }



    protected function makeSerializer(): ?Serializer
    {
        $builder = SerializerBuilder::create();
        $serializer = $builder->build();
        return ($serializer instanceof Serializer) ? $serializer : null;
    }

    protected function getRequestQueryParams(): ?array
    {
        return $this->request->query->all();
    }



    //TODO refactor
    protected function makeJson($data, int $status = 200, array $headers = [], $groups = ['Default']): JsonResponse
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);
        $context->setGroups($groups);
        return new JsonResponse($this->makeSerializer()->serialize($data, 'json', $context), $status, $headers, true);
    }
}
