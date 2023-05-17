import React from "react";
import WalletService from "../services/wallet";
import { useNavigate } from "react-router-dom";

const Home = () => {

  const navigate = useNavigate();

  React.useEffect(() => {
    const wallets  = WalletService.getWallet(); 
    console.log(wallets); 
    if(false) {
      navigate("/login");
    }
  });

  return (
    <div>Home</div>
    
  )
}

export default Home

// class Home extends React.Component {
//   constructor(props) {
//     super(props);

//     onst wallets = WalletService.getWallet(); 
//     console.log(wallets); 
//   }

//   render() {
//     return (
//       <div>Home</div>
//     )
//   }
// }

// export default Home