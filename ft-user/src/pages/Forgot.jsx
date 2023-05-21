const Forgot = () => {
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
	            <h1 className="fs-4 card-title fw-bold mb-4">Forgot Password</h1>
	            <form method="POST" className="needs-validation" novalidate="" autocomplete="off">
	              <div className="mb-3">
	                <label className="mb-2 text-muted" htmlFor="email">E-Mail Address</label>
	                <input id="email" type="email" className="form-control" name="email" value="" required autofocus />
	                <div className="invalid-feedback"> Email is invalid </div>
	              </div>
	              <div className="d-flex align-items-center">
	                <button type="submit" className="btn btn-primary ms-auto"> Send Link </button>
	              </div>
	            </form>
	          </div>
	          <div className="card-footer py-3 border-0">
	            <div className="text-center"> Remember your password? <a href="/login" className="text-dark">Login</a>
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

export default Forgot