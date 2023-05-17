import socket
import json
from time import sleep

port = 60001
host = 'electrum.blockstream.info'

content = {
    "jsonrpc": "2.0",
    "method": "blockchain.headers.subscribe",
    "params": [],
    "id": 0
}


def electrumx(host, port, content):
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.connect((host, port))
    sock.sendall(json.dumps(content).encode('utf-8')+b'\n')
    sleep(0.5)
    sock.shutdown(socket.SHUT_WR)
    res = ""
    while True:
        data = sock.recv(1024)
        if (not data):
            break
        res += data.decode()
    print(res)

    # balance = eval(res)['result']
    # print(f'{balance["confirmed"] / 100000000} confirmed bitcoins')
    # print(f'{balance["unconfirmed"] / 100000000} unconfirmed bitcoins')

    sock.close()

electrumx(host, port, content)