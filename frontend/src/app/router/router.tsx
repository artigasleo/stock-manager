import { createBrowserRouter } from "react-router-dom";

import { AppLayout } from "../layouts/AppLayout";
import { DashboardPage } from "../../modules/dashboard/pages/DashboardPage";

export const router = createBrowserRouter([
  {
    element: <AppLayout />,
    children: [
      {
        path: "/",
        element: <DashboardPage />,
      },
    ],
  },
]);