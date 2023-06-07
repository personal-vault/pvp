# https://github.com/swagger-api/swagger-editor

docker run -d -p 80:8080 -v $(pwd):/tmp -e SWAGGER_FILE=/tmp/roadrunner/config/openapi.yaml swaggerapi/swagger-editor

sleep 2

open "http://localhost"
