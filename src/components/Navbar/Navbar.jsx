import React from 'react'
import './Navbar.css';
function Navbar() {
  return (
    <nav className="navbar">
        <div className="logobox">
            <span id="logoname1">MUJ</span><span id="logoname2">STAYS</span>
        </div>
        <div className="searchsuper">
            <input type="text" className="searchbox" id="searchinput" placeholder= "find your perfect place to stay...."/>
            <button type="submit"><i className="fa-solid fa-magnifying-glass"></i></button>
        </div>
        <div className="loginandsignupbox">
            <input type="button" value="Login" className="loginbutton"/>
            <input type="button" value="Create an account" className="signupbutton"/>
        </div>
    </nav>
  )
}

export default Navbar