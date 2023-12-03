#!/bin/bash

cd "$(dirname "$0")"

BASEIMAGE=shanemcc/aoc-2023-03
BASEDOCKERFILE="Dockerfile"

IMAGE=${BASEIMAGE}
DOCKERFILE=${BASEDOCKERFILE}
FORCEBUILD="0";
SHELL="0";

while true; do
	case "$1" in
		--build)
			FORCEBUILD="1";
			;;
		--shell)
			SHELL="1";
			;;
		*)
			export VARIANT=$(echo "${1}" | sed 's/^--//' | sed 's/\///g')
			if [ -e "docker/${BASEDOCKERFILE}-${VARIANT}" ]; then
				IMAGE=${BASEIMAGE}-${VARIANT}
				DOCKERFILE=${BASEDOCKERFILE}-${VARIANT}
			else
				break;
			fi;
			;;
	esac
	shift
done

docker image inspect $IMAGE >/dev/null 2>&1
if [ $? -ne 0 -o ${FORCEBUILD} = "1" ]; then
	echo "One time setup: building docker image ${IMAGE}..."
	cd docker
	docker pull alpine:edge
	docker build . -t $IMAGE --file ${DOCKERFILE}
	cd ..
	echo "Image build complete."
fi

if [ "${SHELL}" = "1" ]; then
	docker run --rm -it -v $(pwd):/code $IMAGE bash
else
	docker run --rm -it -v $(pwd):/code $IMAGE /entrypoint.sh $@
fi;
