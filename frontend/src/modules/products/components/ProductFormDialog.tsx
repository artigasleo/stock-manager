import { zodResolver } from "@hookform/resolvers/zod";
import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  FormControlLabel,
  Grid,
  MenuItem,
  Switch,
  TextField,
} from "@mui/material";
import { useQuery } from "@tanstack/react-query";
import { useEffect } from "react";
import { Controller, useForm } from "react-hook-form";
import { z } from "zod";

import { listCategories } from "../../categories/services/categoryService";
import { listSuppliers } from "../../suppliers/services/supplierService";
import type { Product, ProductFormValues } from "../types";

const schema = z.object({
  name: z.string().min(1, "O nome é obrigatório").max(150, "Máximo de 150 caracteres"),
  code: z.string().min(1, "O código é obrigatório").max(50, "Máximo de 50 caracteres"),
  category_id: z
    .union([z.number(), z.literal("")])
    .refine((value): boolean => value !== "", "A categoria é obrigatória"),
  supplier_id: z.union([z.number(), z.literal("")]),
  quantity: z.number().min(0, "Não pode ser negativo"),
  min_stock: z.number().min(0, "Não pode ser negativo"),
  expiration_date: z.string().optional().or(z.literal("")),
  cost_price: z.number().min(0, "Não pode ser negativo"),
  sale_price: z.number().min(0, "Não pode ser negativo"),
  active: z.boolean(),
});

type ProductFormInputs = z.infer<typeof schema>;

const emptyValues: ProductFormInputs = {
  name: "",
  code: "",
  category_id: "",
  supplier_id: "",
  quantity: 0,
  min_stock: 0,
  expiration_date: "",
  cost_price: 0,
  sale_price: 0,
  active: true,
};

type Props = {
  open: boolean;
  product?: Product | null;
  onClose: () => void;
  onSubmit: (values: ProductFormValues) => Promise<void>;
};

