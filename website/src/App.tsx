import { BrowserRouter, Routes, Route } from "react-router-dom"
import Layout from "./components/layout/Layout"
import Home from "./pages/Home"
import Catalog from "./pages/Catalog"
import Gallery from "./pages/Gallery"
import CustomCake from "./pages/CustomCake"
import Checkout from "./pages/Checkout"

export function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Home />} />
          <Route path="catalog" element={<Catalog />} />
          <Route path="gallery" element={<Gallery />} />
          <Route path="custom" element={<CustomCake />} />
          <Route path="checkout" element={<Checkout />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
