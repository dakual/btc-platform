import React from 'react';

class Alert extends React.Component {
  // constructor(props) {
  //   super(props);
  // }
  
  render() {
    if (!this.props.message) return "";
    return <div className="alert alert-success" role="alert">{this.props.message}</div>;
  }
}

export default Alert