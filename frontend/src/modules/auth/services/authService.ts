import { apiClient, ensureCsrfCookie } from "../../../shared/api/client";
import type { User } from "../types";

export async function login(email: string, password: string): Promise<User> {
  await ensureCsrfCookie();

  const response = await apiClient.post<{ data: User }>("/api/login", {
    email,
    password,
  });

  return response.data.data;
}

export async function logout(): Promise<void> {
  await apiClient.post("/api/logout");
}

export async function fetchMe(): Promise<User | null> {
  try {
    const response = await apiClient.get<{ data: User }>("/api/me");

    return response.data.data;
  } catch {
    return null;
  }
}
