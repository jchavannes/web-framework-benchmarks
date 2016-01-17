#!/bin/bash

ROOT_DIRECTORY=$(dirname "`readlink -f "$0"`")

INTERACTIVE=false
STOP_MODE=false
FRAMEWORK=""
VERBOSE_MODE=false

FOUND_IMAGE=false

function cmd {

    if ! ${VERBOSE_MODE}; then
      $@ &>/dev/null
    else
      $@
    fi

}

function run {

    IMAGE_NAME="${1}"
    PORT="${2}"
    DIRECTORY="${3}"

    cmd echo -e "Using directory: ${DIRECTORY}"

    CURRENT=`sudo docker ps | grep " ${IMAGE_NAME}:" | awk '{print $1}'`

    if [ ! -z "${CURRENT}" ]; then
      echo "Stopping ${IMAGE_NAME} (${CURRENT})..."
      cmd sudo docker stop -t=2 ${CURRENT}
    fi

    if ${STOP_MODE}; then
      return
    fi

    echo "Building ${IMAGE_NAME}..."
    cmd sudo docker build -t ${IMAGE_NAME} ${DIRECTORY}/

    if [ "$?" == "0" ]; then
      cmd echo -e "Image successfully built: ${IMAGE_NAME}"
    else
      echo -e "Error building image"
      exit 1
    fi

    cmd echo "Running ${IMAGE_NAME}..."
    if ${INTERACTIVE}; then
      sudo docker run -v ${DIRECTORY}/www:/var/www -p ${PORT}:80 -i -t ${IMAGE_NAME} /bin/bash
    else
      cmd sudo docker run -v ${DIRECTORY}/www:/var/www -p ${PORT}:80 -d ${IMAGE_NAME}
    fi

    if [ "$?" == "0" ]; then
      echo -e "${IMAGE_NAME} running successfully at: http://localhost:${PORT}"
    else
      echo -e "Error running container"
      exit 1
    fi

}

while [[ $# > 0 ]]; do
  key="$1"

  case ${key} in
    -i|--interactive)
      echo "== Interactive mode =="
      INTERACTIVE=true
    ;;
    -v|--verbose)
      echo "== Verbose mode =="
      VERBOSE_MODE=true
    ;;
    -s|--stop)
      echo "== Stop only =="
      STOP_MODE=true
    ;;
    -f|--framework)
      FRAMEWORK="$2"
      shift
    ;;
    *) # unknown option
    ;;
  esac
  shift
done

if ! type "docker" &>/dev/null; then
  echo "Docker must be installed."
  exit 1
fi

if ${INTERACTIVE} && [ -z ${FRAMEWORK} ]; then
  echo "Cannot do interactive mode without specifying a framework (-f)."
  exit 1
fi

if [ -z "${FRAMEWORK}" ] || [ "${FRAMEWORK}" = "php5-raw" ]; then
  FOUND_IMAGE=true
  run 'php5-raw' 8091 "${ROOT_DIRECTORY}/php/raw"
fi
if [ -z "${FRAMEWORK}" ] || [ "${FRAMEWORK}" = "php5-lumen" ]; then
  FOUND_IMAGE=true
  run 'php5-lumen' 8092 "${ROOT_DIRECTORY}/php/lumen"
fi
if [ -z "${FRAMEWORK}" ] || [ "${FRAMEWORK}" = "php5-zend" ]; then
  FOUND_IMAGE=true
  run 'php5-zend' 8093 "${ROOT_DIRECTORY}/php/zend"
fi
if [ -z "${FRAMEWORK}" ] || [ "${FRAMEWORK}" = "nodejs-express" ]; then
  FOUND_IMAGE=true
  run 'nodejs-express' 8094 "${ROOT_DIRECTORY}/nodejs/express"
fi
if [ -z "${FRAMEWORK}" ] || [ "${FRAMEWORK}" = "nodejs-restify" ]; then
  FOUND_IMAGE=true
  run 'nodejs-restify' 8095 "${ROOT_DIRECTORY}/nodejs/restify"
fi
if [ -z "${FRAMEWORK}" ] || [ "${FRAMEWORK}" = "nodejs-raw" ]; then
  FOUND_IMAGE=true
  run 'nodejs-raw' 8096 "${ROOT_DIRECTORY}/nodejs/raw"
fi

if ! ${FOUND_IMAGE}; then
  echo "Unable to find image."
  exit 1
fi
