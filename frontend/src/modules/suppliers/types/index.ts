export type Supplier = {
  id: number;
  name: string;
  document: string | null;
  phone: string | null;
  email: string | null;
  address: string | null;
  active: boolean;
  created_at: string;
  updated_at: string;
};

export type SupplierFormValues = {
  name: string;
  document?: string | null;
  phone?: string | null;
  email?: string | null;
  address?: string | null;
  active: boolean;
};
