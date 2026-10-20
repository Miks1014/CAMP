FROM eclipse-temurin:17-jdk AS build
WORKDIR /app
COPY src ./src
RUN javac -d out src/ReportsServer.java

FROM eclipse-temurin:17-jre
WORKDIR /app
COPY --from=build /app/out ./out
EXPOSE 8081
CMD ["java", "-cp", "out", "ReportsServer"]
