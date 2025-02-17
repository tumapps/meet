import { defineStore } from 'pinia'

export const useMenuStore = defineStore('menuStore', {
  state: () => ({
    menus: [] // Default value
  }),
  actions: {
    setMenus(menus) {
      this.menus = menus
      localStorage.setItem('menus', JSON.stringify(menus)) // Save to localStorage instead of sessionStorage
    },
    loadMenusFromStorage() {
      const storedMenus = localStorage.getItem('menus')
      if (storedMenus) {
        this.menus = JSON.parse(storedMenus) // Load from localStorage if exists
      }
    },

    navigateToFirstMenu(router) {
      // Accept router as a parameter
      console.log('Navigating to first menu')
      if (this.menus.length > 0) {
        const firstRoute = `/${this.menus[0].route}`
        console.log('Navigating to here:', firstRoute)
        router.push(firstRoute) // Use the passed router instance
      } else {
        console.error('No menus available to navigate.')
      }
    }
  }
})

// In your app initialization, call the `loadMenusFromStorage` method after creating the store
