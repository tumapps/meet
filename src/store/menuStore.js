import { defineStore } from 'pinia';

export const useMenuStore = defineStore('menuStore', {
    state: () => ({
        menus: [],
    }),
    actions: {
        setMenus(menus) {
            this.menus = menus;
        },
    },
});
