import React from 'react'
import './Navbar.css';
import { useRef } from 'react';

function Navbar() {
  
const inputRef = useRef();
const focusoninput = () =>{
    inputRef.current.focus();
}

  return (
<nav className="navbar">
        <div className="logobox">
            <span id="logoname1">MUJ</span><span id="logoname2">STAYS</span>
        </div>
        <div className="searchsuper">
            <input ref={inputRef} type="text" className="searchbox" id="searchinput" placeholder= "find your perfect place to stay...."/>
            <button onClick={focusoninput}><i className="fa-solid fa-magnifying-glass"></i></button>
        </div>
        <div className="loginandsignupbox">
            <input type="button" value="Login" className="loginbutton"/>
            <input type="button" value="Create an account" className="signupbutton"/>
        </div>
        <div className="hamburger">
            <i className="fa-solid fa-bars"></i>
            <div className="dropdown-content">
                <a href="#">Create an account</a>
                <a href="#">Login</a>
            </div>
        </div>
    </nav>
  )
}

export default Navbar