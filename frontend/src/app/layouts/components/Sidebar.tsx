import { Box, Typography } from "@mui/material";

export function Sidebar() {
  return (
    <Box
      sx={{
        width: 260,
        borderRight: "1px solid #e5e7eb",
        p: 2,
      }}
    >
      <Typography fontWeight={600}>
        Menu
      </Typography>
    </Box>
  );
}