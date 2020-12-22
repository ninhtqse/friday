import React, { Component } from 'react';
import logo from './logo.svg';
import './App.css';
import 'bootstrap/dist/css/bootstrap.min.css';

import { Login } from './components';
class App extends Component {
  render() {
    return (
      <div className="App">
        <header className="App-header">
          <img src={logo} className="App-logo" alt="logo" />
        </header>
        <main>
          <Login />
        </main>
      </div>
    );
  }
}

export default App;




































// is_call_api = false;
// is_edit     = false;


// function kiemTraMaSo(){
//   //goi api
//   //check xem api co tra ra data khong
//   data = null; 
//   // gọi api ở đây nếu có dữ liệu data sẽ có còn không mạc định là null 
//   //cứ bấm kiểm tra là chuyển nó thành true để cho hàm keyup bên dưới không làm gì cả
//   is_call_api = true;
//   if(data){
//     //da kiem tra
//     daKiemtra();
//     //fill dữ liệu vào input hết xong thì set lại is_call_api = false
//     fillData();
//     is_call_api = false;
//   }else{
//     chuaKiemTra();
//   }
// }

// function fillData(){
//   //fill dữ liệu vào input
// }


// $( ".name .dob .sex" ).keyup(function() {
//   if(is_call_api == false){
//     chuaKiemTra()
//   }
// });


// function daKiemtra(){
//   //doi thanh chu da kiem tra
// }
// function chuaKiemTra(){
//   //doi thanh chu chua kiem tra
// }