import  AxiosInstance  from '../../api/axios.js';

const axiosInstance = AxiosInstance();

export const login = (Credentials) =>{
    return axiosInstance.post('/v1/auth/login', Credentials);

}
