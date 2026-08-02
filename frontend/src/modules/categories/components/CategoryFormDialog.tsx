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

import type { Category, CategoryFormValues } from "../types";

const schema = z.object({
  name: z.string().min(1, "O nome é obrigatório").max(100, "Máximo de 100 caracteres"),
  active: z.boolean(),
});

type Props = {
  open: boolean;
  category?: Category | null;
  onClose: () => void;
  onSubmit: (values: CategoryFormValues) => Promise<void>;
};

export function CategoryFormDialog({ open, category, onClose, onSubmit }: Props) {
  const {
    control,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<CategoryFormValues>({
    resolver: zodResolver(schema),
    defaultValues: { name: "", active: true },
  });

  useEffect(() => {
    if (open) {
      reset({
        name: category?.name ?? "",
        active: category?.active ?? true,
      });
    }
  }, [open, category, reset]);

  const submit = handleSubmit(async (values) => {
    await onSubmit(values);
  });

  return (
    <Dialog open={open} onClose={onClose} fullWidth maxWidth="xs">
      <DialogTitle>{category ? "Editar categoria" : "Nova categoria"}</DialogTitle>

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
