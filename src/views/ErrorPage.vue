<!-- <script>
export default {
    props: {
        code: {
            type: String,
            required: true
        }
    },
    computed: {
        errorCode() {
            return this.code;
        },
        errorMessage() {
            switch (this.errorCode) {
                case '401':
                    return 'Unauthorized: Access is denied due to invalid credentials.';
                case '403':
                    return 'Forbidden: You do not have permission to access this resource.';
                case '404':
                    return 'Not Found: The requested resource could not be found.';
                case '500':
                    return 'Internal Server Error: An error occurred on the server.';
                default:
                    return null;
            }
        }
    }
};
</script>
<template>
    <div>
        <h1>Error {{ errorCode }}</h1>
        <p v-if="errorMessage">{{ errorMessage }}</p>
        <p v-else>An unknown error occurred.</p>
    </div>
</template> -->


<script>
import Error401 from '../components/Error401.vue';
import Error403 from '../components/Error403.vue';
import Error404 from '../components/Error404.vue';
import Error500 from '../components/Error500.vue';

export default {
    props: {
        code: {
            type: String,
            required: true
        }
    },
    components: {
        Error401,
        Error403,
        Error404,
        Error500
    },
    computed: {
        errorComponent() {
            switch (this.code) {
                case '401':
                    return 'Error401';
                case '403':
                    return 'Error403';
                case '404':
                    return 'Error404';
                case '500':
                    return 'Error500';
                default:
                    return null;
            }
        }
    }
};
</script>

<template>
    <div>
        <component :is="errorComponent" v-if="errorComponent"></component>
        <p v-else>An unknown error occurred.</p>
    </div>
</template>
