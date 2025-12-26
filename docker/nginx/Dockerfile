FROM nginx:1.25-alpine

COPY . /app/

COPY ./docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

COPY ./docker/nginx/ssl /etc/nginx/ssl

RUN chmod 600 /etc/nginx/ssl/nginx-selfsigned.key && \
    chmod 644 /etc/nginx/ssl/nginx-selfsigned.crt