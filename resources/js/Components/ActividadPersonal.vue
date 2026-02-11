<template>
  <div class="relative px-6 py-8 bg-white shadow-lg sm:rounded-3xl sm:p-12">
    <h1 class="text-3xl font-semibold mb-6 text-center">Actividad del Personal</h1>

    <!-- Tabs (solo admin ve las pestañas adicionales) -->
    <div class="flex border-b border-gray-200 mb-6" v-if="isAdmin">
      <button
        @click="tabActivo = 'actividades'"
        :class="tabActivo === 'actividades' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
      >
        Actividades
      </button>
      <button
        @click="abrirNotificaciones"
        :class="tabActivo === 'notificaciones' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
      >
        Notificaciones de Turno
      </button>
      <button
        @click="abrirEvaluacion"
        :class="tabActivo === 'evaluacion' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
      >
        Evaluacion
      </button>
    </div>

    <!-- =================== TAB: NOTIFICACIONES DE TURNO =================== -->
    <div v-show="tabActivo === 'notificaciones'">
      <div class="mb-4 bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input type="date" v-model="filtrosNotif.fecha_inicio"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input type="date" v-model="filtrosNotif.fecha_fin"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
            <select v-model="filtrosNotif.sucursal_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
              <option :value="null">Todas</option>
              <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select v-model="filtrosNotif.tipo"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
              <option :value="null">Todos</option>
              <option value="faltante">Faltante</option>
              <option value="excedente">Excedente</option>
              <option value="horneado">Horneado</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select v-model="filtrosNotif.atendida"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
              <option :value="null">Todos</option>
              <option value="0">Sin atender</option>
              <option value="1">Atendidas</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button @click="cargarNotificaciones" :disabled="cargandoNotif"
            class="bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors disabled:opacity-50">
            <span v-if="cargandoNotif">Cargando...</span>
            <span v-else>Filtrar</span>
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Fecha/Hora</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Trabajador</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Sucursal</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Tipo</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Descripción</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Atendida</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="notif in notificaciones" :key="notif.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 border-b">
                <div class="text-sm font-medium text-gray-900">{{ notif.fecha }}</div>
                <div class="text-xs text-gray-500">{{ notif.hora }}</div>
              </td>
              <td class="px-4 py-3 border-b text-sm text-gray-700">{{ notif.trabajador }}</td>
              <td class="px-4 py-3 border-b text-sm text-gray-700">{{ notif.sucursal }}</td>
              <td class="px-4 py-3 border-b">
                <span :class="getTipoNotifBadge(notif.tipo)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ notif.tipo }}
                </span>
              </td>
              <td class="px-4 py-3 border-b text-sm text-gray-700">{{ notif.descripcion }}</td>
              <td class="px-4 py-3 border-b">
                <span v-if="notif.atendida" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                  Atendida
                </span>
                <button v-else @click="atenderNotificacion(notif)"
                  class="px-3 py-1 text-xs font-medium bg-orange-500 text-white rounded-full hover:bg-orange-600 transition-colors">
                  Atender
                </button>
              </td>
            </tr>
            <tr v-if="notificaciones.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                No se encontraron notificaciones para los filtros seleccionados
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- =================== TAB: ACTIVIDADES =================== -->
    <div v-show="tabActivo === 'actividades'">

      <!-- Filtros -->
      <div class="mb-6 bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input
              type="date"
              v-model="filtros.fecha_inicio"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input
              type="date"
              v-model="filtros.fecha_fin"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
            <select
              v-model="filtros.sucursal_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option :value="null">Todas las sucursales</option>
              <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
                {{ sucursal.nombre }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
            <select
              v-model="filtros.usuario_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option :value="null">Todos los usuarios</option>
              <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                {{ usuario.nombre_completo }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select
              v-model="filtros.tipo_actividad"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option :value="null">Todos los tipos</option>
              <option value="produccion">Produccion</option>
              <option value="venta">Ventas</option>
              <option value="asistencia">Asistencia</option>
              <option value="inventario">Inventario</option>
              <option value="gasto">Gastos</option>
              <option value="corte_caja">Corte de Caja</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="aplicarFiltro"
            :disabled="cargando"
            class="bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors duration-200 disabled:opacity-50"
          >
            <span v-if="cargando">Cargando...</span>
            <span v-else>Filtrar</span>
          </button>
        </div>
      </div>

      <!-- Tarjetas Resumen -->
      <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
          <div class="text-sm text-blue-600 font-medium">Total Actividades</div>
          <div class="text-2xl font-bold text-blue-800">{{ resumen.total_actividades }}</div>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
          <div class="text-sm text-yellow-600 font-medium">Ventas Manuales</div>
          <div class="text-2xl font-bold text-yellow-800">{{ resumen.ventas?.manuales?.cantidad || 0 }}</div>
          <div class="text-sm text-yellow-600">${{ formatNumber(resumen.ventas?.manuales?.total || 0) }}</div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
          <div class="text-sm text-green-600 font-medium">Ventas Sistema (POS)</div>
          <div class="text-2xl font-bold text-green-800">{{ resumen.ventas?.sistema?.cantidad || 0 }}</div>
          <div class="text-sm text-green-600">${{ formatNumber(resumen.ventas?.sistema?.total || 0) }}</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
          <div class="text-sm text-purple-600 font-medium">Asistencias</div>
          <div class="text-2xl font-bold text-purple-800">{{ resumen.por_tipo?.asistencia || 0 }}</div>
        </div>
      </div>

      <!-- Tabla de Actividades -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Fecha/Hora</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Usuario</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Sucursal</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Tipo</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Subtipo</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Detalles</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="actividad in actividadesPaginadas"
              :key="actividad.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3 border-b">
                <div class="text-sm font-medium text-gray-900">{{ actividad.fecha }}</div>
                <div class="text-xs text-gray-500">{{ actividad.hora }}</div>
              </td>
              <td class="px-4 py-3 border-b text-sm text-gray-700">{{ actividad.usuario }}</td>
              <td class="px-4 py-3 border-b text-sm text-gray-700">{{ actividad.sucursal }}</td>
              <td class="px-4 py-3 border-b">
                <span :class="getTipoBadgeClass(actividad.tipo)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ actividad.tipo_label }}
                </span>
              </td>
              <td class="px-4 py-3 border-b">
                <span :class="getSubtipoBadgeClass(actividad.tipo, actividad.subtipo)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ actividad.subtipo_label }}
                </span>
              </td>
              <td class="px-4 py-3 border-b text-sm text-gray-700">
                <span
                  v-if="actividad.tipo === 'venta' && actividad.extra?.productos?.length"
                  :title="actividad.extra.productos.map(p => `${p.nombre} x${p.cantidad} = $${(p.cantidad * p.precio_unitario).toFixed(2)}`).join('\n')"
                  class="cursor-help underline decoration-dotted"
                >
                  {{ actividad.detalles }}
                </span>
                <span v-else>{{ actividad.detalles }}</span>
              </td>
            </tr>
            <tr v-if="actividades.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                No se encontraron actividades para los filtros seleccionados
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginacion -->
      <div v-if="totalPaginas > 1" class="mt-4 flex justify-center items-center space-x-2">
        <button
          @click="paginaActual = Math.max(1, paginaActual - 1)"
          :disabled="paginaActual === 1"
          class="px-3 py-1 border rounded-md disabled:opacity-50 hover:bg-gray-100"
        >
          Anterior
        </button>
        <span class="text-sm text-gray-600">
          Pagina {{ paginaActual }} de {{ totalPaginas }}
        </span>
        <button
          @click="paginaActual = Math.min(totalPaginas, paginaActual + 1)"
          :disabled="paginaActual === totalPaginas"
          class="px-3 py-1 border rounded-md disabled:opacity-50 hover:bg-gray-100"
        >
          Siguiente
        </button>
      </div>

      <!-- Total de registros -->
      <div class="mt-2 text-center text-sm text-gray-500">
        Mostrando {{ actividadesPaginadas.length }} de {{ actividades.length }} actividades
      </div>

    </div>

    <!-- =================== TAB: EVALUACION =================== -->
    <div v-show="tabActivo === 'evaluacion'">
      <!-- Filtros -->
      <div class="mb-6 bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input type="date" v-model="filtrosEval.fecha_inicio"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input type="date" v-model="filtrosEval.fecha_fin"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
            <select v-model="filtrosEval.sucursal_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
              <option :value="null">Todas</option>
              <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
            </select>
          </div>
          <div class="flex items-end gap-2">
            <button @click="cargarEvaluacion" :disabled="cargandoEval"
              class="flex-1 bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors disabled:opacity-50">
              <span v-if="cargandoEval">Cargando...</span>
              <span v-else>Filtrar</span>
            </button>
            <button @click="exportarEvaluacion"
              class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
              CSV
            </button>
          </div>
        </div>
      </div>

      <!-- Tarjetas Resumen (mismo estilo que actividades) -->
      <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
          <div class="text-sm text-yellow-600 font-medium">Mejor Empleado</div>
          <div class="text-xl font-bold text-yellow-800 truncate">{{ evalMejores?.mejor_general?.nombre || '-' }}</div>
          <div class="text-sm text-yellow-600">{{ evalMejores?.mejor_general?.total_puntos || 0 }} puntos</div>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
          <div class="text-sm text-blue-600 font-medium">Mas Ventas</div>
          <div class="text-xl font-bold text-blue-800 truncate">{{ evalMejores?.mas_ventas?.nombre || '-' }}</div>
          <div class="text-sm text-blue-600">{{ evalMejores?.mas_ventas?.total_ventas || 0 }} ventas</div>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
          <div class="text-sm text-orange-600 font-medium">Mas Horneados</div>
          <div class="text-xl font-bold text-orange-800 truncate">{{ evalMejores?.mas_horneados?.nombre || '-' }}</div>
          <div class="text-sm text-orange-600">{{ evalMejores?.mas_horneados?.total_horneados || 0 }} lotes</div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
          <div class="text-sm text-purple-600 font-medium">Promedio Horas</div>
          <div class="text-xl font-bold text-purple-800">{{ evalEstadisticas?.promedio_horas_trabajadas || 0 }}h</div>
          <div class="text-sm text-purple-600">{{ evalEstadisticas?.total_empleados || 0 }} empleados</div>
        </div>
      </div>

      <!-- Tabla Ranking -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">#</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Empleado</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Sucursal</th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-b">Puntos</th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-b">Ventas</th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-b">Horneados</th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-b">Notif.</th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-b">Horas</th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-b">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="empleado in evalRanking" :key="empleado.user_id"
              :class="{
                'bg-yellow-50': empleado.posicion === 1,
                'bg-gray-50': empleado.posicion === 2,
                'bg-orange-50': empleado.posicion === 3,
              }"
              class="hover:bg-gray-100 transition-colors">
              <td class="px-4 py-3 border-b">
                <span :class="{
                  'text-yellow-600 font-bold text-lg': empleado.posicion === 1,
                  'text-gray-500 font-semibold': empleado.posicion === 2,
                  'text-orange-600 font-semibold': empleado.posicion === 3,
                  'text-gray-700': empleado.posicion > 3,
                }" v-html="empleado.posicion === 1 ? '&#127942;' : empleado.posicion === 2 ? '&#129352;' : empleado.posicion === 3 ? '&#129353;' : empleado.posicion">
                </span>
              </td>
              <td class="px-4 py-3 border-b">
                <div class="text-sm font-medium text-gray-900">{{ empleado.nombre }}</div>
                <div class="text-xs text-gray-500">{{ empleado.email }}</div>
              </td>
              <td class="px-4 py-3 border-b text-sm text-gray-700">{{ empleado.sucursal }}</td>
              <td class="px-4 py-3 border-b text-center">
                <span class="px-2 py-1 text-sm font-semibold rounded-full"
                  :class="empleado.total_puntos >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ empleado.total_puntos }}
                </span>
              </td>
              <td class="px-4 py-3 border-b text-center text-sm text-gray-700">{{ empleado.total_ventas }}</td>
              <td class="px-4 py-3 border-b text-center text-sm text-gray-700">{{ empleado.total_horneados }}</td>
              <td class="px-4 py-3 border-b text-center text-sm text-gray-700">{{ empleado.notificaciones_atendidas }}</td>
              <td class="px-4 py-3 border-b text-center text-sm text-gray-700">{{ empleado.horas_trabajadas }}h</td>
              <td class="px-4 py-3 border-b text-center">
                <button @click="verDetalleEmpleado(empleado.user_id)"
                  class="text-orange-600 hover:text-orange-800 text-sm font-medium">
                  Ver detalle
                </button>
              </td>
            </tr>
            <tr v-if="!evalRanking?.length">
              <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                No hay datos para mostrar en el rango de fechas seleccionado.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal Detalle Empleado -->
      <div v-if="mostrarDetalleEmpleado"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="cerrarDetalleEmpleado">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
              Detalle de {{ empleadoDetalle?.empleado?.nombre }}
            </h3>
            <button @click="cerrarDetalleEmpleado" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="p-6" v-if="empleadoDetalle">
            <div class="mb-6">
              <p class="text-gray-600">{{ empleadoDetalle.empleado?.email }}</p>
              <p class="text-gray-600">Sucursal: {{ empleadoDetalle.empleado?.sucursal }}</p>
              <!-- Indicador de tiempo real o sin datos -->
              <div v-if="empleadoDetalle.datos_ranking?.total_puntos === 0" class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-xs text-yellow-700">
                  <strong>Sin datos:</strong> Este empleado no tiene registros de actividad en el período seleccionado.
                </p>
              </div>
              <div v-else-if="empleadoDetalle.metricas?.es_tiempo_real" class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-xs text-blue-700">
                  <strong>Nota:</strong> Los datos se calculan en tiempo real basados en ventas, check-ins, horneados y notificaciones registradas en el período seleccionado.
                </p>
              </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6" :class="{ 'opacity-50': empleadoDetalle.datos_ranking?.total_puntos === 0 }">
              <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-200">
                <p :class="empleadoDetalle.datos_ranking?.total_puntos === 0 ? 'text-gray-400' : 'text-orange-600'" class="text-2xl font-bold">{{ empleadoDetalle.datos_ranking?.total_puntos || empleadoDetalle.metricas?.total_puntos || 0 }}</p>
                <p class="text-xs text-gray-500">Puntos Totales</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-200">
                <p :class="empleadoDetalle.datos_ranking?.total_puntos === 0 ? 'text-gray-400' : 'text-blue-600'" class="text-2xl font-bold">{{ empleadoDetalle.datos_ranking?.total_ventas || empleadoDetalle.metricas?.resumen?.ventas || 0 }}</p>
                <p class="text-xs text-gray-500">Ventas</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-200">
                <p :class="empleadoDetalle.datos_ranking?.total_puntos === 0 ? 'text-gray-400' : 'text-orange-600'" class="text-2xl font-bold">{{ empleadoDetalle.datos_ranking?.total_horneados || empleadoDetalle.metricas?.resumen?.horneados || 0 }}</p>
                <p class="text-xs text-gray-500">Horneados</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3 text-center border border-gray-200">
                <p :class="empleadoDetalle.datos_ranking?.total_puntos === 0 ? 'text-gray-400' : 'text-purple-600'" class="text-2xl font-bold">{{ empleadoDetalle.datos_ranking?.horas_trabajadas || empleadoDetalle.metricas?.horas_trabajadas || 0 }}h</p>
                <p class="text-xs text-gray-500">Horas</p>
              </div>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900 mb-3">Historial de Puntos</h4>
              <div class="max-h-64 overflow-y-auto">
                <!-- Sin datos disponibles -->
                <div v-if="empleadoDetalle.datos_ranking?.total_puntos === 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                  <p class="text-sm text-yellow-700">
                    No hay registros de actividad en el período seleccionado
                  </p>
                </div>
                <!-- Cálculo en tiempo real con datos -->
                <div v-else-if="empleadoDetalle.metricas?.es_tiempo_real" class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                  <p class="text-sm text-blue-700 mb-3">
                    Los puntos se calculan en tiempo real basados en:
                  </p>
                  <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-white rounded p-2 border border-blue-100">
                      <p class="font-semibold text-blue-900">Check-ins</p>
                      <p class="text-blue-700">{{ empleadoDetalle.datos_ranking?.total_check_ins || 0 }} × 5 = {{ (empleadoDetalle.datos_ranking?.total_check_ins || 0) * 5 }}pts</p>
                    </div>
                    <div class="bg-white rounded p-2 border border-blue-100">
                      <p class="font-semibold text-blue-900">Ventas</p>
                      <p class="text-blue-700">{{ empleadoDetalle.datos_ranking?.total_ventas || 0 }} × 2 = {{ (empleadoDetalle.datos_ranking?.total_ventas || 0) * 2 }}pts</p>
                    </div>
                    <div class="bg-white rounded p-2 border border-blue-100">
                      <p class="font-semibold text-blue-900">Horneados</p>
                      <p class="text-blue-700">{{ empleadoDetalle.datos_ranking?.total_horneados || 0 }} × 3 = {{ (empleadoDetalle.datos_ranking?.total_horneados || 0) * 3 }}pts</p>
                    </div>
                    <div class="bg-white rounded p-2 border border-blue-100">
                      <p class="font-semibold text-blue-900">Notif. Atendidas</p>
                      <p class="text-blue-700">{{ empleadoDetalle.datos_ranking?.notificaciones_atendidas || 0 }} × 10 = {{ (empleadoDetalle.datos_ranking?.notificaciones_atendidas || 0) * 10 }}pts</p>
                    </div>
                  </div>
                </div>
                <div v-else>
                  <div v-for="punto in empleadoDetalle.metricas?.historial" :key="punto.id"
                    class="flex justify-between items-center py-2 border-b border-gray-100">
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ formatConcepto(punto.concepto) }}</p>
                      <p class="text-xs text-gray-500">{{ punto.descripcion || '-' }}</p>
                      <p class="text-xs text-gray-400">{{ new Date(punto.created_at).toLocaleString() }}</p>
                    </div>
                    <span class="px-2 py-1 text-sm font-semibold rounded-full"
                      :class="punto.puntos >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                      {{ punto.puntos >= 0 ? '+' : '' }}{{ punto.puntos }}
                    </span>
                  </div>
                  <p v-if="!empleadoDetalle.metricas?.historial?.length" class="text-gray-500 text-center py-4">
                    Sin historial de puntos
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const { props } = usePage();

