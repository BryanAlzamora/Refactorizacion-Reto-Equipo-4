<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultadosAprendizajeSeeder extends Seeder {
    public function run() {
        $resultadosAprendizaje = [
            'SI' => [
                'Evalúa sistemas informáticos, identificando sus componentes y características',
                'Instala sistemas operativos, planificando el proceso e interpretando documentación técnica.',
                'Gestiona la información del sistema, identificando las estructuras de almacenamiento y aplicando medidas para asegurar la integridad de los datos.',
                'Gestiona sistemas operativos, utilizando comandos y herramientas gráficas y evaluando las necesidades del sistema.',
                'Interconecta sistemas en red, configurando dispositivos y protocolos.',
                'Opera sistemas en red, gestionando sus recursos e identificando las restricciones de seguridad existentes.',
                'Elabora documentación, valorando y utilizando aplicaciones informáticas de propósito general.',
            ],
            'BD' => [
                'Reconoce los elementos de las bases de datos, analizando sus funciones y valorando la utilidad de los sistemas gestores.',
                'Crea bases de datos, definiendo su estructura y las características de sus elementos según el modelo relacional.',
                'Consulta la información almacenada en una base de datos, empleando asistentes, herramientas gráficas y el lenguaje de manipulación de datos.',
                'Modifica la información almacenada en la base de datos, utilizando asistentes, herramientas gráficas y el lenguaje de manipulación de datos',
                'Desarrolla procedimientos almacenados, evaluando y utilizando las sentencias del lenguaje incorporado en el sistema gestor de bases de datos.',
                'Diseña modelos relacionales normalizados, interpretando diagramas entidad/relación.',
                'Gestiona la información almacenada en bases de datos objeto-relacionales, evaluando y utilizando las posibilidades que proporciona el sistema gestor.',
                'Reconoce la legislación y normativa sobre seguridad y protección de datos valorando su importancia.',
            ],
            'PRO' => [
                'Reconoce la estructura de un programa informático, identificando y relacionando los elementos propios del lenguaje de programación utilizado.',
                'Escribe y prueba programas sencillos, reconociendo y aplicando los fundamentos de la programación orientada a objetos.',
                'Escribe y depura código, analizando y utilizando las estructuras de control del lenguaje.',
                'Desarrolla programas organizados en clases, analizando y aplicando los principios de la programación orientada a objetos.',
                'Realiza operaciones de entrada y salida de información, utilizando procedimientos específicos del lenguaje y librerías de clases.',
                'Escribe programas que manipulan información, seleccionando y utilizando tipos avanzados de datos.',
                'Desarrolla programas, aplicando características avanzadas de los lenguajes orientados a objetos y del entorno de programación.',
                'Utiliza bases de datos orientadas a objetos, analizando sus características y aplicando técnicas para mantener la persistencia de la información.',
                'Gestiona información almacenada en bases de datos relacionales, manteniendo la integridad y consistencia de los datos.',
            ],
            'LMSGI' => [
                'Reconoce las características de lenguajes de marcas, analizando e interpretando fragmentos de código.',
                'Utiliza lenguajes de marcas para la transmisión de información a través de la Web, analizando la estructura de los documentos e identificando sus elementos.',
                'Genera canales de contenidos, analizando y utilizando tecnologías de sindicación.',
                'Establece mecanismos de validación para documentos XML, utilizando métodos para definir su sintaxis y estructura.',
                'Realiza conversiones sobre documentos XML, utilizando técnicas y herramientas de procesamiento.',
                'Gestiona información en formato XML, analizando y utilizando tecnologías de almacenamiento y lenguajes de consulta.',
                'Opera con sistemas empresariales de gestión de información, realizando tareas de importación, integración, aseguramiento y extracción de la información.',
            ],
            'ED' => [
                'Reconoce los elementos y herramientas que intervienen en el desarrollo de un programa informático, analizando sus características y las fases en las que actúan hasta llegar a su puesta en funcionamiento.',
                'Evalúa entornos integrados de desarrollo, analizando sus características para editar código fuente y generar ejecutables.',
                'Verifica el funcionamiento de programas, diseñando y realizando pruebas.',
                'Optimiza código, empleando las herramientas disponibles en el entorno de desarrollo.',
                'Genera diagramas de clases, valorando su importancia en el desarrollo de aplicaciones y empleando las herramientas disponibles en el entorno.',
                'Genera diagramas de comportamiento, valorando su importancia en el desarrollo de aplicaciones y empleando las herramientas disponibles en el entorno.',
            ],
            'DWEC' => [
                'Selecciona las arquitecturas y tecnologías de programación sobre clientes web, identificando y analizando las capacidades y características de cada una.',
                'Escribe sentencias simples, aplicando la sintaxis del lenguaje y verificando su ejecución sobre navegadores web.',
                'Escribe código, identificando y aplicando las funcionalidades aportadas por los objetos predefinidos del lenguaje.',
                'Programa código para clientes web analizando y utilizando estructuras definidas por el usuario.',
                'Desarrolla aplicaciones web interactivas integrando mecanismos de manejo de eventos.',
                'Desarrolla aplicaciones web analizando y aplicando las características del modelo de objetos del documento.',
                'Desarrolla aplicaciones web dinámicas, reconociendo y aplicando mecanismos de comunicación asíncrona entre cliente y servidor.',
            ],
            'DWES' => [
                'Selecciona las arquitecturas y tecnologías de programación web en entorno servidor, analizando sus capacidades y características propias.',
                'Escribe sentencias ejecutables por un servidor web, reconociendo y aplicando procedimientos de integración del código en lenguajes de marcas.',
                'Escribe bloques de sentencias embebidos en lenguajes de marcas, seleccionando y utilizando las estructuras de programación.',
                'Desarrolla aplicaciones web embebidas en lenguajes de marcas, analizando e incorporando funcionalidades según especificaciones.',
                'Desarrolla aplicaciones web identificando y aplicando mecanismos para separar el código de presentación de la lógica de negocio.',
                'Desarrolla aplicaciones de acceso a almacenes de datos, aplicando medidas para mantener la seguridad y la integridad de la información.',
                'Desarrolla servicios web analizando su funcionamiento e implantando la estructura de sus componentes.',
                'Genera páginas web dinámicas analizando y utilizando tecnologías del servidor web que añadan código al lenguaje de marcas.',
                'Desarrolla aplicaciones web híbridas seleccionando y utilizando librerías de código y repositorios heterogéneos de información.',
            ],
            'DAW' => [
                'Implanta arquitecturas web analizando y aplicando criterios de funcionalidad.',
                'Gestiona servidores web, evaluando y aplicando criterios de configuración para el acceso seguro a los servicios.',
                'Implanta aplicaciones web en servidores de aplicaciones, evaluando y aplicando criterios de configuración para su funcionamiento seguro.',
                'Administra servidores de transferencia de archivos, evaluando y aplicando criterios de configuración que garanticen la disponibilidad del servicio.',
                'Verifica la ejecución de aplicaciones web, comprobando los parámetros de configuración de servicios de red.',
                'Elabora la documentación de la aplicación web, evaluando y seleccionando herramientas de generación de documentación y control de versiones',
            ],
            'DIW' => [
                'Planifica la creación de una interfaz web valorando y aplicando especificaciones de diseño.',
                'Crea interfaces web homogéneos definiendo y aplicando estilos.',
                'Prepara archivos multimedia para la Web, analizando sus características y manejando herramientas especificas.',
                'Integra contenido multimedia en documentos web, valorando su aportación y seleccionando adecuadamente los elementos interactivos.',
                'Desarrolla interfaces web accesibles, analizando las pautas establecidas y aplicando técnicas de verificación.',
                'Desarrolla interfaces web amigables, analizando y aplicando las pautas de usabilidad establecidas.',
            ],
        ];

        foreach ($resultadosAprendizaje as $codigoAsignatura => $resultados) {
            // Obtener codigo de asignatura
            $asignaturaId = DB::table('asignaturas')
                ->where('codigo_asignatura', $codigoAsignatura)
                ->value('id');

            if ($asignaturaId) {
                foreach ($resultados as $descripcion) {
                    DB::table('resultados_aprendizaje')->insert([
                        'descripcion' => $descripcion,
                        'asignatura_id' => $asignaturaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
