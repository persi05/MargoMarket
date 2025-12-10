FROM postgres:16

ENV POSTGRES_PASSWORD=docker
ENV POSTGRES_DB=margomarket
ENV POSTGRES_USER=docker

COPY ./sql/schema.sql /docker-entrypoint-initdb.d/01-schema.sql
COPY ./sql/seed.sql /docker-entrypoint-initdb.d/02-seed.sql