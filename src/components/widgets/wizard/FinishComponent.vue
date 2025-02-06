<template>
  <fieldset>
    <div class="form-card">
      <b-row>
        <div class="col-7">
          <!-- <h3 class="mb-4 text-left">Finish:</h3> -->
        </div>
        <div class="col-5">
          <!-- <h2 class="steps">Step 3 - 3</h2> -->
        </div>
      </b-row>
      <br /><br />
      <h2 class="text-success text-center"><strong>SUCCESS !</strong></h2>
      <br />
      <b-row class="justify-content-center">
        <div class="col-3">
          <img src="@/assets/images/img-success.png" class="img-fluid" alt="fit-image" />
        </div>
      </b-row>
      <br /><br />
      <b-row class="justify-content-center">
        <div class="col-7 text-center">
          <!-- <h5 class="purple-text text-center">User Created</h5> -->
        </div>
      </b-row>
    </div>
  </fieldset>
  <button @click="back" class="btn btn-warning float-end me-1">Close</button>

</template>
<script setup> 
// import { useRouter } from 'vue-router';

// const router = useRouter();

// const back = () => {
// router.push('/');};



// export default {
//   name: 'FinishComponent'
// }
</script>
