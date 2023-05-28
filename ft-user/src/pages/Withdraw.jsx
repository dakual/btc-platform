import React, { useState, useEffect } from 'react'
import WalletService from '../services/wallet'
import {Modal, Button} from 'react-bootstrap';
import Alert from 'react-bootstrap/Alert';

const Withdraw = () => {
  const [address, setAddress]         = useState('mkrRJzD9QVaeynDk5JLstg3pYkksLuNnNN');
  const [description, setDescription] = useState('test');
  const [amount, setAmount]           = useState(1000);
  const [show, setShow]               = useState(false);
  const [response, setResponse]       = useState([]);
  const [message, setMessage]         = useState();
  const [messageType, setMessageType] = useState();
  const [withdrawals, setWithdrawals] = useState();

  const fetchData = () => {
    WalletService.getWithdrawals().then(response => {
      console.log(response.data.withdrawals);
      setWithdrawals(response.data.withdrawals);
    });
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleClose = () => {
    setShow(false);
  }

  const handleSubmit = async e => {
    e.preventDefault();

    const form = e.currentTarget;
    if (form.checkValidity() === false) {
      e.stopPropagation();
      return;
    }

    setMessage('');

    WalletService.withdraw({
      "currency"    : "btc",
      "address"     : address,
      "amount"      : amount,
      "description" : description
    }).then(response => {
      setResponse(response);
      setShow(true);
    },
    error => {
      console.log(error);
    });
  }

  const handleWithdraw = async e => {
    e.preventDefault();

    WalletService.withdraw({
      "currency"    : "btc",
      "address"     : address,
      "amount"      : amount,
      "description" : description,
      "action"      : "send"
    }).then(response => {
      console.log(response);
      setShow(false);
      setMessage('Successfully sent '+ response.data.transaction.amount +' btc to '+ response.data.transaction.address +'.');
      setMessageType('success');
      fetchData();
    },
    error => {
      console.log(error);
    });
  }

  return (
    <>
      <h2>Withdraw</h2>

      {message && (
        <Alert key={messageType} variant={messageType}>{message}</Alert>
      )}
      
      <div className="row">
        <div className="col">
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
              <button type="submit" className="btn btn-primary">Create Transaction</button>
            </div>
          </form>      
        </div>
        <div className="col">
          <h3>Last Withdrawals</h3>
          {withdrawals?.length > 0 && (
          <ul className="list-group">
            {withdrawals.map((val, key) => {
              return (
                <li className="list-group-item" key={key}>{val.amount}<br />{val.created_at}</li>
              )
            })}
          </ul>
          )}
        </div>
      </div>
      


      {show && (
      <Modal show={show} onHide={handleClose}>
        <Modal.Header closeButton>
          <Modal.Title>Transaction review</Modal.Title>
        </Modal.Header>
        <Modal.Body>You will send <b>{response.data.transaction.amount} {response.data.currency}</b> to <b>{response.data.transaction.address}</b>. You will pay <b>{response.data.transaction.fee} {response.data.currency}</b> fee for this transaction.<br /><br />Do you want to continue?</Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={handleClose}>Close</Button>
          <Button variant="primary" onClick={handleWithdraw}>Send</Button>
        </Modal.Footer>
      </Modal>           
      )}
   
    </>
  )
}

export default Withdraw;
