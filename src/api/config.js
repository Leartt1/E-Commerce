// src/api/config.js

// API configuration
export const API_BASE_URL = "http://localhost:8000/php/api";

// Common headers
export const commonHeaders = {
  "Content-Type": "application/json",
};

// Error handling
export const handleApiError = (error) => {
  console.error("API Error:", error);

  // Handle network errors specifically
  if (error.message && error.message.includes("Network Error")) {
    console.warn(
      "Network error detected. Check that PHP server is running on port 8000"
    );
    return {
      error: true,
      message:
        "Cannot connect to server. Please ensure the PHP backend is running.",
    };
  }

  return {
    error: true,
    message: error.message || "An unexpected error occurred",
  };
};
