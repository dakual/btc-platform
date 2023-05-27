import React, { useEffect, useState } from 'react'
import WalletService from '../services/wallet'


const Wallet = () => {
  const [wallet, setWallet]     = useState([]);
  const [isLoading, setLoading] = useState(false);

  const fetchData = () => {
    setLoading(true);
    WalletService.getWallet().then(response => {
      setWallet(response.data);
      setLoading(false);
    });
  };

  const createAddress = () => {
    WalletService.createAddress({
      "currency":"btc"
    }).then(response => {
      fetchData();
      console.log(response);
    });
  };

  useEffect(() => {
    fetchData();
  }, []);

  return (
    <div>
      {isLoading && <p>Loading...</p>}

      <div className="d-flex">
        <div className="p-2 flex-grow-1">
          <h2 className='text-uppercase'>{wallet.currency} Wallet</h2>
          <h5>Total Confirmed: {wallet.totalConfirmed}</h5>
          <h5>Total Unconfirmed: {wallet.totalUnconfirmed}</h5>
        </div>
        <div className="p-2">
          <button type="button" className="btn btn-primary" onClick={ createAddress }>Create new address</button>
        </div>
      </div>

      

      {wallet.wallets?.length > 0 && (
        <div className="table-responsive">
          <table className="table table-striped table-hover">
            <thead>
              <tr>
                <th scope="col">Address</th>
                <th scope="col">Confirmed</th>
                <th scope="col">Unconfirmed</th>
                <th scope="col">Created Date</th>
              </tr>
            </thead>
            <tbody>
              {wallet.wallets.map((val, key) => {
                return (
                  <tr key={val.wid}>
                    <th scope="row">{val.address}</th>
                    <td>{val.balance.confirmed}</td>
                    <td>{val.balance.unconfirmed}</td>
                    <td>{val.created_at}</td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        </div>
      )}


    </div>
  )
}

export default Wallet;
