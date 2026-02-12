import type { RA } from "./RA";

export interface Asignatura {
  id: number;
  codigo_asignatura: string;
  nombre_asignatura: string;
  resultados_aprendizaje: RA[];
}