// Datos del servidor
const usuarios = ref(props.usuarios || []);
const sucursales = ref(props.sucursales || []);
const actividades = ref(props.actividades || []);
const resumen = ref(props.resumen || {});

// Estado de filtros actividades
const filtros = ref({
  fecha_inicio: props.fechaInicio || new Date().toISOString().split('T')[0],
  fecha_fin: props.fechaFin || new Date().toISOString().split('T')[0],
  sucursal_id: null,
  usuario_id: null,
  tipo_actividad: null,
});

// Estado de carga
const cargando = ref(false);

// Paginacion
const paginaActual = ref(1);
const itemsPorPagina = 20;

const totalPaginas = computed(() => Math.ceil(actividades.value.length / itemsPorPagina));

const actividadesPaginadas = computed(() => {
  const inicio = (paginaActual.value - 1) * itemsPorPagina;
  const fin = inicio + itemsPorPagina;
  return actividades.value.slice(inicio, fin);
});

// Aplicar filtro
const aplicarFiltro = async () => {
  cargando.value = true;
  paginaActual.value = 1;

  try {
    const response = await axios.post('/actividad-personal/filtro', filtros.value);
    actividades.value = response.data.actividades;
    resumen.value = response.data.resumen;
  } catch (error) {
    console.error('Error al filtrar:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron cargar las actividades',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
    });
  } finally {
    cargando.value = false;
  }
};

