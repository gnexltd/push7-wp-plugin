#!/bin/bash

syntax () {
    find . -name "*.php" -exec php -l {} \; 1>/dev/null
}

result="$(syntax 2>&1)"

if [ "$result" ]
then
    echo $result >&2
    exit 1
fi
