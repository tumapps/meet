import { onUnmounted } from 'vue'

export function useCleanup() {
  const cleanups = []

  // Register a cleanup function
  const registerCleanup = (cleanupFn) => {
    cleanups.push(cleanupFn)
  }

  // Run all cleanup functions automatically on component unmount
  onUnmounted(() => {
    cleanups.forEach((cleanup) => cleanup())
  })

  return { registerCleanup }
}
