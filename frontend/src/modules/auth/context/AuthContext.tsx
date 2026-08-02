import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { createContext, useContext, type ReactNode } from "react";

import { fetchMe, login as loginRequest, logout as logoutRequest } from "../services/authService";
import type { User } from "../types";

type AuthContextValue = {
  user: User | null | undefined;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

const ME_QUERY_KEY = ["auth", "me"];

export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient();

  const { data: user, isLoading } = useQuery({
    queryKey: ME_QUERY_KEY,
    queryFn: fetchMe,
    retry: false,
    staleTime: Infinity,
  });

  const loginMutation = useMutation({
    mutationFn: ({ email, password }: { email: string; password: string }) =>
      loginRequest(email, password),
    onSuccess: (loggedUser) => {
      queryClient.setQueryData(ME_QUERY_KEY, loggedUser);
    },
  });

  const logoutMutation = useMutation({
    mutationFn: logoutRequest,
    onSuccess: () => {
      queryClient.setQueryData(ME_QUERY_KEY, null);
    },
  });

  return (
    <AuthContext.Provider
      value={{
        user,
        isLoading,
        login: async (email, password) => {
          await loginMutation.mutateAsync({ email, password });
        },
        logout: async () => {
          await logoutMutation.mutateAsync();
        },
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider");
  }

  return context;
}
