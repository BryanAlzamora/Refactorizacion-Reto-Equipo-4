export interface Alumno {
  id: number;
  nombre: string;
  apellidos: string;
  email?: string;
  telefono?: string;
  fecha: string;
  ciudad?: string;
  user_id: number;
  created_at: string;
  updated_at: string;
  pivot?: {
    id: number;
    alumno_id: number;
    tutor_id: number;
    instructor_id: number;
    empresa_id: number;
    curso_id: number;
    puesto: string;
    fecha_inicio: string;
    fecha_fin: string;
    horas_totales: number;
    created_at: string;
    updated_at: string;
  };
}
