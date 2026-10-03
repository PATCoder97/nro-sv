FROM mariadb:10.11

COPY ["database team2026.sql", "/docker-entrypoint-initdb.d/001-team2026.sql"]
