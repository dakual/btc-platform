import React from 'react';

class Modal extends React.Component {
  // constructor(props) {
  //   super(props);
  // }

  show() {
    (this.modal).modal('show');
  };

  render() {
    return (
      <div className="modal" tabIndex="-1" ref={modal => this.modal = modal}>
        <div className="modal-dialog">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title">{this.props.title}</h5>
              <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div className="modal-body">
              <p>{this.props.message}</p>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" className="btn btn-primary">Save changes</button>
            </div>
          </div>
        </div>
      </div>      
    );
  }
}

export default Modal