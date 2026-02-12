export interface Instructor {
  id: number;
  nombre: string;
  apellidos: string;
  telefono: string | null;
  ciudad: string | null;
  empresa_id: number;
  user_id: number | null;
  created_at?: string;
  updated_at?: string;
  empresa?: {
    id: number;
    nombre: string;
  };
}