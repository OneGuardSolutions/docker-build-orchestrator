#!/usr/bin/env sh

composer install --no-dev --no-interaction \
    || { echo "ERROR: failed to install dependencies" >&2; exit 1; }
box compile -v
box info dobr.phar
./dobr.phar --version

rm gh-pages/ -rf
git clone https://github.com/OneGuardSolutions/docker-build-orchestrator.git gh-pages -b gh-pages

SUM="$(sha1sum dobr.phar | cut -d ' ' -f 1)"
VERSION="$(./dobr.phar --version | cut -d ' ' -f 2)"
BASE_URL="https://oneguardsolutions.github.io/docker-build-orchestrator/releases/$VERSION"
php "$(dirname "$0")/add-to-manifest.php" dobr.phar "$BASE_URL/dobr.phar" "$BASE_URL/dobr.phar.pubkey" "$SUM" "$VERSION" gh-pages/manifest.json
mkdir -p "gh-pages/releases/$VERSION/"
cp dobr.phar "gh-pages/releases/$VERSION/dobr.phar"
cp dobr.phar.pubkey "gh-pages/releases/$VERSION/dobr.phar.pubkey"
