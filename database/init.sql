CREATE TABLE files (
    id SERIAL PRIMARY KEY,
    hash VARCHAR(64) NOT NULL,
    path TEXT UNIQUE NOT NULL,
    filename TEXT,
    filesize BIGINT,
    mime TEXT,
    date_created TIMESTAMP,
    gps_lat REAL,
    gps_lon REAL,
    gps_alt REAL,
    scan_version INTEGER,
    scanned_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    removed_at TIMESTAMP
);
