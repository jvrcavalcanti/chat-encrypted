const socket = new WebSocket("ws://127.0.0.1:9502")

socket.onopen = () => {
  console.log("Conn")
  // localStorage.clear()
}

socket.onmessage = ev => {
  console.log(ev.data)
  const { event, data } = JSON.parse(ev.data)
  
  if (event === "connect") {
    console.log(data)
    window.data = {
      publicKey: Uint8Array.from(data.publicKey),
      nonce: Uint8Array.from(data.nonce)
    }
  }
}

window.sodium = {
  onload: function (sodium) {
      const keyPair = sodium.crypto_box_keypair()
      const publicKey = keyPair.publicKey
      const privateKey = keyPair.privateKey

      sendMessage('connect', {
        publicKey
      })

      function sendMessage (event, data) {
        socket.send(JSON.stringify({ event, data }))
      }

      function encrypt(message, nonce, publicKey, privateKey) {
        return sodium.crypto_box_easy(message, nonce, publicKey, privateKey)
      }
      
      document.getElementById('button').addEventListener('click', e => {
        const message = encrypt(
          document.getElementById("chat").value,
          window.data.nonce,
          window.data.publicKey,
          privateKey
        )

        console.log(message)
        // const message = sodium.crypto+
        sendMessage("message", message)
      })
  }
}

window.addEventListener("beforeunload", e => socket.close())
window.addEventListener("reload", e => socket.close())
