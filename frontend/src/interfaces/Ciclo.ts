import type { FamiliaProfesional } from "./FamiliaProfesional";

export interface Ciclo {
  id: number;
  nombre: string;
  familia_profesional_id: FamiliaProfesional["id"];
}
