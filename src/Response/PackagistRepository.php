<?php

class PackagistRepository
{
    private $name;

    private $githubLink;

    private $versions;

    public function __construct(string $name, string $githubLink, array $versions)
    {
        $this->name = $name;
        $this->githubLink = $githubLink;
        $this->versions = $versions;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGithubLink(): string
    {
        return $this->githubLink;
    }

    public function getVersions(): array
    {
        return $this->versions;
    }
}
