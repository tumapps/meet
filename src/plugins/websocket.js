let socket = null
let listeners = []

export const connectWebSocket = (url) => {
  if (!socket) {
    socket = new WebSocket(url)

    socket.onopen = () => {
      //console.log('WebSocket connected')
    }

    socket.onmessage = (event) => {
      const data = JSON.parse(event.data)
      listeners.forEach((listener) => listener(data))
    }

    socket.onerror = (error) => {
      console.error('WebSocket error:', error)
    }

    socket.onclose = () => {
      //console.log('WebSocket disconnected')
      socket = null
    }
  }
}

export const sendWebSocketMessage = (message) => {
  if (socket && socket.readyState === WebSocket.OPEN) {
    socket.send(JSON.stringify(message))
  }
}

export const addWebSocketListener = (callback) => {
  listeners.push(callback)
}

export const removeWebSocketListener = (callback) => {
  listeners = listeners.filter((listener) => listener !== callback)
}

// Create a Vue plugin
export default {
  install(app, { url }) {
    connectWebSocket(url)
    app.config.globalProperties.$sendWebSocketMessage = sendWebSocketMessage
    app.config.globalProperties.$addWebSocketListener = addWebSocketListener
    app.config.globalProperties.$removeWebSocketListener = removeWebSocketListener
  }
}
