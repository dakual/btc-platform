FROM ubuntu:20.04

RUN apt-get -y update && apt-get install -y --no-install-recommends \
         ca-certificates \
         build-essential \
         git \
         libleveldb-dev \
         python3 python3-dev python3-pip \
    && rm -rf /var/lib/apt/lists/*

USER root
WORKDIR /

# electrumx
RUN git clone https://github.com/spesmilo/electrumx.git
RUN cd electrumx && pip3 install .

COPY ./conf/electrumx/init /usr/local/bin
RUN chmod +x /usr/local/bin/init

VOLUME ["/data"]

ENV HOME /data
ENV ALLOW_ROOT true
ENV PEER_DISCOVERY off
ENV DB_DIRECTORY /data
ENV SERVICES=tcp://:50001,ssl://:50002,wss://:50004,rpc://0.0.0.0:8000
ENV SSL_CERTFILE ${DB_DIRECTORY}/electrumx.crt
ENV SSL_KEYFILE ${DB_DIRECTORY}/electrumx.key
ENV HOST ""

WORKDIR /data

EXPOSE 50001 50002 50004 8000

# start
CMD ["init"]