<?php

namespace Ofeige\Rfc14Bundle\Service;

interface HeaderInformation
{
    public function addHeaderInformation(string $key, $value): void;

    public function getHeaderInformation(): array;
}