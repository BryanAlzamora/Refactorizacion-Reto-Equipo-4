export interface EntregaCuaderno {
  id: number;
  fecha_creacion: Date;
  fecha_limite: Date;
  tutor_id: number;
  descripcion?: string;
}
