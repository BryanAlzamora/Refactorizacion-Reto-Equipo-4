export interface Empresa {
  id: number;
  nombre: string;
  cif: string;
  telefono: string;
  email: string;
  direccion: string;
  alumnos_count?: number;
  created_at?: string;
  updated_at?: string;
}
