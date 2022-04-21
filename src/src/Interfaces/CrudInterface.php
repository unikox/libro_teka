<?php

namespace App\Interfaces;

interface CrudInterface
{
    public function Create($data): array;
    public function Read($data): array;
    public function Update($data): array;
    public function Delete($data): array;
}
