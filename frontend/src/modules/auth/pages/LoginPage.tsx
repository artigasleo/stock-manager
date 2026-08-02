import { zodResolver } from "@hookform/resolvers/zod";
import { Box, Button, Paper, TextField, Typography } from "@mui/material";
import { isAxiosError } from "axios";
import toast from "react-hot-toast";
import { useForm } from "react-hook-form";
import { useNavigate } from "react-router-dom";
import { z } from "zod";

import { useAuth } from "../context/AuthContext";

const loginSchema = z.object({
  email: z.string().email("Informe um e-mail válido"),
  password: z.string().min(1, "A senha é obrigatória"),
});

type LoginFormData = z.infer<typeof loginSchema>;

export default function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginFormData) => {
    try {
      await login(data.email, data.password);
      navigate("/", { replace: true });
    } catch (error) {
      if (isAxiosError(error) && error.response?.status === 422) {
        toast.error("E-mail ou senha inválidos.");
      } else {
        console.error(error);
        toast.error("Não foi possível conectar ao servidor. Tente novamente.");
      }
    }
  };

  return (
    <Box
      sx={{
        minHeight: "100vh",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        backgroundColor: "background.default",
      }}
    >
      <Paper elevation={3} sx={{ p: 4, width: 360 }}>
        <Typography variant="h5" sx={{ fontWeight: 600, mb: 3 }}>
          Stock Manager
        </Typography>

        <Box component="form" onSubmit={handleSubmit(onSubmit)} noValidate>
          <TextField
            {...register("email")}
            label="E-mail"
            type="email"
            fullWidth
            margin="normal"
            error={!!errors.email}
            helperText={errors.email?.message}
          />

          <TextField
            {...register("password")}
            label="Senha"
            type="password"
            fullWidth
            margin="normal"
            error={!!errors.password}
            helperText={errors.password?.message}
          />

          <Button
            type="submit"
            variant="contained"
            fullWidth
            sx={{ mt: 2 }}
            disabled={isSubmitting}
          >
            Entrar
          </Button>
        </Box>
      </Paper>
    </Box>
  );
}
