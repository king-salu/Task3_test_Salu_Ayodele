import React, { createContext, useContext } from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import ReactDOM from 'react-dom/client';
import Login from './Login';
import Home from './Home';

function App() {
    return (
        <div className="App">
            <BrowserRouter>
                <Routes>
                    <Route path='/' element={<Login />} />
                    <Route path='/home' element={<Home />} />
                </Routes>
            </BrowserRouter>
        </div>
    );
}


const app = document.getElementById('app');
if (app) {
    const root = ReactDOM.createRoot(app);

    root.render(<App />);
}