import React, {useEffect} from 'react'
import WalletService from '../services/wallet'


const Wallet = () => {

  useEffect(() => {
    const wallets = WalletService.getWallet(); 
    console.log(wallets); 
  });

  return (
    <h2>Wallet Page</h2>
  )
}

export default Wallet;
