FROM eclipse-temurin:17-jdk-jammy AS builder

RUN apt-get update \
    && apt-get install --yes --no-install-recommends ant ca-certificates curl \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /build
COPY . .
RUN test -f data/map/tile_set_info
ARG MARIADB_CONNECTOR_VERSION=3.5.10
ARG MARIADB_CONNECTOR_SHA256=919b8c1c771d9ee3465811462f242c9543ab401e140c64988ddbf1d8abcb18b2
RUN curl --fail --silent --show-error --location \
        "https://repo.maven.apache.org/maven2/org/mariadb/jdbc/mariadb-java-client/${MARIADB_CONNECTOR_VERSION}/mariadb-java-client-${MARIADB_CONNECTOR_VERSION}.jar" \
        --output "lib/mariadb-java-client-${MARIADB_CONNECTOR_VERSION}.jar" \
    && echo "${MARIADB_CONNECTOR_SHA256}  lib/mariadb-java-client-${MARIADB_CONNECTOR_VERSION}.jar" | sha256sum --check --strict \
    && rm -f lib/mysql-connector-java*.jar
RUN ant clean jar

FROM eclipse-temurin:17-jre-jammy

RUN useradd --create-home --uid 10001 game
WORKDIR /app

COPY --from=builder /build/dist/NgocRongOnline.jar ./app.jar
COPY --from=builder /build/lib/ ./lib/
COPY --from=builder /build/data/ ./data/
COPY --from=builder /build/Config.properties /build/maintenanceConfig.txt ./

RUN mkdir -p /app/log \
    && chown -R game:game /app

USER game
EXPOSE 14445/tcp

ENTRYPOINT ["java", "-server", "-Dfile.encoding=UTF-8", "-cp", "app.jar:lib/*", "nro.models.server.ServerManager"]
