import AddIcon from "@mui/icons-material/Add";
import DeleteOutlineIcon from "@mui/icons-material/DeleteOutlined";
import EditOutlinedIcon from "@mui/icons-material/EditOutlined";
import { Box, Button, Chip, IconButton, Stack, Typography } from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import toast from "react-hot-toast";

import { ConfirmDialog } from "../../../shared/components/ConfirmDialog";
import { CategoryFormDialog } from "../components/CategoryFormDialog";
import {
  createCategory,
  deleteCategory,
  listCategories,
  updateCategory,
} from "../services/categoryService";
import type { Category, CategoryFormValues } from "../types";

const QUERY_KEY = ["categories"];

export default function CategoryPage() {
  const queryClient = useQueryClient();

  const { data: categories = [], isLoading } = useQuery({
    queryKey: QUERY_KEY,
    queryFn: listCategories,
  });

  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing] = useState<Category | null>(null);
  const [deleting, setDeleting] = useState<Category | null>(null);

  const createMutation = useMutation({ mutationFn: createCategory });
  const updateMutation = useMutation({
    mutationFn: ({ id, values }: { id: number; values: CategoryFormValues }) =>
      updateCategory(id, values),
  });
  const deleteMutation = useMutation({ mutationFn: deleteCategory });

  const closeForm = () => {
    setFormOpen(false);
    setEditing(null);
  };

  const handleSubmit = async (values: CategoryFormValues) => {
    try {
      if (editing) {
        await updateMutation.mutateAsync({ id: editing.id, values });
        toast.success("Categoria atualizada.");
      } else {
        await createMutation.mutateAsync(values);
        toast.success("Categoria criada.");
      }
      await queryClient.invalidateQueries({ queryKey: QUERY_KEY });
      closeForm();
    } catch {
      toast.error("Não foi possível salvar a categoria.");
    }
  };

  const handleDelete = async () => {
    if (!deleting) return;

    try {
      await deleteMutation.mutateAsync(deleting.id);
      toast.success("Categoria excluída.");
      await queryClient.invalidateQueries({ queryKey: QUERY_KEY });
      setDeleting(null);
    } catch {
      toast.error("Não foi possível excluir a categoria.");
    }
  };

  const columns: GridColDef<Category>[] = [
    { field: "name", headerName: "Nome", flex: 1 },
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
          Categorias
        </Typography>

        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => {
            setEditing(null);
            setFormOpen(true);
          }}
        >
          Nova categoria
        </Button>
      </Stack>

      <Box sx={{ height: 500, backgroundColor: "background.paper", borderRadius: 2 }}>
        <DataGrid rows={categories} columns={columns} loading={isLoading} disableRowSelectionOnClick />
      </Box>

      <CategoryFormDialog open={formOpen} category={editing} onClose={closeForm} onSubmit={handleSubmit} />

      <ConfirmDialog
        open={!!deleting}
        title="Excluir categoria"
        description={`Tem certeza que deseja excluir "${deleting?.name}"?`}
        onCancel={() => setDeleting(null)}
        onConfirm={handleDelete}
        loading={deleteMutation.isPending}
      />
    </Box>
  );
}
