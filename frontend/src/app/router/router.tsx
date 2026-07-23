import { createBrowserRouter } from "react-router-dom";

import { DashboardPage } from "../../modules/dashboard/pages/DashboardPage";

export const router = createBrowserRouter([
  {
    path: "/",
    element: <DashboardPage />,
  },
]);