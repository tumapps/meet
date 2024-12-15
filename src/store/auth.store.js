import { defineStore } from 'pinia'
import CryptoJS from 'crypto-js'

const secretKey = 'Gk$e3lbvXi!n7kpiLamr@i9eZ@q220T4'
export const useAuthStore = defineStore('userAuth', {
  state: () => ({
    user: {
      isAuthenticated: false,
      username: null,
      token: null,
      userPreferences: null,
      ipAddr: null, // Added to store IP
      user_id: null, // Added to store user_id
      role: null, // Added to store booking status
      loggedIn: false
    }
  }),

  actions: {
    initStore() {
      const storedToken = localStorage.getItem('user.token')
      const storedUsername = localStorage.getItem('user.username')

      if (storedToken && storedUsername) {
        this.setToken(storedToken, storedUsername)
      }
    },

    setToken(token, username) {
      // console.log('Setting token:', token, username);

      this.user.token = token
      this.user.username = username
      this.user.isAuthenticated = true

      localStorage.setItem('user.token', token)
      localStorage.setItem('user.username', username)
      localStorage.setItem('loggedIn', true)
    },

    async removeToken() {
      this.user.token = null
      this.user.username = null
      this.user.isAuthenticated = false

      localStorage.removeItem('user.token')
      localStorage.removeItem('user.username')

      // console.log('Removed token');
    },

    setIp(ipAddr) {
      this.user.ipAddr = ipAddr
      sessionStorage.setItem('ipA', ipAddr)
      // console.log("Set IP address in store:", sessionStorage.getItem("ipA"));
    },

    setUserId(token) {
      if (token) {
        // Decode the token to get the user_id

        const arrayToken = token.split('.')
        const tokenPayload = JSON.parse(atob(arrayToken[1]))

        // console.log('Token payload:', tokenPayload);

        const user_id = tokenPayload.user_id // Declare user_id here
        // console.log('User ID:', user_id);

        // Convert user_id to a string before encryption
        const userIdString = user_id.toString()
        try {
          // Encrypt the user_id using AES encryption
          const encryptedUserId = CryptoJS.AES.encrypt(userIdString, secretKey).toString()

          // Store the encrypted user_id in the store's state
          this.user.user_id = encryptedUserId

          // Save the encrypted user_id in localStorage
          localStorage.setItem('user_id', encryptedUserId)
        } catch (error) {
          // console.error('Error during encryption:', error);
        }
      }
    },

    // Method to get and decrypt the user_id
    getUserId() {
      // Retrieve the encrypted user_id from localStorage
      const encryptedUserId = localStorage.getItem('user_id')
      if (encryptedUserId) {
        try {
          // Decrypt the user_id
          const bytes = CryptoJS.AES.decrypt(encryptedUserId, secretKey)
          const decryptedUserId = bytes.toString(CryptoJS.enc.Utf8)

          // Convert the decrypted value back to an integer
          const userIdInt = parseInt(decryptedUserId, 10)
          if (isNaN(userIdInt)) {
            // console.error('Decryption result is not a valid number', userIdInt);

            return null
          }

          // console.log('Decrypted user_id:', userIdInt);
          return userIdInt
        } catch (error) {
          // console.error('Error during decryption:', error);
        }
      }

      return null
    },

    setRole(role) {
      if (role) {
        // Convert user booking to a string before encryption
        const rolestring = role.toString()
        try {
          // Encrypt the user_id using AES encryption
          const encryptedrole = CryptoJS.AES.encrypt(rolestring, secretKey).toString()

          // Store the encrypted user_id in the store's state
          this.user.role = encryptedrole

          // Save the encrypted user_id in localStorage
          localStorage.setItem('Role', encryptedrole)
        } catch (error) {
          // console.error('Error during encryption:', error);
        }
      }
    },

    getRole() {
      // Retrieve the encrypted user_id from localStorage
      const encryptedrole = localStorage.getItem('Role')
      if (encryptedrole) {
        try {
          // Decrypt the user_id
          const bytes = CryptoJS.AES.decrypt(encryptedrole, secretKey)
          const decryptedRole = bytes.toString(CryptoJS.enc.Utf8)

          // Convert the decrypted value back to an integer
          const Role = decryptedRole

          console.log('Decrypted role:', Role)
          return Role
        } catch (error) {
          // console.error('Error during decryption:', error);
        }
      }

      return null
    },

    // Clear user_id from sessionStorage and store
    clearUserId() {
      this.user.user_id = null
      sessionStorage.removeItem('user_id')
    }
  },

  getters: {
    isAuthenticated: (state) => state.user.isAuthenticated
  }
})
