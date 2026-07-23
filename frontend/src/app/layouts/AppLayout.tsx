import { Box } from "@mui/material";
import { Outlet } from "react-router-dom";

import { Header } from "./components/Header";
import { Sidebar } from "./components/Sidebar";

export function AppLayout() {
  return (
    <Box sx={{ minHeight: "100vh" }}>
      <Header />

      <Box
        sx={{
          display: "flex",
          minHeight: "calc(100vh - 64px)",
        }}
      >
        <Sidebar />

        <Box
          component="main"
          sx={{
            flex: 1,
            p: 3,
            backgroundColor: "background.default",
          }}
        >
          <Outlet />
        </Box>
      </Box>
    </Box>
  );
}