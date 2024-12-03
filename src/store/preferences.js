// preferencesStore.js
import { defineStore } from 'pinia'

export const usePreferencesStore = defineStore('preferences', {
  state: () => ({
    weekend: true // Default value
  }),

  actions: {
    setWeekendPreference(weekend) {
      this.weekend = !!weekend // Ensure boolean
      persistState('preferences', this.$state)
    },

    loadPreferences() {
      const persistedPreferences = localStorage.getItem('preferences')
      if (persistedPreferences) {
        const parsedState = JSON.parse(persistedPreferences)

        // Ensure weekend is boolean
        this.$patch({
          weekend: typeof parsedState.weekend === 'boolean' ? parsedState.weekend : true
        })
      }
    }
  }
})

// Helper function to persist state
function persistState(key, state) {
  localStorage.setItem(key, JSON.stringify(state))
}
