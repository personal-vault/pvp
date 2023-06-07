#!/bin/bash

# Load .env variables
source .env

# Convert the host path to an absolute path
ABSOLUTE_HOST_VAULT_PATH=$(realpath $HOST_VAULT_PATH)

# This will strip the leading part of the file path
DOCKER_PATH=${1//$ABSOLUTE_HOST_VAULT_PATH/$VAULT_PATH}

# Call our docker API with the modified path
curl "http://localhost:7030/scan" -X POST -d "path=$DOCKER_PATH"
