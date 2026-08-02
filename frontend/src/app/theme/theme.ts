import { createTheme } from "@mui/material/styles";

export const theme = createTheme({
  palette: {
    primary: {
      main: "#3F4A34",
      light: "#5C6B4A",
      dark: "#2C3324",
      contrastText: "#F3EEE0",
    },
    secondary: {
      main: "#8B6F4E",
    },
    background: {
      default: "#F3EEE0",
      paper: "#FBF8F2",
    },
    text: {
      primary: "#2A2A24",
      secondary: "#5C5A4E",
    },
  },

  shape: {
    borderRadius: 10,
  },

  typography: {
    fontFamily: [
      "Inter",
      "Roboto",
      "Arial",
      "sans-serif",
    ].join(","),
  },
});