import React from 'react'
import Navbar from '../components/Navbar'
import Carousel from '../components/Carousel'
import RecommendedPGs from '../components/RecommendedPGs'
function Home() {
  return (
    <>
        <Navbar/>
        <Carousel/>
        <RecommendedPGs/>
    </>
  )
}

export default Home