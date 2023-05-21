import React, { useState } from 'react';
import Alert from "../components/Alert";
import UserService from "../services/user";


const Login = () => {
  const [username, setUsername] = useState('daghan.altunsoy@gmail.com');
  const [password, setPassword] = useState('1234');
  const [remember, setRemember] = useState(true);
  const [message, setMessage]   = useState();

  const handleSubmit = async e => {
    e.preventDefault();

    const form = e.currentTarget;
    if (form.checkValidity() === false) {
      e.stopPropagation();
      return;
    }

    UserService.login({
      'username' : username,
      'password' : password
    }).then(response => {
        if(response.status === 'success') {
          localStorage.setItem("accessToken", response.data.token);
          // window.location.href = "/";
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
                <h1 className="fs-4 card-title fw-bold mb-4">Login</h1>
                <form method="POST" onSubmit={handleSubmit} autoComplete="off">
                  <div className="mb-3">
                    <label className="mb-2 text-muted" htmlFor="email">E-Mail Address</label>
                    <input id="email" type="email" className="form-control" name="email" value={username} onChange={e => setUsername(e.target.value)} required autoFocus />
                    <div className="invalid-feedback"> Email is invalid </div>
                  </div>

                  <div className="mb-3">
                    <div className="mb-2 w-100">
                      <label className="text-muted" htmlFor="password">Password</label>
                      <a href="/forgot" className="float-end"> Forgot Password? </a>
                    </div>
                    <input id="password" type="password" className="form-control" name="password" value={password} onChange={e => setPassword(e.target.value)} required />
                    <div className="invalid-feedback"> Password is required </div>
                  </div> 

                  <div className="d-flex align-items-center">
                    <div className="form-check">
                      <input type="checkbox" name="remember" id="remember" className="form-check-input" checked={remember} onChange={e => setRemember(e.target.value)} />
                      <label htmlFor="remember" className="form-check-label">Remember Me</label>
                    </div>
                    <button type="submit" className="btn btn-primary ms-auto"> Login </button>
                  </div>
                </form>

                <Alert message={message} />

              </div>
              <div className="card-footer py-3 border-0">
                <div className="text-center"> Don't have an account? <a href="/register" className="text-dark">Create One</a>
                </div>
              </div>
            </div>
            <div className="text-center mt-5 text-muted"> Copyright &copy; 2017-2023 &mdash; Your Company </div>
          </div>
        </div>
      </div>
    </section>
  )
}

export default Login