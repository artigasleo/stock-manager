import axios from "axios";

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
  withXSRFToken: true,
});

export function ensureCsrfCookie() {
  return apiClient.get("/sanctum/csrf-cookie");
}
