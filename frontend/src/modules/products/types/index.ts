import type { Category } from "../../categories/types";
import type { Supplier } from "../../suppliers/types";

export type Product = {
  id: number;
  name: string;
  code: string;
  category: Category;
  supplier: Supplier | null;
  quantity: number;
  min_stock: number;
  expiration_date: string | null;
  cost_price: string;
  sale_price: string;
  active: boolean;
  created_at: string;
  updated_at: string;
};

export type ProductFormValues = {
  name: string;
  code: string;
  category_id: number | "";
  supplier_id: number | "";
  quantity: number;
  min_stock: number;
  expiration_date?: string | null;
  cost_price: number;
  sale_price: number;
  active: boolean;
};
