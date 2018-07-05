<?php
declare(strict_types=1);

namespace Ofeige\Rfc14Bundle\Service;


trait HeaderInformationTrait
{
    /**
     * @var array
     */
    private $headerInformation = [];

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addHeaderInformation(string $key, $value): void
    {
        $this->headerInformation[$key] = $value;
    }

    /**
     * @return mixed[]
     */
    public function getHeaderInformation(): array
    {
        return $this->headerInformation;
    }
}