import React from 'react';
import {
  BrowserRouter as Router,
  Route,
} from "react-router-dom";
import Header from '../containers/Header';
import Products from '../containers/products/Products';
import Home from '../containers/home/Home';

import 'bootstrap/dist/css/bootstrap.min.css';

export default function App(){
  return (
    <Router>
      <div className="App">
        <Header />
        <Route path="/" exact component={Home} />
        <Route path="/products/" exact component={Products} />
      </div>
    </Router>
  )
}