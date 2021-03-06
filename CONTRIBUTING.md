# How to contribute

## Testing

    cd /path/to/project
    # run all tests
    make test
    # generate code coverage
    make coverage
    # show code coverage
    make show-coverage
    # generate agile documentation
    make testdox
    # show agile documentation
    make show-testdox

or if you don't want to use make, and run with default options:

    phpunit


## Documentation

    cd /path/to/project
    # generate documentation from phpdoc comments with apigen
    make doc


## Submitting Changes

Before submitting your changes, make sure to:

* Add the necessary tests.
* Run _all_ the tests to assure nothing else was accidentally broken.
- Add the proper phpdoc comments to your code.
- Check the code coverage and _CRAP_ score.

It is highly recommended to use the git hooks in hooks/ directory to run the
test before each commit.

Be sure to follow [this
convention](https://github.com/erlang/otp/wiki/Writing-good-commit-messages)
for your commit messages.


# Additional Resources

* [Travis CI](https://travis-ci.org/)
* [Guzzle 6](http://docs.guzzlephp.org/en/latest/quickstart.html)
* [PHPUnit](https://phpunit.de/)
- [github_changelog_generator](https://github.com/skywinder/github-changelog-generator)
