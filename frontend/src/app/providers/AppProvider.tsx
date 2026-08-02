import { ThemeProvider } from "@mui/material/styles";
import CssBaseline from "@mui/material/CssBaseline";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { useState, type ReactNode } from "react";
import { Toaster } from "react-hot-toast";
import { theme } from "../theme/theme";
import { AuthProvider } from "../../modules/auth/context/AuthContext";

type Props = {
  children: ReactNode;
};

export function AppProvider({ children }: Props) {
  const [queryClient] = useState(() => new QueryClient());

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <Toaster position="top-right" />
          {children}
        </AuthProvider>
      </QueryClientProvider>
    </ThemeProvider>
  );
}