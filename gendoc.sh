#!/bin/bash

# Requires jsdoc and phpdoc (which  in turn depends on php-pear).
# Install jsdoc 3.3: git clone https://github.com/jsdoc3/jsdoc.git
# Install php-pear: sudo apt-get install php-pear
# Install phpdoc: sudo pear channel-discover pear.phpdoc.org && sudo pear install phpdoc/phpDocumentor

current_dir=$PWD

cd $current_dir/jslibs
echo "Generating JS documentation ..."
jsdoc -d doc colordef.js cssutils.js genutils.js lazy.js legacy.js tracklib.js url.js

cd $current_dir/phplibs
echo "Generating PHP documentation ..."
phpdoc -t doc -d .

exit 0
