#!/bin/bash

source "$(realpath $(dirname $BASH_SOURCE))/version-extractor.sh"

check () {
    local version="$(version_from_readme)"
    [ "$version" = "$(version_from_push7_class)" ] &&
    [ "$version" = "$(version_from_main)" ]
}

check
