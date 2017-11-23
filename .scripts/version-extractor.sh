#!/bin/bash

version_from_readme () {
  grep 'Stable tag:' readme.txt | awk '{print $3}'
}

version_from_push7_class () {
  grep 'VERSION = ' classes/push7.php | awk '{print $4}' | cut -d\' -f 2
}

version_from_main () {
  grep 'Version: ' push7.php | awk '{print $2}'
}
