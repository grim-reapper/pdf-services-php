<?php

declare(strict_types=1);

namespace GrimReapper\Contracts;

use GrimReapper\PdfServices\Config\PdfServicesConfig;

/**
 * Interface for all PDF Services
 */
interface ServiceInterface
{
    /**
     * Get the service configuration
     *
     * @return PdfServicesConfig
     */
    public function getConfig(): PdfServicesConfig;

    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string;
}
