PU=./vendor/bin/phpunit
PHPCS=./vendor/bin/phpcs
PHPCBF=./vendor/bin/phpcbf
PHPMD=./vendor/bin/phpmd

help:
	@echo "Use \`make <target>' where <target> is one of"
	@echo "  doc 			build the documentation in build/api/"
	@echo "  test 			run unit tests"
	@echo "  testdox 		gerenate testdox report in build/"
	@echo "  show-testdox		run unit tests in testdox format"
	@echo "  coverage 		generate code coverage report"
	@echo "  show-coverage 	show code coverage report"
	@echo "  phpcs			check code quality with PHP_CodeSniffer"
	@echo "  phpcbf		fix PHP Code with PHP_CodeSniffer"
	@echo "  phpmd			check for code mess"
	@echo "  changelog		generate CHANGELOG.md"
	@echo "  release VERSION=x.x.x	create a new release, running changelog and git bump"
	@echo "  clean			delete build/ folder content"

doc:
	apigen generate --source src --destination build/api

test:
	$(PU)

show-testdox:
	$(PU) --testdox

testdox:
	$(PU) --testdox-text build/agile-doc.txt

coverage:
	$(PU) --coverage-html build/coverage

show-coverage:
	$(PU) --coverage-text

phpcs:
	$(PHPCS) --standard=ruleset.xml -v

phpcbf:
	$(PHPCBF) --standard=ruleset.xml -v

phpmd:
	$(PHPMD) src/   text ruleset.phpmd.xml

bump: changelog
	git bump

bump-minor: changelog
	git bump minor

bump-major: changelog
	git bump major

clean:
	rm -rf build/*
