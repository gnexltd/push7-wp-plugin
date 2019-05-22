#!/bin/bash

source .scripts/version-extractor.sh

check () {
    local version="$(version_from_readme)"
    [ "$version" = "$(version_from_push7_class)" ] &&
    [ "$version" = "$(version_from_main)" ]
}

check
status=$?

if [ $status -ne 0 ]
then
    cat >&2 <<MESSAGE
バージョンが統一されていません
    readme.txt: $(version_from_readme)
    push7.php: $(version_from_main)
    classes/push7.php: $(version_from_push7_class)
MESSAGE
fi

exit $status
