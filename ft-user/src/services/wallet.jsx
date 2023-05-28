import axios from 'axios';
import authHeader from './auth-header';
// import { useNavigate } from "react-router-dom";

const API_URL = 'http://localhost:8081/api';

class WalletService {

  async getWallet() {
    return axios
      .get(API_URL + '/wallet?currency=btc', { headers: authHeader() })
      .then((response) => {
        if (response.data.error && response.data.error.code === 401) {
          // navigate("/");
        }
  
        return response.data;
      });
  }

  async createAddress(params) {
    return axios
      .post(API_URL + '/wallet', params, { headers: authHeader() })
      .then((response) => {
        return response.data;
      });
  }

  async withdraw(params) {
    return axios
      .post(API_URL + '/withdraw', params, { headers: authHeader() })
      .then((response) => {
        return response.data;
      });
  }

  async getTransactions() {
    return axios
      .get(API_URL + '/transaction?currency=btc', { headers: authHeader() })
      .then((response) => {
        return response.data;
      });
  }

  async getWithdrawals() {
    return axios
      .get(API_URL + '/withdraw?currency=btc', { headers: authHeader() })
      .then((response) => {
        return response.data;
      });
  }
}

const ws = new WalletService();

export default ws; 
