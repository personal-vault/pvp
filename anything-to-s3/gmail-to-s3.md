---
title: Gmail to S3
layout: default
parent: Anything to S3
nav_order: 10
---
# Gmail to S3

Historical and real-time sync of Gmail messages into an S3 bucket.

Basic usage:

```bash
GOOGLE_APPLICATION_CREDENTIALS=./credentials.json \
S3_ENDPOINT=http://127.0.0.1:9000/ \
php ./src/gmail-to-s3.php
```

The `credentials.json` file can be downloaded from Google API Console, see [Authorize credentials for a web application](https://developers.google.com/gmail/api/quickstart/js#authorize_credentials_for_a_web_application) on how to do that.

This assumes that the S3 access key and secret are stored in `~/.aws/credentials` and a config file specifying `region` exists in `~/.aws/config`. See [AWS Configuration and credential file settings](https://docs.aws.amazon.com/cli/latest/userguide/cli-configure-files.html) for details of how these file look like.

Alternatively, S3 credentials and configuration files can be specified when running the script:

```bash
GOOGLE_APPLICATION_CREDENTIALS=./credentials.json \
GOOGLE_TOKEN_PATH=./token.json \
AWS_CREDENTIALS=./s3_credentials \
AWS_CONFIG=./s3_config \
S3_ENDPOINT=http://127.0.0.1:9000/ \
php ./src/gmail-to-s3.php
```

Google tokens are saved locally in case a different path is not specified. Send a full path + filename in `GOOGLE_TOKEN_PATH` to override:

```bash
GOOGLE_APPLICATION_CREDENTIALS=./credentials.json \
GOOGLE_TOKEN_PATH=/tmp/token.json \
S3_ENDPOINT=http://127.0.0.1:9000/ \
php ./src/gmail-to-s3.php
```
