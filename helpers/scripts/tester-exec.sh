#!/bin/bash

echo "### extract"
/repo/helpers/scripts/tester-extract.sh || exit 100

echo "### init"
/repo/helpers/scripts/tester-init.sh || exit 101

echo "### run"
/repo/helpers/scripts/tester-run.sh || exit 102

echo "### video"
/repo/helpers/scripts/tester-video.sh || exit 103
