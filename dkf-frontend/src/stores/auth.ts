import type { User } from "@/interfaces/User";
import { defineStore } from "pinia";
import { ref } from "vue";
import router from "@/router";

export const useAuthStore = defineStore('auth', () => {
  const currentUser = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('token'));

  async function login(email: string, password: string) {
    try {
      const response = await fetch('http://localhost:8000/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      });

      if(!response.ok) return false;

      const data = await response.json();

      token.value = data.token;
      localStorage.setItem('token', data.token);

      currentUser.value = data.user as User;

      router.replace('/');
      return true;

    } catch (error) {
      console.error(error);
      return false
    }
  }

  function logout() {
    localStorage.removeItem('token');
    currentUser.value = null;
  }

  async function fetchCurrentUser() {
    if (!token.value) return;

    try {
      const response = await fetch('http://localhost:8000/api/user', {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        logout();
        return;
      }

      const user = await response.json();
      currentUser.value = user as User;

    } catch (error) {
      console.error(error);
      logout();
    }
  }

  return { currentUser, token, login, logout, fetchCurrentUser }
});
