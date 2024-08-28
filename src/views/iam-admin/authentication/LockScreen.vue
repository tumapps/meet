<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/store/auth.store.js';
import createAxiosInstance from '@/api/axios';

const isLoading = ref(false);
const password = ref("");
const errors = ref({ password: '', general: '' });
const authStore = useAuthStore();
const router = useRouter();
const axiosInstance = createAxiosInstance(router, authStore);
const username = localStorage.getItem('user.username');

const onSubmit = async () => {
  errors.value = { username: '', password: '', general: '' };

  try {
    isLoading.value = true;

    const response = await axiosInstance.post('/v1/auth/login', {
      username,
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

      router.back();

    } else {

      errors.value.general = 'An unexpected error occurred.';
    }
  } catch (error) {
    console.error("An unexpected error occurred:", error);

    if (error.response && error.response.status === 422 && error.response.data.errorPayload) {

      const errorDetails = error.response.data.errorPayload.errors;
      console.log("Validation error details:", errorDetails);
      errors.value.password = errorDetails.password || '';

    }
  } finally {
    isLoading.value = false;
  }
};


</script>

<template>
  <section class="backimg">
    <b-row class="align-items-center justify-content-center vh-100 w-100">
      <b-col cols="10" lg="6">
        <b-card class="text-center p-5 rounded-3 bg-aliceblue" style="min-height: 400px;" > <!-- Added padding and min-height -->
          <img src="@/assets/images/avatars/01.png" alt="User-Profile"
            class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded" loading="lazy" />
          <h4 class="mt-4">Hi ! {{ username }}</h4>
          <p>Enter your password to access the admin.</p>
          <div class="form-group me-3">
            <label class="form-label" for="lock-pass">Password</label>
            <input type="password" class="form-control mb-0" id="password" v-model="password"
              placeholder="Enter password" aria-label="Password" autocomplete="off" />
              <div v-if="errors.password" class="error" aria-live="polite">{{ errors.password }}</div> <!-- Access errors.value -->
          </div>
          <b-button class="bg-green" @click="onSubmit" variant="primary">Login</b-button>
        </b-card>
      </b-col>
    </b-row>
  </section>
</template>


<style lang="scss" scoped>

.backimg {
  background-image: url('@/assets/images/tum.jpg');
  background-size: cover;
  background-repeat: no-repeat;
  min-height: 100vh;
  /* background-color: black !important; */
}

.error {
  color: red;
  font-size: 1.1em;
}
</style>
