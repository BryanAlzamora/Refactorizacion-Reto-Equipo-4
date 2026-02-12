import type { Instructor } from "./Instructor";

export interface Empresa {
  id: number;
  nombre: string;
  cif: string;
  telefono: string;
  email: string;
  direccion: string;
  alumnos_count?: number;
  instructores?: Instructor[];
  created_at?: string;
  updated_at?: string;
}