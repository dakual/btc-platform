import React from "react";
import {Modal, Button} from 'react-bootstrap';


class WithdrawModal {
  constructor(props) {
    super(props);
    this.state = {
      show: false,
      title: "",
      body: ""
    };
  }

  handleClose = () => {
    this.setState({ show: false });
  }

  show = () => {
    this.setState({ show: true });
  }

  render() {
    return (
      <Modal show={this.props.show} onHide={this.handleClose}>
        <Modal.Header closeButton>
          <Modal.Title>Modal heading</Modal.Title>
        </Modal.Header>
        <Modal.Body>Woohoo, you're reading this text in a modal!</Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={this.handleClose}>
            Close
          </Button>
          <Button variant="primary" onClick={this.handleClose}>
            Save Changes
          </Button>
        </Modal.Footer>
      </Modal>
    );
  }
}

export default WithdrawModal;