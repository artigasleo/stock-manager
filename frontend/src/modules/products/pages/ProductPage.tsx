import AddIcon from "@mui/icons-material/Add";
import DeleteOutlineIcon from "@mui/icons-material/DeleteOutlined";
import EditOutlinedIcon from "@mui/icons-material/EditOutlined";
import { Box, Button, Chip, IconButton, Stack, Typography } from "@mui/material";
import { DataGrid, type GridColDef } from "@mui/x-data-grid";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import toast from "react-hot-toast";

import { ConfirmDialog } from "../../../shared/components/ConfirmDialog";
import { ProductFormDialog } from "../components/ProductFormDialog";
import { createProduct, deleteProduct, listProducts, updateProduct } from "../services/productService";
import type { Product, ProductFormValues } from "../types";

const QUERY_KEY = ["products"];

const currencyFormatter = new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" });

export default function ProductPage() {
  const queryClient = useQueryClient();

  const { data: products = [], isLoading } = useQuery({
    queryKey: QUERY_KEY,
    queryFn: listProducts,
  });

  const [formOpen, setFormOpen] = useState(false);
  const [editing, setEditing] = useState<Product | null>(null);
  const [deleting, setDeleting] = useState<Product | null>(null);

  const createMutation = useMutation({ mutationFn: createProduct });
  const updateMutation = useMutation({
    mutationFn: ({ id, values }: { id: number; values: ProductFormValues }) =>
      updateProduct(id, values),
  });
  const deleteMutation = useMutation({ mutationFn: deleteProduct });

  const closeForm = () => {
    setFormOpen(false);
    setEditing(null);
  };

  const handleSubmit = async (values: ProductFormValues) => {
    try {
      if (editing) {
        await updateMutation.mutateAsync({ id: editing.id, values });
        toast.success("Produto atualizado.");
      } else {
        await createMutation.mutateAsync(values);
        toast.success("Produto criado.");
      }
      await queryClient.invalidateQueries({ queryKey: QUERY_KEY });
      closeForm();
    } catch {
      toast.error("Não foi possível salvar o produto.");
    }
  };

  const handleDelete = async () => {
    if (!deleting) return;

    try {
      await deleteMutation.mutateAsync(deleting.id);
      toast.success("Produto excluído.");
      await queryClient.invalidateQueries({ queryKey: QUERY_KEY });
      setDeleting(null);
    } catch {
      toast.error("Não foi possível excluir o produto.");
    }
  };

  const columns: GridColDef<Product>[] = [
    { field: "code", headerName: "Código", width: 110 },
    { field: "name", headerName: "Nome", flex: 1 },
    {
      field: "category",
      headerName: "Categoria",
      width: 140,
      valueGetter: (_value, row) => row.category?.name ?? "",
    },
    {
      field: "supplier",
      headerName: "Fornecedor",
      width: 160,
      valueGetter: (_value, row) => row.supplier?.name ?? "—",
    },
    { field: "quantity", headerName: "Qtd.", width: 90 },
    {
      field: "sale_price",
      headerName: "Preço de venda",
      width: 140,
      valueGetter: (_value, row) => currencyFormatter.format(Number(row.sale_price)),
    },
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
          Produtos
        </Typography>

        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => {
            setEditing(null);
            setFormOpen(true);
          }}
        >
          Novo produto
        </Button>
      </Stack>

      <Box sx={{ height: 500, backgroundColor: "background.paper", borderRadius: 2 }}>
        <DataGrid rows={products} columns={columns} loading={isLoading} disableRowSelectionOnClick />
      </Box>

      <ProductFormDialog open={formOpen} product={editing} onClose={closeForm} onSubmit={handleSubmit} />

      <ConfirmDialog
        open={!!deleting}
        title="Excluir produto"
        description={`Tem certeza que deseja excluir "${deleting?.name}"?`}
        onCancel={() => setDeleting(null)}
        onConfirm={handleDelete}
        loading={deleteMutation.isPending}
      />
    </Box>
  );
}
