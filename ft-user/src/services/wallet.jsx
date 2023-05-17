import axios from 'axios';
import authHeader from './auth-header';
import { useNavigate } from "react-router-dom";


const API_URL = 'http://localhost:8081/api';


const WalletService = () => {
  const navigate = useNavigate();

  const getWallet = async () => {
    return axios
      .get(API_URL + '/wallet?currency=btc', { headers: authHeader() })
      .then((response) => {
        if (response.data.error && response.data.error.code === 401) {
          navigate("/");
        }
  
        return response.data;
      });
  }
}

export default WalletService;