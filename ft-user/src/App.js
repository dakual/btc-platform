import { BrowserRouter, Route, Routes } from 'react-router-dom';

import Login from "./pages/Login"
import Register from "./pages/Register"
import Home from "./pages/Home"
import Forgot from "./pages/Forgot"
import Reset from "./pages/Reset"
import Dashboard from './pages/Dashboard';
import UserLayout from './components/Layout';
import Wallet from './pages/Wallet';

function App() {
  return (
    <BrowserRouter>
    <Routes>
      <Route path="/user" element={<UserLayout />}>
        <Route index element={<Dashboard />} />
        <Route path="wallet" element={<Wallet />} />
      </Route>
      <Route path="/">
        <Route index element={<Home />} />
        <Route path="login" element={<Login />} />
        <Route path="register" element={<Register />} />
        <Route path="forgot" element={<Forgot />} />
        <Route path="reset" element={<Reset />} />
      </Route>
    </Routes>
    </BrowserRouter>
  );
}

export default App;
