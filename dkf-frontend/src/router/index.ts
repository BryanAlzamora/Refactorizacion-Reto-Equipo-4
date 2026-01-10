import LoginView from "@/pages/Authentication/LoginView.vue";
import DashboardView from "@/pages/DashboardView.vue";
import { useAuthStore } from "@/stores/auth";
import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/login",
      name: "login",
      component: LoginView,
      meta: { guest: true },
    },
    {
      path: "/",
      component: DashboardView,
      meta: { requiresAuth: true },
      children: [
        // Grupo de rutas para Alumnos
        {
          path: "alumno/mis-datos",
          name: "alumno-datos",
          components: {
            main: () => import("@/pages/Alumno/misDatosView.vue"),
          },
        },
        {
          path: "alumno/empresa",
          name: "alumno-empresa",
          components: {
            main: () => import("@/pages/Alumno/empresa.vue"),
          },
        },
      ],
    },
  ],
});

router.beforeEach((to) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.token) {
    return { name: "login" };
  }

  if (to.meta.guest && auth.token) {
    return { name: "dashboard" };
  }
});

export default router;
