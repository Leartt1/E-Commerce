// src/App.js

import React from "react";
import {
  BrowserRouter as Router,
  Route,
  Routes,
  Navigate,
} from "react-router-dom";
import { Backoffice } from "./features/backoffice";
import { ProductDetail } from "./features/products";
import { LandingPage } from "./layout";
import { PageBuilder } from "./features/pageBuilder";

// Ensure we import any required styles
import "react-quill/dist/quill.snow.css";
import "bootstrap/dist/css/bootstrap.min.css";
import "./App.css";

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<LandingPage />} />

        {/* Admin route that redirects to backoffice */}
        <Route path="/admin" element={<Navigate to="/backoffice" replace />} />

        {/* Backoffice Routes */}
        <Route path="/backoffice" element={<Backoffice />} />
        <Route
          path="/backoffice/products"
          element={<Backoffice initialSection="products" />}
        />
        <Route
          path="/backoffice/orders"
          element={<Backoffice initialSection="orders" />}
        />
        <Route
          path="/backoffice/clients"
          element={<Backoffice initialSection="clients" />}
        />
        <Route
          path="/backoffice/media"
          element={<Backoffice initialSection="media" />}
        />
        <Route
          path="/backoffice/layouts"
          element={<Backoffice initialSection="layouts" />}
        />

        {/* Page Builder Route */}
        <Route path="/pagebuilder/:id" element={<PageBuilder />} />

        {/* Product Detail Route */}
        <Route path="/product/:id" element={<ProductDetail />} />

        {/* Fallback Route */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </Router>
  );
}

export default App;
