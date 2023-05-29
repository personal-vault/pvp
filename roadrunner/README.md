# Road Runner

This is the central brain of the system. It has all the tools for scanning a system, extracting metadata and indexing.

Operations that it does:

- **scan**: iterates over the storage systems to determine which files are new/changed/moved/deleted;
- **parse**: generates data for the designated files, which can be extracting metadata, generating a thumbnail or any kind of processing that aids the indexing;
- **index**: adds all data to a full-text search index;
- **embed**: generates embeddings for files, useful to query;

## Scan

Start a complete scan:

```
docker compose exec --workdir /var/www roadrunner /bin/bash -c "php src/scan.php"
```
