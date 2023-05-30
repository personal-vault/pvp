# Use an official PostgreSQL runtime as a parent image
FROM postgres:15-alpine3.18

# Set the working directory in the container to /docker-entrypoint-initdb.d
# This is the directory that Postgres runs scripts from during the container startup
WORKDIR /docker-entrypoint-initdb.d

# Copy the SQL script into the container
# Your script must be in the same directory as your Dockerfile
COPY ./init.sql ./init.sql

# Copy the Postgres configuration file into the container
COPY ./postgres.conf /etc/postgresql/postgresql.conf

# Make sure the script is executable
RUN chmod +x ./init.sql

# When the container starts, this script will be run
# followed by anything in /docker-entrypoint-initdb.d
CMD ["postgres"]
