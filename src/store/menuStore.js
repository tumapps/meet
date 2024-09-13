import { defineStore } from 'pinia';

export const useMenuStore = defineStore('menuStore', {
    state: () => ({
        menus: [], // Default value
    }),
    actions: {
        setMenus(menus) {
            this.menus = menus;
            localStorage.setItem('menus', JSON.stringify(menus)); // Save to localStorage instead of sessionStorage
        },
        loadMenusFromStorage() {
            const storedMenus = localStorage.getItem('menus');
            if (storedMenus) {
                this.menus = JSON.parse(storedMenus); // Load from localStorage if exists
            }
        },
    },
});

// In your app initialization, call the `loadMenusFromStorage` method after creating the store
