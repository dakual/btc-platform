import React, { useState } from 'react'
import WalletService from '../services/wallet'
import Modal from '../components/Modal';

const Withdraw = () => {
  const [address, setAddress]         = useState('mkrRJzD9QVaeynDk5JLstg3pYkksLuNnNN');
  const [description, setDescription] = useState('test');
  const [amount, setAmount]           = useState(1000);

  const handleSubmit = async e => {
    e.preventDefault();

    new Modal().show();

    const form = e.currentTarget;
    if (form.checkValidity() === false) {
      e.stopPropagation();
      return;
    }

    WalletService.withdraw({
      "currency"    : "btc",
      "address"     : address,
      "amount"      : amount,
      "description" : description
    }).then(response => {
      console.log(response);
    },
    error => {
      console.log(error);
    });
  }

  return (
    <div>
      <h2>Withdraw</h2>
      <form method="POST" onSubmit={handleSubmit} autoComplete="off">
        <div className="mb-3">
          <label htmlFor="address" className="form-label">Address</label>
          <input type="text" className="form-control" id="address" value={address} onChange={e => setAddress(e.target.value)} required />
        </div>
        <div className="mb-3">
          <label htmlFor="description" className="form-label">Description</label>
          <input type="text" className="form-control" id="description" value={description} onChange={e => setDescription(e.target.value)} required />
        </div>
        <div className="mb-3">
          <label htmlFor="amount" className="form-label">Amount</label>
          <input type="number" className="form-control" id="amount" value={amount} placeholder="0.00000000" step=".01" onChange={e => setAmount(e.target.value)} required />
        </div>
        <div className="">
          <button type="submit" className="btn btn-primary">Send</button>
        </div>
      </form>
    </div>
    
  )
}

export default Withdraw;
