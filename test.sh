#!/bin/bash

docker compose exec --workdir /var/www roadrunner /bin/bash -c "vendor/bin/phpunit"
