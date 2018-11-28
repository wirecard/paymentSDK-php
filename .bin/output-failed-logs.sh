#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

for f in tests/_output/*.fail.html; do
echo "----------------------<$f>----------------------";
cat "$f"; done