import { apiClient } from "../../../shared/api/client";
import type { Category, CategoryFormValues } from "../types";

export async function listCategories(): Promise<Category[]> {
  const { data } = await apiClient.get<{ data: Category[] }>("/api/categories");
  return data.data;
}

export async function createCategory(values: CategoryFormValues): Promise<Category> {
  const { data } = await apiClient.post<{ data: Category }>("/api/categories", values);
  return data.data;
}

export async function updateCategory(id: number, values: CategoryFormValues): Promise<Category> {
  const { data } = await apiClient.put<{ data: Category }>(`/api/categories/${id}`, values);
  return data.data;
}

export async function deleteCategory(id: number): Promise<void> {
  await apiClient.delete(`/api/categories/${id}`);
}
