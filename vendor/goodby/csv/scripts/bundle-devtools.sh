#/usr/bin/env bash

function print_help {
    echo "$(basename $0) [path/to/dir/to/install]"
}

set -eux

if [ $# -eq 0 ]
then
    print_help
    exit 1
fi

INSTALL_DIR=$1

if [ ! -d "$INSTALL_DIR" ]
then
    echo "No such directory: $INSTALL_DIR"
    exit 1
fi

cd $INSTALL_DIR

if [ -f composer.phar ]
then
    rm -f composer.phar
fi

COMPOSER="$INSTALL_DIR/composer.phar"

wget http://getcomposer.org/composer.phar

chmod +x composer.phar

./composer.phar install --dev
