import { apiClient } from "../../../shared/api/client";
import type { Supplier, SupplierFormValues } from "../types";

export async function listSuppliers(): Promise<Supplier[]> {
  const { data } = await apiClient.get<{ data: Supplier[] }>("/api/suppliers");
  return data.data;
}

export async function createSupplier(values: SupplierFormValues): Promise<Supplier> {
  const { data } = await apiClient.post<{ data: Supplier }>("/api/suppliers", values);
  return data.data;
}

export async function updateSupplier(id: number, values: SupplierFormValues): Promise<Supplier> {
  const { data } = await apiClient.put<{ data: Supplier }>(`/api/suppliers/${id}`, values);
  return data.data;
}

export async function deleteSupplier(id: number): Promise<void> {
  await apiClient.delete(`/api/suppliers/${id}`);
}
