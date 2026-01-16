export interface Seguimiento {
  id: number;
  accion: string;
  fecha: string;
  descripcion?: string | null;
  via?: string | null;
  estancia_id: number;
  created_at?: string;
  updated_at?: string;
}
