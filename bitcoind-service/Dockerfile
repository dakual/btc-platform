FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive 

RUN apt-get -y update && apt-get install -y --no-install-recommends \
         wget \
         ca-certificates \
    && rm -rf /var/lib/apt/lists/*

USER root
WORKDIR /root

# bitcoind
RUN wget https://bitcoincore.org/bin/bitcoin-core-24.0.1/bitcoin-24.0.1-aarch64-linux-gnu.tar.gz && \ 
    tar -xzf bitcoin-24.0.1-aarch64-linux-gnu.tar.gz && \
    mv bitcoin-24.0.1 bitcoin && \
    rm bitcoin-24.0.1-aarch64-linux-gnu.tar.gz

COPY ./conf/bitcoind/bitcoin.conf /root/

RUN mkdir /root/bitcoin/data

EXPOSE 8332 18332

# start
CMD ["bash", "-c", "/root/bitcoin/bin/bitcoind -conf=/root/bitcoin.conf -datadir=/root/bitcoin/data"]