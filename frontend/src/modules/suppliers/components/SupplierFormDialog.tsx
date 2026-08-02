import { zodResolver } from "@hookform/resolvers/zod";
import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  FormControlLabel,
  Switch,
  TextField,
} from "@mui/material";
import { useEffect } from "react";
import { Controller, useForm } from "react-hook-form";
import { z } from "zod";

import type { Supplier, SupplierFormValues } from "../types";

const schema = z.object({
  name: z.string().min(1, "O nome é obrigatório").max(150, "Máximo de 150 caracteres"),
  document: z.string().max(20, "Máximo de 20 caracteres").optional().or(z.literal("")),
  phone: z.string().max(20, "Máximo de 20 caracteres").optional().or(z.literal("")),
  email: z.string().email("Informe um e-mail válido").optional().or(z.literal("")),
  address: z.string().max(255, "Máximo de 255 caracteres").optional().or(z.literal("")),
  active: z.boolean(),
});

type SupplierFormInputs = z.infer<typeof schema>;

type Props = {
  open: boolean;
  supplier?: Supplier | null;
  onClose: () => void;
  onSubmit: (values: SupplierFormValues) => Promise<void>;
};

export function SupplierFormDialog({ open, supplier, onClose, onSubmit }: Props) {
  const {
    control,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<SupplierFormInputs>({
    resolver: zodResolver(schema),
    defaultValues: { name: "", document: "", phone: "", email: "", address: "", active: true },
  });

  useEffect(() => {
    if (open) {
      reset({
        name: supplier?.name ?? "",
        document: supplier?.document ?? "",
        phone: supplier?.phone ?? "",
        email: supplier?.email ?? "",
        address: supplier?.address ?? "",
        active: supplier?.active ?? true,
      });
    }
  }, [open, supplier, reset]);

  const submit = handleSubmit(async (values) => {
    await onSubmit({
      name: values.name,
      active: values.active,
      document: values.document || null,
      phone: values.phone || null,
      email: values.email || null,
      address: values.address || null,
    });
  });

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="sm">
      <DialogTitle>{supplier ? "Editar fornecedor" : "Novo fornecedor"}</DialogTitle>

      <Box component="form" onSubmit={submit} noValidate>
        <DialogContent>
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

          <Controller
            name="document"
            control={control}
            render={({ field }) => (
              <TextField
                {...field}
                value={field.value ?? ""}
                label="CNPJ/CPF"
                fullWidth
                margin="normal"
                error={!!errors.document}
                helperText={errors.document?.message}
              />
            )}
          />

          <Controller
            name="phone"
            control={control}
            render={({ field }) => (
              <TextField
                {...field}
                value={field.value ?? ""}
                label="Telefone"
                fullWidth
                margin="normal"
                error={!!errors.phone}
                helperText={errors.phone?.message}
              />
            )}
          />

          <Controller
            name="email"
            control={control}
            render={({ field }) => (
              <TextField
                {...field}
                value={field.value ?? ""}
                label="E-mail"
                fullWidth
                margin="normal"
                error={!!errors.email}
                helperText={errors.email?.message}
              />
            )}
          />

          <Controller
            name="address"
            control={control}
            render={({ field }) => (
              <TextField
                {...field}
                value={field.value ?? ""}
                label="Endereço"
                fullWidth
                margin="normal"
                error={!!errors.address}
                helperText={errors.address?.message}
              />
            )}
          />

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
