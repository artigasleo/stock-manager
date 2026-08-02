import { AppBar, Box, Button, Toolbar, Typography } from "@mui/material";

import { useAuth } from "../../../modules/auth/context/AuthContext";

export function Header() {
  const { user, logout } = useAuth();

  return (
    <AppBar position="static" elevation={1}>
      <Toolbar sx={{ justifyContent: "space-between" }}>
        <Typography variant="h6">
          Stock Manager
        </Typography>

        {user && (
          <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
            <Typography variant="body2">{user.name}</Typography>
            <Button color="inherit" onClick={() => logout()}>
              Sair
            </Button>
          </Box>
        )}
      </Toolbar>
    </AppBar>
  );
}