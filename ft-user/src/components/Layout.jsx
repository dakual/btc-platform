import React, {useEffect} from "react";
import { Outlet, Link } from "react-router-dom"

const UserLayout = () => {

  useEffect(() => {
    if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
      document.body.classList.toggle('sb-sidenav-toggled');
    }
  });

  const toggleSidebar = event => {
    event.preventDefault();
    document.body.classList.toggle('sb-sidenav-toggled');
    localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
  }

  const handleLogout = () => {
    localStorage.removeItem("accessToken");
    window.location.href = "/";
  };

  return (
    <div className="d-flex" id="wrapper">
      <div className="border-end bg-white" id="sidebar-wrapper">
        <div className="sidebar-heading border-bottom bg-light">Start Bootstrap</div>
        <div className="list-group list-group-flush">
          <Link className="list-group-item list-group-item-action list-group-item-light p-3" to="/user">Dashboard</Link>
          <Link className="list-group-item list-group-item-action list-group-item-light p-3" to="/user/wallet">Wallet</Link>
          <Link className="list-group-item list-group-item-action list-group-item-light p-3" to="/user/withdraw">Withdraw</Link>
          <Link className="list-group-item list-group-item-action list-group-item-light p-3" to="/" onClick={handleLogout}>Logout</Link>
        </div>
      </div>
      <div id="page-content-wrapper">
        <nav className="navbar navbar-expand-lg navbar-light bg-light border-bottom">
          <div className="container-fluid">
            <button className="btn btn-primary" id="sidebarToggle" onClick={toggleSidebar}><span className="navbar-toggler-icon"></span></button>
          </div>
        </nav>
        <div className="container-fluid">
        <Outlet />
        </div>
      </div>
    </div>
  );
};

export default UserLayout;