// =================== TABS ===================
const tabActivo = ref('actividades');

const isAdmin = computed(() => {
  const user = props.auth?.user || props.user;
  const roles = user?.roles || [];
  // Roles pueden venir como strings o como objetos {name}
  return roles.some(r => (typeof r === 'string' ? r : r?.name) === 'admin');
});

const abrirNotificaciones = () => {
  tabActivo.value = 'notificaciones';
  cargarNotificaciones();
};

// =================== NOTIFICACIONES DE TURNO ===================
const notificaciones = ref([]);
const cargandoNotif = ref(false);
const filtrosNotif = ref({
  fecha_inicio: new Date().toISOString().split('T')[0],
  fecha_fin: new Date().toISOString().split('T')[0],
  sucursal_id: null,
  tipo: null,
  atendida: null,
});

const cargarNotificaciones = async () => {
  cargandoNotif.value = true;
  try {
    const params = {};
    if (filtrosNotif.value.fecha_inicio) params.fecha_inicio = filtrosNotif.value.fecha_inicio;
    if (filtrosNotif.value.fecha_fin) params.fecha_fin = filtrosNotif.value.fecha_fin;
    if (filtrosNotif.value.sucursal_id) params.sucursal_id = filtrosNotif.value.sucursal_id;
    if (filtrosNotif.value.tipo) params.tipo = filtrosNotif.value.tipo;
    if (filtrosNotif.value.atendida !== null) params.atendida = filtrosNotif.value.atendida;
    const response = await axios.get('/api/notificaciones-personal', { params });
    notificaciones.value = response.data.notificaciones;
  } catch (error) {
    console.error('Error al cargar notificaciones:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron cargar las notificaciones',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
    });
  } finally {
    cargandoNotif.value = false;
  }
};

