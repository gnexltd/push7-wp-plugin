#!/bin/bash

rm -rf .git
cd ../
svn checkout https://plugins.svn.wordpress.org/push7/
cd push7/
svn remove trunk
cp -r ../push7-wp-plugin ./trunk
svn add trunk
svn commit -m "release for $TRAVIS_COMMIT" --username $WP_USER --password $WP_PASSWORD
