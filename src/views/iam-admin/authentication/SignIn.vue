<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/store/auth.store.js';
import createAxiosInstance from '@/api/axios';
import axios from 'axios';

const isLoading = ref(false);
const username = ref("");
const password = ref("");
const errors = ref({ username: '', password: '', general: '' });
const authStore = useAuthStore();
const router = useRouter();
const axiosInstance = createAxiosInstance(router, authStore);

const onSubmit = async () => {
  errors.value = { username: '', password: '', general: '' };

  try {
    isLoading.value = true;

    const response = await axiosInstance.post('/v1/iam/auth/login',  {
      username: username.value,
      password: password.value,
    });


if (response.data.dataPayload && !response.data.dataPayload.error) {
//set cookie
const setCookieHeader = response.headers['set-cookie'];
console.log('Cookie received:', setCookieHeader);

      authStore.setToken(
        response.data.dataPayload.data.token,
        response.data.dataPayload.data.username
      );

      router.push('/');

    } else {

      errors.value.general = 'An unexpected error occurred.';
    }
  } catch (error) {
    console.error("An unexpected error occurred:", error);

    if (error.response && error.response.status === 422 && error.response.data.errorPayload) {

      const errorDetails = error.response.data.errorPayload.errors;
      console.log("Validation error details:", errorDetails);

      errors.value.username = errorDetails.username || '';
      errors.value.password = errorDetails.password || '';

    }
  } finally {
    isLoading.value = false;
  }
};
</script>


<template>
  <section class="iq-auth-page">
    <div class="row m-0 align-items-center justify-content-center vh-100 w-100 ">
      <div class="col-md-4 col-lg-3 ">
        <b-card class="h-100 py-5 d-flex flex-column justify-content-between " style="background: white !important;">
          <img src="https://www.tum.ac.ke/resources/public/logo.png" class="img-thumbnail h-12 w-25 d-block mx-auto" alt="logo">
          <h5 class="text-center mt-3 mb-4 fw-bold text-primary">Sign In | TUMMEET</h5>


          <!-- <h4 class="text-center">Sign </h4> -->
          <!-- <p class="text-center">Sign in to stay connected</p> -->
          <form @submit.prevent="onSubmit">
            <div class="form-group">
              <label class="form-label" for="username-id">Username</label>
              <input type="text" class="form-control mb-0" id="username" v-model="username"
                placeholder="Enter username" aria-label="Username"/>
              <div v-if="errors.username" class="error" aria-live="polite">{{ errors.username }}</div> <!-- Access errors.value -->
            </div>
            <div class="form-group">
              <label class="form-label" for="password">Password</label>
              <input type="password" class="form-control mb-0" id="password" v-model="password"
                placeholder="Enter password" autocomplete="current-password" aria-label="Password" />
              <div v-if="errors.password" class="error" aria-live="polite">{{ errors.password }}</div> <!-- Access errors.value -->
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="form-check d-inline-block pt-1 mb-0">
                <input type="checkbox" class="form-check-input" id="customCheck11" />
                <label class="form-check-label" for="customCheck11">Remember Me</label>
              </div>
              <router-link :to="{ name: 'dashboard' }">Forgot password</router-link>
            </div>
            <div class="text-center pb-3">
              <button v-if="!isLoading" type="submit" class="btn btn-primary" :disabled="isLoading" aria-label="Sign in">Sign in</button>
              <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </div>
          </form>
        </b-card>
      </div>
    </div>
  </section>
</template>

<style>

.error {
  color: red;
  font-size: 0.9em;
}

.iq-auth-page {
  background-image: url('@/assets/images/tum.jpg');
  background-size: cover;
  background-repeat: no-repeat;
  min-height: 100vh;
  /* background-color: black !important; */
}


.b-card {
  margin-bottom: 0;
}


</style>
