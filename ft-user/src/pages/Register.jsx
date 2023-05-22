import React, { useState } from 'react';
import Alert from "../components/Alert";
import UserService from "../services/user";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons'


const Register = () => {
	const [name, setName] 				= useState('daghan');
	const [username, setUsername] = useState('daghan@gmail.com');
  const [password, setPassword] = useState('1234');
  const [message, setMessage]   = useState();
	const [passwordType, setPasswordType] = useState("password");

	const togglePassword = () => {
    if(passwordType==="password") {
     setPasswordType("text")
     return;
    }
    setPasswordType("password")
  }

	const handleSubmit = async e => {
    e.preventDefault();

    const form = e.currentTarget;
    if (form.checkValidity() === false) {
      e.stopPropagation();
      return;
    }

    UserService.register({
			'name' : name,
      'username' : username,
      'password' : password
    }).then(response => {
        if(response.status === 'success') {
          setMessage(response.data.message); 
        } else {
          setMessage(response.error.message);     
        }
    },
    error => {
      setMessage(error.toString());   
    });
  }

  return (
    <section className="h-100">
	  <div className="container h-100">
	    <div className="row justify-content-sm-center h-100">
	      <div className="col-xxl-4 col-xl-5 col-lg-5 col-md-7 col-sm-9">
	        <div className="text-center my-5">
	          <img src={require('../assets/logo.png')} alt="logo" width="100" />
	        </div>
	        <div className="card shadow-lg">
	          <div className="card-body">
	            <h1 className="fs-4 card-title fw-bold mb-4">Register</h1>
	            <form method="POST" onSubmit={handleSubmit} autoComplete="off">
	              <div className="mb-3">
	                <label className="mb-2 text-muted" htmlFor="name">Name</label>
	                <input id="name" type="text" className="form-control" name="name" value={name} onChange={e => setName(e.target.value)} required autoFocus />
	                <div className="invalid-feedback"> Name is required </div>
	              </div>
	              <div className="mb-3">
	                <label className="mb-2 text-muted" htmlFor="email">E-Mail Address</label>
	                <input id="email" type="email" className="form-control" name="email" value={username} onChange={e => setUsername(e.target.value)} required />
	                <div className="invalid-feedback"> Email is invalid </div>
	              </div>

								<div className="mb-3">
									<div className="mb-2 w-100">
										<label className="text-muted" htmlFor="password">Password</label>
										<a href="/forgot" className="float-end"> Forgot Password? </a>
									</div>
									<div className="input-group mb-3">
										<input id="password" type={passwordType} className="form-control" name="password" value={password} onChange={e => setPassword(e.target.value)} required />
										<button className="btn btn-outline-secondary" type="button" onClick={togglePassword}>
											{ passwordType==="password" ? <FontAwesomeIcon icon={faEyeSlash} /> : <FontAwesomeIcon icon={faEye} /> }
										</button>
									</div>
									<div className="invalid-feedback"> Password is required </div>
								</div> 

	              <p className="form-text text-muted mb-3"> By registering you agree with our <a href="/register">terms and condition.</a></p>
	              <div className="align-items-center d-flex">
	                <button type="submit" className="btn btn-primary ms-auto"> Register </button>
	              </div>
	            </form>

							<Alert message={message} />

	          </div>
	          <div className="card-footer py-3 border-0">
	            <div className="text-center"> Already have an account? <a href="/login" className="text-dark">Login</a>
	            </div>
	          </div>
	        </div>
	        <div className="text-center mt-5 text-muted"> Copyright &copy; 2017-2021 &mdash; Your Company </div>
	      </div>
	    </div>
	  </div>
	</section>
  )
}

export default Register