const atenderNotificacion = async (notif) => {
  try {
    await axios.post(`/api/notificaciones-personal/${notif.id}/atender`);
    notif.atendida = true;
    Swal.fire({ icon: 'success', title: 'Atendida', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
  } catch (error) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo marcar como atendida', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
  }
};

const getTipoNotifBadge = (tipo) => {
  const classes = {
    faltante: 'bg-red-100 text-red-800',
    excedente: 'bg-yellow-100 text-yellow-800',
    horneado: 'bg-orange-100 text-orange-800',
  };
  return classes[tipo] || 'bg-gray-100 text-gray-800';
};

// =================== HELPERS ACTIVIDADES ===================
const formatNumber = (num) => {
  return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
};

const getTipoBadgeClass = (tipo) => {
  const classes = {
    produccion: 'bg-orange-100 text-orange-800',
    venta: 'bg-green-100 text-green-800',
    asistencia: 'bg-purple-100 text-purple-800',
    inventario: 'bg-blue-100 text-blue-800',
    gasto: 'bg-red-100 text-red-800',
    corte_caja: 'bg-gray-100 text-gray-800',
  };
  return classes[tipo] || 'bg-gray-100 text-gray-800';
};

const getSubtipoBadgeClass = (tipo, subtipo) => {
  if (tipo === 'venta') {
    return subtipo === 'manual' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
  }
  if (tipo === 'asistencia') {
    return subtipo === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
  }
  if (tipo === 'produccion') {
    const colors = {
      pendiente: 'bg-yellow-100 text-yellow-800',
      horneando: 'bg-orange-100 text-orange-800',
      en_espera: 'bg-blue-100 text-blue-800',
      vendido: 'bg-green-100 text-green-800',
    };
    return colors[subtipo] || 'bg-gray-100 text-gray-800';
  }
  return 'bg-gray-100 text-gray-800';
};

// =================== EVALUACION DE EMPLEADOS ===================
const evalRanking = ref(props.evaluacion?.ranking || []);
const evalMejores = ref(props.evaluacion?.mejores || {});
const evalEstadisticas = ref(props.evaluacion?.estadisticas || {});
const cargandoEval = ref(false);
const mostrarDetalleEmpleado = ref(false);
const empleadoDetalle = ref(null);

// Filtros de evaluacion
const fechaInicioMes = () => {
  const d = new Date();
  return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().split('T')[0];
};

const filtrosEval = ref({
  fecha_inicio: fechaInicioMes(),
  fecha_fin: new Date().toISOString().split('T')[0],
  sucursal_id: null,
});

const abrirEvaluacion = () => {
  tabActivo.value = 'evaluacion';
  if (!evalRanking.value?.length) {
    cargarEvaluacion();
  }
};

const cargarEvaluacion = async () => {
  cargandoEval.value = true;
  try {
    const response = await axios.post('/actividad-personal/evaluacion', filtrosEval.value);
    evalRanking.value = response.data.ranking || [];
    evalMejores.value = response.data.mejores || {};
    evalEstadisticas.value = response.data.estadisticas || {};
  } catch (error) {
    console.error('Error al cargar evaluacion:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo cargar la evaluacion',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
    });
  } finally {
    cargandoEval.value = false;
  }
};

const verDetalleEmpleado = async (userId) => {
  try {
    const params = {
      fecha_inicio: filtrosEval.value.fecha_inicio,
      fecha_fin: filtrosEval.value.fecha_fin,
    };
    const response = await axios.get(`/actividad-personal/evaluacion/detalle/${userId}`, { params });
    empleadoDetalle.value = response.data;
    mostrarDetalleEmpleado.value = true;
  } catch (error) {
    console.error('Error al cargar detalle:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo cargar el detalle del empleado',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
    });
  }
};

const cerrarDetalleEmpleado = () => {
  mostrarDetalleEmpleado.value = false;
  empleadoDetalle.value = null;
};

const exportarEvaluacion = () => {
  const params = new URLSearchParams(filtrosEval.value);
  window.open(`/actividad-personal/evaluacion/exportar?${params.toString()}`, '_blank');
};

const formatConcepto = (concepto) => {
  const labels = {
    check_in: 'Check-in',
    venta: 'Venta',
    horneado: 'Horneado',
    notificacion_atendida: 'Notificacion Atendida',
    notificacion_no_atendida: 'Notificacion No Atendida',
    sobrante: 'Sobrante',
    corte_caja: 'Corte de Caja',
  };
  return labels[concepto] || concepto;
};
</script>
