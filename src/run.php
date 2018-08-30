<?php


require __DIR__ . '/../vendor/autoload.php';

$githubRawFileUrl = 'https://raw.githubusercontent.com';

$client = new \GuzzleHttp\Client();

$githubOAuthToken = '';
$githubClient = new \Keboola\Dependencies\Services\GithubClient($client, $githubOAuthToken);


$githubCacheDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache';

$organizationName = 'keboola';
$organizationRepositoriesCacheFile = $githubCacheDir . DIRECTORY_SEPARATOR . $organizationName . '.json';
$organizationIgnoredRepositoriesCacheFile = $githubCacheDir . DIRECTORY_SEPARATOR . $organizationName . '-ignore.json';

if (file_exists($organizationRepositoriesCacheFile)) {
    $repos = json_decode(file_get_contents($organizationRepositoriesCacheFile));
} else {
    $repos = $githubClient->getOrganizationRepositories($organizationName);
    file_put_contents($organizationRepositoriesCacheFile, json_encode($repos, JSON_PRETTY_PRINT));
}


$requiredProjects = [
    'keboola/ex-teradata',
    'keboola/wr-slack',
    'keboola/http-extractor',
];

$counter = 0;
$projects = [];
$projectsWithoutComposerFile = [];

foreach ($repos as $repo) {
    ++$counter;
    $repositoryName = $repo->full_name;
/*
    if (!in_array($repositoryName, $requiredProjects)) {
        continue;
    }
*/
    if (file_exists($organizationIgnoredRepositoriesCacheFile)) {
        $ignoredFiles = json_decode(file_get_contents($organizationIgnoredRepositoriesCacheFile), true);
    } else {
        $ignoredFiles = [];
    }

    if (in_array($repositoryName, $ignoredFiles)) {
        print (sprintf('Repository \'%s\' is ignored.' . PHP_EOL, $repositoryName));
        continue;
    }

    try {
        // https://raw.githubusercontent.com/keboola/gooddata-writer-v3/master/composer.json
        $result = $client->get(sprintf('%s/%s/master/composer.json', $githubRawFileUrl, $repositoryName));
    } catch (\GuzzleHttp\Exception\ClientException $exception) {
        if ($exception->getCode() === 404) {
            print (sprintf('Repository \'%s\' does not contain composer.json' . PHP_EOL, $repositoryName));
            $projectsWithoutComposerFile[] = $repositoryName;
            continue;
        }
        throw $exception;
    }

    $composerJson = json_decode($result->getBody()->getContents(), true);

    if (!isset($composerJson['require'])
        || !isset($composerJson['require-dev'])
    ) {
        $requirements = [];
    }

    $require = $composerJson['require'] ?? [];
    $requireDev = $composerJson['require-dev'] ?? [];
    $requirements = array_merge($require, $requireDev);

    $projects[$repositoryName] = $requirements;
}

if (!file_exists($organizationIgnoredRepositoriesCacheFile)) {
    file_put_contents($organizationIgnoredRepositoriesCacheFile, json_encode($projectsWithoutComposerFile, JSON_PRETTY_PRINT));
}

/*
    digraph G {
        "To" -> "GraphViz!"
        "A" -> "To"
        "Welcome" -> "To"
    }
 */

$graphVizLines = [];
foreach ($projects as $repositoryName => $dependencies) {
    foreach ($dependencies as $dependency => $version) {
        $graphVizLines[] = sprintf('"%s" -> "%s"', $dependency, $repositoryName);
    }
}

$graphVizCode = "digraph G {" . PHP_EOL
    . '  graph [pad="0.8", nodesep="1.5", ranksep="5"];' . PHP_EOL
    . '  node [fontsize=10,width=".2", height=".5", margin=.5, fontsize=36];' . PHP_EOL . '  '
    . implode("\n  ", $graphVizLines) . PHP_EOL
    . "}" . PHP_EOL;

$graphVizCodeFilePath = __DIR__ . '/../out/graphViz';
file_put_contents($graphVizCodeFilePath, $graphVizCode);

exit;
// var_dump($projects);

var_dump(array_keys($projects));

var_dump(count($projects));

var_dump($counter);


