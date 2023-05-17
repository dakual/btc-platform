import React from "react";
import Alert from "../components/Alert";
import UserService from "../services/user";
import { withRouter } from '../common/with-router';

class Login extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      username: "",
      password: "",
      message: "",
      loading: false
    };

    this.handleInputChange = this.handleInputChange.bind(this);
    this.handleSubmit      = this.handleSubmit.bind(this);
  }

  handleInputChange(event) {
    event.preventDefault();

    const target = event.target;
    this.setState({
      [target.name]: target.value,
    });
  }

  async handleSubmit(event) {
    event.preventDefault();

    this.setState({
      message: "",
      loading: true
    });

    UserService.login({
      'username' : this.state.username,
      'password' : this.state.password
    }).then(response => {
        if(response.status === 'success') {
          localStorage.setItem("accessToken", response.data.token);
          this.props.router.navigate('/');
        } else {
          this.setState({
            loading: false,
            message: response.error.message
          });          
        }
      },
      error => {
        this.setState({
          loading: false,
          message: error.toString()
        });
      }
    );
  }


  render() {
    return (
    <div id="layoutAuthentication">
      <div id="layoutAuthentication_content">
          <main>
              <div className="container">
                  <div className="row justify-content-center">
                      <div className="col-lg-5">
                          <div className="card shadow-lg border-0 rounded-lg mt-5">
                              <div className="card-header"><h3 className="text-center font-weight-light my-4">Login</h3></div>
                              <div className="card-body">
                                  <form onSubmit={this.handleSubmit}>
                                      <div className="form-floating mb-3">
                                          <input
                                            id="username"
                                            className="form-control"
                                            placeholder="name@example.com"
                                            name="username"
                                            type="text"
                                            value={this.state.username}
                                            onChange={this.handleInputChange} />
                                          <label htmlFor="username">Email address</label>
                                      </div>
                                      <div className="form-floating mb-3">
                                          <input
                                            className="form-control"
                                            id="password"
                                            placeholder="Password"
                                            name="password"
                                            type="password"
                                            value={this.state.password}
                                            onChange={this.handleInputChange} />
                                          <label htmlFor="password">Password</label>
                                      </div>
                                      <div className="form-check mb-3">
                                          <input className="form-check-input" id="inputRememberPassword" type="checkbox" value="" />
                                          <label className="form-check-label" htmlFor="inputRememberPassword">Remember Password</label>
                                      </div>

                                      <Alert message={this.state.message} />

                                      <div className="d-flex align-items-center justify-content-between mt-4 mb-0">
                                          <a className="small" href="/login">Forgot Password?</a>
                                          <button className="btn btn-primary" type="submit">Login</button>
                                      </div>
                                  </form>
                              </div>
                              <div className="card-footer text-center py-3">
                                  <div className="small"><a href="/register">Need an account? Sign up!</a></div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </main>
      </div>
      <div id="layoutAuthentication_footer">
          <footer className="py-4 bg-light mt-auto">
              <div className="container-fluid px-4">
                  <div className="d-flex align-items-center justify-content-between small">
                      <div className="text-muted">Copyright &copy; Your Website 2023</div>
                      <div>
                          <a href="/">Privacy Policy</a>
                          &middot;
                          <a href="/">Terms &amp; Conditions</a>
                      </div>
                  </div>
              </div>
          </footer>
      </div>
    </div>
    );
  }
}

export default withRouter(Login);