export function ProductFormDialog({ open, product, onClose, onSubmit }: Props) {
  const { data: categories = [] } = useQuery({ queryKey: ["categories"], queryFn: listCategories });
  const { data: suppliers = [] } = useQuery({ queryKey: ["suppliers"], queryFn: listSuppliers });

  const {
    control,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<ProductFormInputs>({
    resolver: zodResolver(schema),
    defaultValues: emptyValues,
  });

  useEffect(() => {
    if (open) {
      reset(
        product
          ? {
              name: product.name,
              code: product.code,
              category_id: product.category.id,
              supplier_id: product.supplier?.id ?? "",
              quantity: product.quantity,
              min_stock: product.min_stock,
              expiration_date: product.expiration_date ?? "",
              cost_price: Number(product.cost_price),
              sale_price: Number(product.sale_price),
              active: product.active,
            }
          : emptyValues,
      );
    }
  }, [open, product, reset]);

  const submit = handleSubmit(async (values) => {
    await onSubmit({
      name: values.name,
      code: values.code,
      category_id: values.category_id,
      supplier_id: values.supplier_id,
      quantity: values.quantity,
      min_stock: values.min_stock,
      cost_price: values.cost_price,
      sale_price: values.sale_price,
      active: values.active,
      expiration_date: values.expiration_date || null,
    });
  });

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
      <DialogTitle>{product ? "Editar produto" : "Novo produto"}</DialogTitle>

      <Box component="form" onSubmit={submit} noValidate>
        <DialogContent>
          <Grid container spacing={2}>
            <Grid size={8}>
              <Controller
                name="name"
                control={control}
                render={({ field }) => (
                  <TextField
                    {...field}
                    label="Nome"
                    fullWidth
                    autoFocus
                    margin="normal"
                    error={!!errors.name}
                    helperText={errors.name?.message}
                  />
                )}
              />
            </Grid>

            <Grid size={4}>
              <Controller
                name="code"
                control={control}
                render={({ field }) => (
                  <TextField
                    {...field}
                    label="Código"
                    fullWidth
                    margin="normal"
                    error={!!errors.code}
                    helperText={errors.code?.message}
                  />
                )}
              />
            </Grid>

            <Grid size={6}>
              <Controller
                name="category_id"
                control={control}
                render={({ field }) => (
                  <TextField
                    {...field}
                    select
                    label="Categoria"
                    fullWidth
                    margin="normal"
                    error={!!errors.category_id}
                    helperText={errors.category_id?.message}
                  >
                    {categories.map((category) => (
                      <MenuItem key={category.id} value={category.id}>
                        {category.name}
                      </MenuItem>
                    ))}
                  </TextField>
                )}
              />
            </Grid>

            <Grid size={6}>
              <Controller
                name="supplier_id"
                control={control}
                render={({ field }) => (
                  <TextField {...field} select label="Fornecedor" fullWidth margin="normal">
                    <MenuItem value="">Nenhum</MenuItem>
                    {suppliers.map((supplier) => (
                      <MenuItem key={supplier.id} value={supplier.id}>
                        {supplier.name}
                      </MenuItem>
                    ))}
                  </TextField>
                )}
              />
            </Grid>

            <Grid size={4}>
              <Controller
                name="quantity"
                control={control}
                render={({ field: { onChange, ...field } }) => (
                  <TextField
                    {...field}
                    onChange={(e) => onChange(e.target.value === "" ? 0 : Number(e.target.value))}
                    type="number"
                    label="Quantidade"
                    fullWidth
                    margin="normal"
                    error={!!errors.quantity}
                    helperText={errors.quantity?.message}
                  />
                )}
              />
            </Grid>

            <Grid size={4}>
              <Controller
                name="min_stock"
                control={control}
                render={({ field: { onChange, ...field } }) => (
                  <TextField
                    {...field}
                    onChange={(e) => onChange(e.target.value === "" ? 0 : Number(e.target.value))}
                    type="number"
                    label="Estoque mínimo"
                    fullWidth
                    margin="normal"
                    error={!!errors.min_stock}
                    helperText={errors.min_stock?.message}
                  />
                )}
              />
            </Grid>

            <Grid size={4}>
              <Controller
                name="expiration_date"
                control={control}
                render={({ field }) => (
                  <TextField
                    {...field}
                    value={field.value ?? ""}
                    type="date"
                    label="Validade"
                    fullWidth
                    margin="normal"
                    slotProps={{ inputLabel: { shrink: true } }}
                  />
                )}
              />
            </Grid>

            <Grid size={6}>
              <Controller
                name="cost_price"
                control={control}
                render={({ field: { onChange, ...field } }) => (
                  <TextField
                    {...field}
                    onChange={(e) => onChange(e.target.value === "" ? 0 : Number(e.target.value))}
                    type="number"
                    label="Preço de custo"
                    fullWidth
                    margin="normal"
                    error={!!errors.cost_price}
                    helperText={errors.cost_price?.message}
                  />
                )}
              />
            </Grid>

            <Grid size={6}>
              <Controller
                name="sale_price"
                control={control}
                render={({ field: { onChange, ...field } }) => (
                  <TextField
                    {...field}
                    onChange={(e) => onChange(e.target.value === "" ? 0 : Number(e.target.value))}
                    type="number"
                    label="Preço de venda"
                    fullWidth
                    margin="normal"
                    error={!!errors.sale_price}
                    helperText={errors.sale_price?.message}
                  />
                )}
              />
            </Grid>
          </Grid>

          <Controller
            name="active"
            control={control}
            render={({ field }) => (
              <FormControlLabel
                control={
                  <Switch checked={field.value} onChange={(e) => field.onChange(e.target.checked)} />
                }
                label="Ativo"
              />
            )}
          />
        </DialogContent>

        <DialogActions>
          <Button onClick={onClose}>Cancelar</Button>
          <Button type="submit" variant="contained" disabled={isSubmitting}>
            Salvar
          </Button>
        </DialogActions>
      </Box>
    </Dialog>
  );
}
