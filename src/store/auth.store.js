import { defineStore } from 'pinia';
import createAxiosInstance from '../api/axios';

export const useAuthStore = defineStore('userAuth', {
    state: () => ({
        user: {
            isAuthenticated: false,
            username: null,
            token: null,
            userPreferences: null,
            ipAddr: null, // Added to store IP
        }
    }),

    actions: {
        initStore() {
            const storedToken = localStorage.getItem('user.token');
            const storedUsername = localStorage.getItem('user.username');

            if (storedToken && storedUsername) {
                this.setToken(storedToken, storedUsername);
                console.log('Initialized user', this.user);
            }
        },

        setToken(token, username) {
            console.log('Setting token:', token, username);

            this.user.token = token;
            this.user.username = username;
            this.user.isAuthenticated = true;

            localStorage.setItem('user.token', token);
            localStorage.setItem('user.username', username);
        },

        async removeToken() {
            this.user.token = null;
            this.user.username = null;
            this.user.isAuthenticated = false;

            localStorage.removeItem('user.token');
            localStorage.removeItem('user.username');

            console.log('Removed token');
        },

        setIp(ipAddr) {
            this.user.ipAddr = ipAddr;
            sessionStorage.setItem("ipA", ipAddr);
            console.log("Set IP address in store:", sessionStorage.getItem("ipA"));
        }
    },

    getters: {
        isAuthenticated: (state) => state.user.isAuthenticated,
    },
});








// import { defineStore } from 'pinia'

// // import axios from 'axios';
// import createAxiosInstance from '../api/axios';

// export const useAuthStore = defineStore('userAuth', {
//     state: () => ({
//         user: {
//             isAuthenticated: false,
//             username: null,
//             token: null,
//             userPreferences: null

//         }
//     }),

//     actions: {

//         initStore() {
//             // const userPreferences = localStorage.getItem('user.userPreferences')
//             const storedToken = localStorage.getItem('user.token');
//             const storedUsername = localStorage.getItem('user.username');

//             if (storedToken && storedUsername) {
//                 this.setToken(storedToken, storedUsername);
//             }
//             // this.user.isAuthenticated = false

//             // if (localStorage.getItem('user.token')) {
//             //     this.user.token = localStorage.getItem('user.token')
//             //     this.user.username = localStorage.getItem('user.username')
//             //     this.user.isAuthenticated = true

//             //     console.log('Initilized user', this.user)
//             // }


// const axiosInstance = createAxiosInstance();





//         },

//         setToken(token, username) {
//             console.log('set token already', token, username)

//             this.user.token = token
//             this.user.username = username
//             this.user.isAuthenticated = true

//             localStorage.setItem('user.token', token)
//             localStorage.setItem('user.username', username)

//         },


//         removeToken() {
//             //send a delete to refresh
//             const logoutUser = async () => {
//                 try {
//                     await axiosInstance.delete('/v1/iam/auth/logout', { withCredentials: true });
//                     console.log("Sent logout request");
//                     // Perform additional actions after logout if needed
//                 } catch (error) {
//                     console.error('Error during logout:', error);
//                 }
//             };

//             logoutUser();
            

//             this.user.token = null
//             this.user.username = null

//             this.user.isAuthenticated = false

//             localStorage.setItem('user.token', '')
//             localStorage.setItem('user.username', '')

//             console.log('Removed token')

//         },

//         setIp(ipAddr) {
//             this.user.ipAddr = ipAddr

//             sessionStorage.setItem("ipA", ipAddr)
//             console.log("set ipaddr in store:", sessionStorage.getItem(ipA))

//         }
//     },

//     getters: {
//         isAuthenticated: (state) => state.user.isAuthenticated,
//     },
// })