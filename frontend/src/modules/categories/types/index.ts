export type Category = {
  id: number;
  name: string;
  active: boolean;
  created_at: string;
  updated_at: string;
};

export type CategoryFormValues = {
  name: string;
  active: boolean;
};
