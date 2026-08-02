import CategoryOutlinedIcon from "@mui/icons-material/CategoryOutlined";
import DashboardOutlinedIcon from "@mui/icons-material/DashboardOutlined";
import Inventory2OutlinedIcon from "@mui/icons-material/Inventory2Outlined";
import LocalShippingOutlinedIcon from "@mui/icons-material/LocalShippingOutlined";
import { Box, List, ListItemButton, ListItemIcon, ListItemText, Typography } from "@mui/material";
import { NavLink } from "react-router-dom";

const navItems = [
  { label: "Dashboard", to: "/", icon: <DashboardOutlinedIcon /> },
  { label: "Categorias", to: "/categories", icon: <CategoryOutlinedIcon /> },
  { label: "Produtos", to: "/products", icon: <Inventory2OutlinedIcon /> },
  { label: "Fornecedores", to: "/suppliers", icon: <LocalShippingOutlinedIcon /> },
];

export function Sidebar() {
  return (
    <Box
      sx={{
        width: 260,
        borderRight: "1px solid",
        borderColor: "divider",
        p: 2,
      }}
    >
      <Typography sx={{ fontWeight: 600, mb: 1 }}>
        Menu
      </Typography>

      <List disablePadding>
        {navItems.map((item) => (
          <ListItemButton
            key={item.to}
            component={NavLink}
            to={item.to}
            end={item.to === "/"}
            sx={{
              borderRadius: 1,
              mb: 0.5,
              "&.active": {
                backgroundColor: "primary.main",
                color: "primary.contrastText",
                "& .MuiListItemIcon-root": {
                  color: "primary.contrastText",
                },
              },
            }}
          >
            <ListItemIcon sx={{ minWidth: 36 }}>{item.icon}</ListItemIcon>
            <ListItemText primary={item.label} />
          </ListItemButton>
        ))}
      </List>
    </Box>
  );
}
