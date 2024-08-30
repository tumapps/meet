<script setup>
//export compoment name


</script>
<template>
    <b-row>
        <div class="col-xl-9 col-lg-8">
            <div class="card">
                <card-header class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Account Details</h4>
                    </div>
                </card-header>
                <b-card-body>
                    <div class="new-user-info">
                        <form @submit.prevent="addNewUser">
                            <div class="row">
                                <b-row>
                                    <b-col md="12" lg="6" class="form-group">
                                        <label class="form-label" for="uname">User Name:</label>
                                        <input v-model="username" type="text" class="form-control" id="uname"
                                            placeholder="User Name" />
                                        <div v-if="errors.username" class="error" aria-live="polite">{{ errors.username
                                            }}</div>

                                    </b-col>
                                </b-row>

                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="pass">Password:</label>
                                    <input v-model="password" type="password" class="form-control" id="pass"
                                        placeholder="Password" />
                                    <div v-if="errors.password" class="error" aria-live="polite">{{ errors.password }}
                                    </div>
                                </b-col>
                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="rpass">Repeat Password:</label>
                                    <input v-model="confirm_password" type="password" class="form-control" id="rpass"
                                        placeholder="Repeat Password " />
                                    <div v-if="errors.confirm_password" class="error" aria-live="polite">{{
                                        errors.confirm_password
                                        }}</div>
                                </b-col>
                            </div>
                            <hr />
                            <h5 class="mb-3">New User Information</h5>
                            <b-row>
                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="fname">First Name:</label>
                                    <input v-model="first_name" type="text" class="form-control" id="fname"
                                        placeholder="First Name" />
                                    <div v-if="errors.first_name" class="error" aria-live="polite">{{ errors.first_name
                                        }}</div>
                                </b-col>
                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="cname">Middle Name:</label>
                                    <input v-model="middle_name" type="text" class="form-control" id="cname"
                                        placeholder="Middle Name" />
                                    <div v-if="errors.middle_name" class="error" aria-live="polite">{{
                                        errors.middle_name }}</div>
                                </b-col>
                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="lname">Last Name:</label>
                                    <input v-model="last_name" type="text" class="form-control" id="lname"
                                        placeholder="Last Name" />
                                    <div v-if="errors.last_name" class="error" aria-live="polite">{{ errors.last_name }}
                                    </div>
                                </b-col>
                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="mobno">Mobile Number:</label>
                                    <input v-model="mobile_number" type="text" class="form-control" id="mobno"
                                        placeholder="Mobile Number" />
                                    <div v-if="errors.mobile_number" class="error" aria-live="polite">{{
                                        errors.mobile_number }}</div>
                                </b-col>
                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="email">Email:</label>
                                    <input v-model="email_address" type="email" class="form-control" id="email"
                                        placeholder="Email" />
                                    <div v-if="errors.email_address" class="error" aria-live="polite">{{
                                        errors.email_address }}</div>
                                </b-col>
                            </b-row>
                            <button type="submit" class="btn btn-primary">Add New User</button>
                        </form>
                    </div>
                </b-card-body>
            </div>
        </div>
    </b-row>
</template>
<style scoped>
.error {
    color: red;
}
</style>