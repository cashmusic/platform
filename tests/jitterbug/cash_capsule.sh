#!/bin/bash

# first arg:  build_dir
# second arg: report path
# third arg: should we use perlbrew?

# this is getting smelly
builddir=$1
report_path=$2
perlbrew=$3

function jitterbug_build () {
    if [ -f 'tests/php/all.php' ]; then
        echo "Found PHP tests"
        php tests/php/all.php &> $logfile
    fi
}

echo "Creating report_path=$report_path"
mkdir -p $report_path

cd $builddir

thephp=$(php -v|grep ^PHP|cut -d' ' -f2)
logfile="$report_path/php-$thephp.txt"

mkdir -p $report_path
echo "touching $logfile"
touch $logfile

jitterbug_build
