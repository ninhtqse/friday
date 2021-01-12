import React from 'react';
import ReactDOM from 'react-dom';
import App from './components/App';
import { Provider } from "react-redux";
import { createStore } from "redux";


//Gọi reducers
import reducers from "./redux/reducers/index";

const store = createStore(reducers);
console.log(store);
ReactDOM.render(
  <React.StrictMode>
    <Provider store={store}>
    <App />
    </Provider>
  </React.StrictMode>,
  document.getElementById('root')
);