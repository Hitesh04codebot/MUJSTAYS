import { useState } from "react";
import { Menu, X } from "lucide-react";
import { AiOutlineHome } from "react-icons/ai";
import { AiOutlineInfoCircle } from "react-icons/ai";
import { MdOutlinePhone } from "react-icons/md";

export default function Navbar() {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <nav className="w-full h-20 bg-white border-b border-gray-300 fixed top-0 left-0 z-50">
      <div className="max-w-7xl h-full mx-auto px-4 flex items-center justify-between">
        {/* Logo */}
        <div>
          <span className="font-bold text-4xl text-blue-600">MUJ</span>
          <span className="font-bold text-4xl text-black">STAYS</span>
        </div>

        {/* Desktop Menu */}
        <div className="hidden md:flex">
          <ul className="flex space-x-10">
            <li className="cursor-pointer hover:text-blue-600 transition">
              Home
            </li>
            <li className="cursor-pointer hover:text-blue-600 transition">
              About
            </li>
            <li className="cursor-pointer hover:text-blue-600 transition">
              Contact
            </li>
          </ul>
        </div>

        {/* Desktop Buttons */}
        <div className="hidden md:flex">
          <button className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition cursor-pointer">
            Login
          </button>
          <button className="ml-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition cursor-pointer">
            Sign Up
          </button>
        </div>

        {/* Hamburger (only on small/medium screens) */}
        <div className="md:hidden">
          <button onClick={() => setIsOpen(!isOpen)}>
            {isOpen ? <X size={28} /> : <Menu size={28} />}
          </button>
        </div>
      </div>

      {/* Sidebar (slides from LEFT now) */}
      <div
        className={`fixed top-0 left-0 h-full w-64 bg-white shadow-lg transform transition-transform duration-300 z-40 
        ${isOpen ? "translate-x-0" : "-translate-x-full"}`}
      >
        <div className="p-6 flex flex-col justify-center space-y-6">
          <ul className="space-y-4 text-lg">
            <li className="cursor-pointer hover:text-blue-600 transition flex items-center space-x-2">
              <AiOutlineHome size={20} />
              <span>Home</span>
            </li>

            <li className="cursor-pointer hover:text-blue-600 transition flex items-center space-x-2">
              <AiOutlineInfoCircle size={20} />
              <span>About</span>
            </li>

            <li className="cursor-pointer hover:text-blue-600 transition flex items-center space-x-2">
              <MdOutlinePhone size={20} />
              <span>Contact</span>
            </li>
          </ul>
          <div className="flex flex-col space-y-4">
            <button className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition cursor-pointer">
              Login
            </button>
            <button className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition cursor-pointer">
              Sign Up
            </button>
          </div>
        </div>
      </div>

      {/* Overlay when sidebar is open */}
      {isOpen && (
        <div
          onClick={() => setIsOpen(false)}
          className="fixed inset-0 bg-black opacity-40 z-30"
        ></div>
      )}
    </nav>
  );
}
