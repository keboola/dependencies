<?php

namespace Keboola\Dependencies\Services;

use GuzzleHttp\Client;


class GithubClient
{
    private const GITHUB_HOSTNAME = 'https://api.github.com';

    private $client;

    private $oAuthToken;

    public function __construct(Client $client, string $oAuthToken)
    {
        $this->client = $client;
        $this->oAuthToken = $oAuthToken;
    }

    public function getOrganizationRepositories(string $organization): array
    {
        $resultsPerPage = 100;
        $uri = sprintf(
            '%s/orgs/%s/repos?access_token=%s&page=%%s&per_page=%s',
            self::GITHUB_HOSTNAME,
            $organization,
            $this->oAuthToken,
            $resultsPerPage
        );

        $repositories = [];
        $page = 1;
        do {
            $response = $this->client->get(sprintf($uri, $page));
            $page++;
            $lastResult = json_decode($response->getBody());

            $repositories = array_merge($repositories, $lastResult);
        } while (!empty($lastResult));

        return $repositories;
    }

}
