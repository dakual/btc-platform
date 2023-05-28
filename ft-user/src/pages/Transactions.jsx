import React, { useEffect, useState } from 'react'
import WalletService from '../services/wallet'


const Transactions = () => {
  const [transactions, setTransactions] = useState([]);
  const [isLoading, setLoading]         = useState(false);

  const fetchData = () => {
    setLoading(true);
    WalletService.getTransactions().then(response => {
      setTransactions(response.data);
      setLoading(false);
    });
  };

  useEffect(() => {
    fetchData();
  }, []);

  return (
    <>
      <h2>Transactions Page</h2>

      {isLoading && <p>Loading...</p>}

      {transactions.transactions?.length > 0 && (
        <div className="table-responsive">
          <table className="table table-striped table-hover">
            <thead>
              <tr>
                <th scope="col">TX Hash</th>
                <th scope="col">Height</th>
                <th scope="col">Confirmation</th>
                <th scope="col">Amount</th>
              </tr>
            </thead>
            <tbody>
              {transactions.transactions.map((val, key) => {
                return (
                  <tr key={val.tx_hash}>
                    <th scope="row">{val.tx_hash}</th>
                    <td>{val.height}</td>
                    <td>{val.confirmation}</td>
                    <td>{val.decoded.value}</td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        </div>
      )}
    </>
  )
}

export default Transactions
