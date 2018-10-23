<?php

namespace CrCms\Foundation\Client\Http\Contracts;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseContract
 * @package CrCms\Foundation\Client\Contracts
 */
interface ResponseContract
{
    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;
}