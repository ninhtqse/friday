import React, { useState } from "react";
import axios from "axios"; //Sử dụng axios
 
//Component hiển thị danh sách người dùng
const ShowUser = (props) => {
  //Lấy giá tri của props listUser
  const { listUser } = props;
  // Render ra list user
  // React.Fragment cho phép bọc JSX lại.
  // List Keys :  chỉ định key, giúp loại bỏ cảnh báo.
  return (
    <div>
      {listUser.map((user, index) => {
        return (
          <React.Fragment key={user.id}>
            <ul>
              <li>{user.name}</li>
              <li>{user.email}</li>
            </ul>
            <hr />
          </React.Fragment>
        );
      })}
    </div>
  );
};
 
export default function App(props) {
  //Khai báo state, sử dụng hook: useState
  const [listUser, setListUser] = useState([]);
  const [isLoading, setLoading] = useState(false);
 
  //API chứa dữ liệu người dùng
  const getUserAPI =
    "https://5df8a4c6e9f79e0014b6a587.mockapi.io/freetuts/users";
 
    //Hàm fetch API để lấy dữ liệu ng. dùng
    const getUser = () => {
        //Cập nhật lại giá trị của state loading
        setLoading(true);
        get(getUserAPI);
   
    };
    function get(url) {
        //Thực hiện gọi api
        axios
        .get(url)
        .then((res) => {
            //Cập nhật giá trị của state listUser
            setListUser(res.data);
        })
        .catch((err) => {
            //Trường hợp xảy ra lỗi
            alert("Không thể kết nối tới server");
        })
        .finally(() => {
            // Câu lệnh trong này được khởi chạy mỗi khi call API xong
            // bất kể thất bại hay không.
            // ...
            setLoading(false); //Cập nhật giá trị của state isLoading
        }); 
    }
    return (
        <div>
        <code>freetuts.net</code> <br />
        {isLoading ? "loading..." : <button onClick={getUser}>Get User</button>}
        <ShowUser listUser={listUser} />
        </div>
    );
}