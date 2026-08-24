#!/usr/bin/env bash
# Lints every PHP file, then runs the behavioural test suites against WordPress stubs.
# No WordPress install required.
set -euo pipefail
cd "$(dirname "$0")/.."

echo "== Lint =="
find mu-plugins -name '*.php' -print0 | xargs -0 -n1 php -l

echo
echo "== Core suite =="
php tests/test-core.php

echo
echo "== WooCommerce suite =="
php tests/test-woocommerce.php

echo
echo "== Fonts + SEO suite =="
php tests/test-fonts-seo.php

echo
echo "== Contrast verification =="
python3 tests/verify-contrast.py

echo
echo "All checks passed."
