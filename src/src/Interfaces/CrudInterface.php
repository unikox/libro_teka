<?php

namespace App\Interfaces;

interface CrudInterface
{
    public function Create($em, $response, $data): array;
    public function Read($em, $response, $data): array;
    public function Update($em, $response, $data): array;
    public function Delete($em, $response, $data): array;
    public function Validate($response, $data): array;
    public function setOperation($operation);
}
