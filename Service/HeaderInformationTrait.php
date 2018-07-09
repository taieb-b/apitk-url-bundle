<?php
declare(strict_types=1);

namespace Ofeige\Rfc14Bundle\Service;

/**
 * Trait HeaderInformationTrait
 *
 * Header specific methods for the Rfc14Service.
 *
 * @package Ofeige\Rfc14Bundle\Service
 */
trait HeaderInformationTrait
{
    /**
     * @var string[]
     */
    private $headerInformation = [];

    /**
     * Register a value, which will be written in the response headers (key will be prefixed with 'x-rfc14-').
     *
     * @param string $key
     * @param mixed $value
     */
    public function addHeaderInformation(string $key, $value): void
    {
        $this->headerInformation[$key] = $value;
    }

    /**
     * Returns all currently registered header information for the response.
     *
     * @return mixed[]
     */
    public function getHeaderInformation(): array
    {
        return $this->headerInformation;
    }
}