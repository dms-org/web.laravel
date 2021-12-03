#!/bin/bash

docker run --rm -v$PWD:/app node:8 sh -c "cd /app && ([ -d node_modules ] || npm ci) && ./node_modules/.bin/gulp --production"
