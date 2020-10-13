import bearer from '@websanova/vue-auth/drivers/auth/bearer'
import axios from '@websanova/vue-auth/drivers/http/axios.1.x'
import router from '@websanova/vue-auth/drivers/router/vue-router.2.x'

const config = {
  auth: bearer,
  http: axios,
  router: router,
  tokenDefaultName: 'laravel-jwt-auth',
  tokenStore: ['localStorage'],
  rolesVar: 'role',
  
  registerData: {
    url: 'user/store', 
    method: 'POST', 
    redirect: '/login'
  },
  loginData: {
    url: 'user/login', 
    method: 'POST', 
    redirect: '/', 
    fetchUser: true
  },
  logoutData: {
    url: 'logout', 
    method: 'POST', 
    redirect: '/', 
    makeRequest: true
  },
  fetchData: {
    url: 'user/all', 
    method: 'GET', 
    enabled: true
  },
  refreshData: {
    url: 'user/refresh', 
    method: 'GET', 
    enabled: true, 
    interval: 30
  }
}
export default config