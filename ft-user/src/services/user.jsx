import axios from 'axios';

const API_URL = 'http://localhost:8080/api';

class UserService {

  async login(credentials) {
    return axios
      .post(API_URL + "/user/login", credentials) // { withCredentials: true }
      .then(response => {
        if (response.data.data && response.data.data.token) {
          localStorage.setItem("accessToken", response.data.data.token);
        }

        return response.data;
      });
  }

  logout() {
    localStorage.removeItem("user");
  }

  getCurrentUser() {
    return localStorage.getItem('accessToken');
  }
}
const ss = new UserService();

export default ss; 