#! /bin/sh
#
# build.sh
# Copyright (C) 2015 Yannick Huerre <dev@sheoak.fr>
# Distributed under terms of the MIT license.

# documentation
phpdoc -d src -t docs/phpdoc --force

# code coverage and agile documentation
phpunit

