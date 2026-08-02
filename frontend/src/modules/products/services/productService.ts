import { apiClient } from "../../../shared/api/client";
import type { Product, ProductFormValues } from "../types";

type ProductPayload = Omit<ProductFormValues, "category_id" | "supplier_id"> & {
  category_id: number;
  supplier_id: number | null;
};

function toPayload(values: ProductFormValues): ProductPayload {
  return {
    ...values,
    category_id: Number(values.category_id),
    supplier_id: values.supplier_id === "" ? null : Number(values.supplier_id),
  };
}

export async function listProducts(): Promise<Product[]> {
  const { data } = await apiClient.get<{ data: Product[] }>("/api/products");
  return data.data;
}

export async function createProduct(values: ProductFormValues): Promise<Product> {
  const { data } = await apiClient.post<{ data: Product }>("/api/products", toPayload(values));
  return data.data;
}

export async function updateProduct(id: number, values: ProductFormValues): Promise<Product> {
  const { data } = await apiClient.put<{ data: Product }>(`/api/products/${id}`, toPayload(values));
  return data.data;
}

export async function deleteProduct(id: number): Promise<void> {
  await apiClient.delete(`/api/products/${id}`);
}
