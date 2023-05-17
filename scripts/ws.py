import websocket
import time
import ssl
import json

try:
     import thread
except ImportError:
    import _thread as thread

# Callback for handling WebSocket open event
def on_open(ws):
    print("WebSocket connection opened.")

    content = {
        "method": "blockchain.header.subscribe",
        "params": ["9f02d2618613d621f1a7534497e28c2ad2e35a23e812833d3f26c1fa102b6316"],
        "id": 0
    }

    ws.send(json.dumps(content).encode('utf-8')+b'\n')

# Callback for handling WebSocket message event
def on_message(ws, message):
    print("Received message: {}".format(message))

# Callback for handling WebSocket error event
def on_error(ws, error):
    print("Error encountered: {}".format(error))

# Callback for handling WebSocket close event
def on_close(ws):
    print("WebSocket connection closed.")

# Define the WebSocket server URL
websocket_url = "wss://localhost:50004"

# Creating WebSocket connection object and attaching event handlers
ws = websocket.WebSocketApp(
    websocket_url,
    on_open=on_open,
    on_message=on_message,
    on_error=on_error,
    on_close=on_close
)

# Start the WebSocket connection
ws.run_forever(sslopt={"cert_reqs": ssl.CERT_NONE})

