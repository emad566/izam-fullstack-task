interface ToastOptions {
  type?: 'success' | 'error' | 'warning' | 'info'
  duration?: number
}

export const toast = {
  show: (message: string, options: ToastOptions = {}) => {
    const { type = 'info', duration = 3000 } = options

    // Create toast element
    const toastEl = document.createElement('div')
    toastEl.style.cssText = `
      position: fixed;
      top: 80px;
      right: 16px;
      z-index: 9999;
      padding: 12px 16px;
      border-radius: 8px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      color: white;
      font-weight: 500;
      max-width: 320px;
      min-width: 200px;
      transition: all 0.3s ease;
      transform: translateX(100%);
      word-wrap: break-word;
    `

    // Set background color based on type
    switch (type) {
      case 'success':
        toastEl.style.backgroundColor = '#16a34a'
        break
      case 'error':
        toastEl.style.backgroundColor = '#dc2626'
        break
      case 'warning':
        toastEl.style.backgroundColor = '#ca8a04'
        break
      default:
        toastEl.style.backgroundColor = '#2563eb'
        break
    }

    toastEl.textContent = message

    // Add to DOM
    document.body.appendChild(toastEl)

    // Animate in
    setTimeout(() => {
      toastEl.style.transform = 'translateX(0)'
    }, 10)

    // Remove after duration
    setTimeout(() => {
      toastEl.style.transform = 'translateX(100%)'
      setTimeout(() => {
        document.body.removeChild(toastEl)
      }, 300)
    }, duration)
  },

  success: (message: string, duration?: number) => toast.show(message, { type: 'success', duration }),
  error: (message: string, duration?: number) => toast.show(message, { type: 'error', duration }),
  warning: (message: string, duration?: number) => toast.show(message, { type: 'warning', duration }),
  info: (message: string, duration?: number) => toast.show(message, { type: 'info', duration })
}
