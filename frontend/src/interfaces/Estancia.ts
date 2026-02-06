import type { Empresa } from "./Empresa";
import type { TutorEgibide } from "./TutorEgibide";
import type { TutorEmpresa } from "./TutorEmpresa";

export interface Estancia {
  id: number;
  puesto: string | null;
  fecha_inicio: string | null;
  fecha_fin: string | null;
  horas_totales: number | null;
  alumno_id: number;
  tutor_id: number | null;
  instructor_id: number | null;
  empresa_id: number | null;
  empresa?: Empresa | null;
  tutor?: TutorEgibide | null;
  instructor?: TutorEmpresa | null;
}