import React from 'react'
import Topnav from '../components/topnav'
import Sidenav from '../components/sidenav'
import Content from '../components/content'

const Home = () => {

  return (
    <div>
      <Topnav />
      <div id="layoutSidenav">
        <Sidenav />  
        <Content />
      </div>
    </div>
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