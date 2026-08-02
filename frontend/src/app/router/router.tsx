import { createBrowserRouter } from "react-router-dom";

import { AppLayout } from "../layouts/AppLayout";
import DashboardPage from "../../modules/dashboard/pages/DashboardPage";
import CategoryPage from "../../modules/categories/pages/CategoryPage";
import LoginPage from "../../modules/auth/pages/LoginPage";
import { ProtectedRoute } from "../../shared/components/ProtectedRoute";

export const router = createBrowserRouter([
    {
        path: "/login",
        element: <LoginPage />,
    },
    {
        element: <ProtectedRoute />,
        children: [
            {
                element: <AppLayout />,
                children: [
                    {
                        path: "/",
                        element: <DashboardPage />,
                    },
                    {
                        path: "/categories",
                        element: <CategoryPage />,
                    },
                ],
            },
        ],
    },
]);
