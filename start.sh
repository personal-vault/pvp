#!/bin/bash

export HOST_VAULT_PATH="$(pwd)/vault"

# Create the folder if it doesn't exist
mkdir -p "${HOST_VAULT_PATH}"
# chmod 777 "${HOST_VAULT_PATH}"

docker-compose up
