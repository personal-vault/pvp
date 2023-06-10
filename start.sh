#!/bin/bash

host="localhost"
port=7030
wait_time=10
counter=0

# Run `docker compose build --no-cache` to force a rebuild
docker compose up -d --build

while ! nc -z $host $port; do
  sleep 1
  counter=$((counter+1))
  if [ $counter -ge $wait_time ]; then
    echo "API port $port is not available after $wait_time seconds. Aborting."
    docker-compose down
    exit 1
  fi
done

echo "Port $port is available. Continuing."
sleep 3

source .env

./path-converter.sh $(realpath "$HOST_VAULT_PATH")

echo "Watching for changes in $HOST_VAULT_PATH"

fswatch -0 --event Created --event Updated --event Renamed --event Removed $HOST_VAULT_PATH | xargs -0 -n 1 -I {} ./path-converter.sh {}

docker compose down
