PU=./vendor/bin/phpunit
PHPCS=./vendor/bin/phpcs
PHPCBF=./vendor/bin/phpcbf
PHPMD=./vendor/bin/phpmd

help:
	@echo "Use \`make <target>' where <target> is one of"
	@echo "  docs 			build the documentation in build/api/"
	@echo "  test 			run unit tests"
	@echo "  testdox 		gerenate testdox report in reports/"
	@echo "  show-testdox		run unit tests in testdox format"
	@echo "  coverage 		generate code coverage report"
	@echo "  show-coverage 	show code coverage report"
	@echo "  phpcs			check code quality with PHP_CodeSniffer"
	@echo "  phpcbf		fix PHP Code with PHP_CodeSniffer"
	@echo "  phpmd			check for code mess"
	@echo "  changelog		generate CHANGELOG.md"

docs:
	apigen generate --source src --destination build/api

test:
	$(PU)

show-testdox:
	$(PU) --testdox

testdox:
	$(PU) --testdox-text reports/agile-doc.txt

coverage:
	$(PU) --coverage-html reports/coverage

show-coverage:
	$(PU) --coverage-text

phpcs:
	$(PHPCS) --standard=ruleset.xml -v

phpcbf:
	$(PHPCBF) --standard=ruleset.xml -v

phpmd:
	$(PHPMD) src/   text ruleset.phpmd.xml

changelog:
	$(CHGLOG) github_changelog_generator
