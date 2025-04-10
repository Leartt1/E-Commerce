// src/api/index.js

// Import all API modules
import productsApi from "./products";
import * as layoutApiModule from "./layouts/layoutApi";
import * as mediaApiModule from "./media/mediaApi";
import * as orderApiModule from "./orders/orderApi";
import * as configModule from "./config";

// Export API configuration
export const API_BASE_URL = configModule.API_BASE_URL;
export const commonHeaders = configModule.commonHeaders;
export const handleApiError = configModule.handleApiError;

// Export all API services
export const productApi = productsApi;
export const layoutApi = layoutApiModule;
export const mediaApi = mediaApiModule;
export const orderApi = orderApiModule;
