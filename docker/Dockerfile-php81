FROM alpine:edge

ADD entrypoint.sh /entrypoint.sh

RUN apk add --no-cache -X http://dl-cdn.alpinelinux.org/alpine/edge/testing php81 php81-gmp php81-opcache bash && \
    ln -sf /usr/bin/php81 /usr/bin/php && \
    touch /etc/php81/conf.d/01_jit.ini && \
    chmod 777 /etc/php81/conf.d/01_jit.ini && \
    chmod +x /entrypoint.sh && \
    chown -R nobody:nobody /entrypoint.sh

USER nobody

CMD /entrypoint.sh
VOLUME /code
