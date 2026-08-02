import AddIcon from "@mui/icons-material/Add";
import DeleteOutlineIcon from "@mui/icons-material/DeleteOutlined";
import EditOutlinedIcon from "@mui/icons-material/EditOutlined";
import { Box, Button, Chip, IconButton, Stack, Typography } from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import toast from "react-hot-toast";

import { ConfirmDialog } from "../../../shared/components/ConfirmDialog";
import { SupplierFormDialog } from "../components/SupplierFormDialog";
import {
  createSupplier,
  deleteSupplier,
  listSuppliers,
  updateSupplier,
} from "../services/supplierService";
import type { Supplier, SupplierFormValues } from "../types";

const QUERY_KEY = ["suppliers"];

export default function SupplierPage() {
  const queryClient = useQueryClient();

  const { data: suppliers = [], isLoading } = useQuery({
    queryKey: QUERY_KEY,
    queryFn: listSuppliers,
  });

  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing] = useState<Supplier | null>(null);
  const [deleting, setDeleting] = useState<Supplier | null>(null);

  const createMutation = useMutation({ mutationFn: createSupplier });
  const updateMutation = useMutation({
    mutationFn: ({ id, values }: { id: number; values: SupplierFormValues }) =>
      updateSupplier(id, values),
  });
  const deleteMutation = useMutation({ mutationFn: deleteSupplier });

  const closeForm = () => {
    setFormOpen(false);
    setEditing(null);
  };

  const handleSubmit = async (values: SupplierFormValues) => {
    try {
      if (editing) {
        await updateMutation.mutateAsync({ id: editing.id, values });
        toast.success("Fornecedor atualizado.");
      } else {
        await createMutation.mutateAsync(values);
        toast.success("Fornecedor criado.");
      }
      await queryClient.invalidateQueries({ queryKey: QUERY_KEY });
      closeForm();
    } catch {
      toast.error("Não foi possível salvar o fornecedor.");
    }
  };

  const handleDelete = async () => {
    if (!deleting) return;

    try {
      await deleteMutation.mutateAsync(deleting.id);
      toast.success("Fornecedor excluído.");
      await queryClient.invalidateQueries({ queryKey: QUERY_KEY });
      setDeleting(null);
    } catch {
      toast.error("Não foi possível excluir o fornecedor.");
    }
  };

  const columns: GridColDef<Supplier>[] = [
    { field: "name", headerName: "Nome", flex: 1 },
    { field: "document", headerName: "CNPJ/CPF", width: 160 },
    { field: "phone", headerName: "Telefone", width: 140 },
    { field: "email", headerName: "E-mail", flex: 1 },
    {
      field: "active",
      headerName: "Status",
      width: 120,
      renderCell: ({ value }) => (
        <Chip label={value ? "Ativo" : "Inativo"} color={value ? "success" : "default"} size="small" />
      ),
    },
    {
      field: "actions",
      headerName: "Ações",
      width: 110,
      sortable: false,
      renderCell: ({ row }) => (
        <>
          <IconButton
            size="small"
            onClick={() => {
              setEditing(row);
              setFormOpen(true);
            }}
          >
            <EditOutlinedIcon fontSize="small" />
          </IconButton>
          <IconButton size="small" onClick={() => setDeleting(row)}>
            <DeleteOutlineIcon fontSize="small" />
          </IconButton>
        </>
      ),
    },
  ];

  return (
    <Box>
      <Stack direction="row" sx={{ justifyContent: "space-between", alignItems: "center", mb: 2 }}>
        <Typography variant="h5" sx={{ fontWeight: 600 }}>
          Fornecedores
        </Typography>

        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => {
            setEditing(null);
            setFormOpen(true);
          }}
        >
          Novo fornecedor
        </Button>
      </Stack>

      <Box sx={{ height: 500, backgroundColor: "background.paper", borderRadius: 2 }}>
        <DataGrid rows={suppliers} columns={columns} loading={isLoading} disableRowSelectionOnClick />
      </Box>

      <SupplierFormDialog open={formOpen} supplier={editing} onClose={closeForm} onSubmit={handleSubmit} />

      <ConfirmDialog
        open={!!deleting}
        title="Excluir fornecedor"
        description={`Tem certeza que deseja excluir "${deleting?.name}"?`}
        onCancel={() => setDeleting(null)}
        onConfirm={handleDelete}
        loading={deleteMutation.isPending}
      />
    </Box>
  );
}
