#!/bin/bash

cd "$(dirname "$0")"

for DOCKERFILE in `ls docker/Dockerfile-*`; do
	VERSION="${DOCKERFILE#*-}"
	printf "%12s: \n" "${VERSION}"
	# TODO: This doesn't check if it passes or fails, just how long it runs for...
	./docker.sh --${VERSION} test "${@}" | while read IN; do
		printf "%12s  %s\n" "" "${IN}"
	done;
done;
