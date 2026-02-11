import type { Alumno } from "./Alumno";

export interface AlumnoEntrega {
  id: number;
  url_entrega: string;
  fecha_entrega: Date;
  alumno_id: number;
  entrega_id: number;
  observaciones?: string;
  feedback?: 'Bien' | 'Regular' | 'Debe mejorar';
  alumno: Alumno;
}
