#!/bin/bash

set -e

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi

# CHECK MASTER BRANCH
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [[ "$CURRENT_BRANCH" != "master" ]]; then
  echo "You have to be on master branch currently on $CURRENT_BRANCH . Aborting"
  exit 65
fi

# CHECK FORMAT OF THE TAG
php -r "if(preg_match('/^\d+\.\d+\.\d+(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?\$/',\$argv[1])) exit(0) ;else{ echo 'format of version tag is not invalid' . PHP_EOL ; exit(1);}" $1

# CHECK jsawk COMMAND
command -v jsawk >/dev/null 2>&1 || { echo "Error : Command jsawk is not installed on the system"; echo "See : https://github.com/micha/jsawk "; echo  "Exiting..." >&2; exit 65; }

# CHECK js COMMAND
command -v js >/dev/null 2>&1 || { echo "Error : Command js is not installed on the system"; echo "Should be fixed by installing spidermonkey "; echo  "Exiting..." >&2; exit 65; }

# CHECK box COMMAND
command -v box >/dev/null 2>&1 || { echo "Error : Command box is not installed on the system"; echo "See : https://github.com/box-project/box2 "; echo  "Exiting..." >&2; exit 65; }

# CHECK python COMMAND
command -v python >/dev/null 2>&1 || { echo "Error : Command python is not installed on the system"; echo  "Exiting..." >&2; exit 65; }

# CHECK THAT WE CAN CHANGE BRANCH
git checkout gh-pages
git checkout --quiet master

# Write the tag to the version file, then tag it
TAG=$1
echo ${TAG} > VERSION
git add VERSION
git commit -m "Bump version ${TAG}"
git tag ${TAG}

# Now build the .phar
box build
rm -rf /tmp/inplace
mkdir -p /tmp/inplace
mv inplace.phar /tmp/inplace
cp inplace.phar.pubkey /tmp/inplace

# Copy executable file into GH pages
git checkout --quiet gh-pages
mkdir -p releases
cp /tmp/inplace/inplace.phar releases/
cp /tmp/inplace/inplace.phar.pubkey releases/
rm -rf /tmp/inplace
git add .

SHA1=$(openssl sha1 releases/inplace.phar)

JSON='name:"inplace.phar"'
JSON="${JSON},sha1:\"${SHA1}\""
JSON="${JSON},url:\"https://ssx.github.io/inplace/releases/inplace.phar\""
JSON="${JSON},version:\"${TAG}\""

if [ -f inplace.phar.pubkey ]; then
    git add inplace.phar.pubkey
    JSON="${JSON},publicKey:\"https://ssx.github.io/inplace/release/inplace.phar.pubkey\""
fi

# Update manifest
cat manifest.json | jsawk -a "this.push({${JSON}})" | python -mjson.tool > manifest.json.tmp
mv manifest.json.tmp manifest.json
git add manifest.json
git commit --quiet -m "Bump version ${TAG}"

# Go back to master
git checkout --quiet master

echo "New version created. Pushing..."
git push --quiet origin gh-pages
git push --quiet --tags && git push
