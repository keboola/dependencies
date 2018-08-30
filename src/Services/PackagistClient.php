<?php

namespace Keboola\Dependencies\Services;

use GuzzleHttp\Client;


class PackagistClient
{
    private const PACKAGIST_HOST = 'https://packagist.org/packages/list.json';
    private const PACKAGIST_REPOSITORY_HOST = 'https://repo.packagist.org/p/%s/%s.json';

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }


}
