# Pinba MySQL image is maintained in the dedicated pinba_engine project.
# This wrapper keeps compatibility for local `docker build` workflows.
ARG PINBA_IMAGE=xolegator/pinba-engine:8.0
FROM ${PINBA_IMAGE}

COPY ./docker/mysql-init/20-create-pinboard-user.sh /docker-entrypoint-initdb.d/20-create-pinboard-user.sh
RUN chmod +x /docker-entrypoint-initdb.d/20-create-pinboard-user.sh
