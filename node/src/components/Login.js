import React from 'react';
import '../Login.css';
const Login = () => {
    return (
      <div className="container">
        <div className="row">
            <div className="col-lg-4 box-center">
            <h4>Login</h4>
            <div className=" form-group">
                <label className="float-left">USERNAME : </label>
                <input className="form-control username" />                  
            </div>
            <div className=" form-group">
                <label className="float-left">PASSWORD : </label>
                <input type="password" className="form-control password" />                  
            </div>
            <button className="button btn btn-primary color-button-blue" >
                LOGIN
            </button>
            </div>
        </div>
    </div>
    );

}

export { Login };