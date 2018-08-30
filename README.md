# Keboola Dependencies
Script to create dependency tree of Keboola repositories for [GraphViz](http://www.webgraphviz.com)

## Usage
Run it via `docker-compose run dev`. Script store code into `.../out/keboola`.

Script create permanent cache to avoid using many http requests every time. Which is stored in `.../cache`.

There will be created 2 files:

- `keboola.json` - List of all public organization repositories
- `keboola-ignore.json` List of ignored repositories (repositories without `composer.json` file in it)

In case you want to get rid of this cache simply remove those 2 files.
