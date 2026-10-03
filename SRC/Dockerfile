FROM eclipse-temurin:17-jdk-jammy AS builder

RUN apt-get update \
    && apt-get install --yes --no-install-recommends ant \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /build
COPY . .
RUN ant clean jar

FROM eclipse-temurin:17-jre-jammy

RUN useradd --create-home --uid 10001 game
WORKDIR /app

COPY --from=builder /build/dist/ ./dist/
COPY --from=builder /build/data/ ./data/
COPY --from=builder /build/Config.properties /build/maintenanceConfig.txt ./

RUN mkdir -p /app/log \
    && chown -R game:game /app

USER game
EXPOSE 14445/tcp

ENTRYPOINT ["java", "-server", "-Dfile.encoding=UTF-8", "-jar", "dist/NgocRongOnline.jar"]
