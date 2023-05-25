FROM ghcr.io/roadrunner-server/roadrunner:2023.1.3 AS roadrunner
FROM php:8-fpm

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
# Install and configure your application
# ...

CMD rr serve -c /etc/roadrunner/.rr.yaml --dotenv /etc/roadrunner/.env
